<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Order\Validation;

use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Price\PriceDisplay;
use Isotope\CoreBundle\Util\FloatComparator;
use Isotope\CoreBundle\Validator\Constraints\NotHasKey;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @internal
 */
class LineItemValidator
{
    /**
     * @return list<Constraint>
     */
    public function getLineItemConstraints(): array
    {
        return [
            new Assert\Sequentially([
                new Assert\NotNull(),
                new Assert\Type('array'),
                new Assert\Collection(
                    fields: [
                        'type' => [new Assert\NotBlank()],
                        'sorting' => [new Assert\NotNull()],
                        'quantity' => [new Assert\NotNull(), new Assert\Type('integer'), new Assert\Positive()],
                        'price' => [new Assert\NotNull(), new Assert\Type('finite-number')],
                        'price_data' => [new Assert\Type('array'), $this->getPriceDataConstraint()],

                        'configuration' => new Assert\Optional([new Assert\Type('array')]),
                        'tax_free_price' => new Assert\Optional([new Assert\NotNull(), new Assert\Type('finite-number')]),
                    ],
                    allowExtraFields: true,
                ),
                new Assert\Callback($this->checkPriceData(...)),
            ]),
            ...array_map(static fn (string $field) => new NotHasKey($field), ['id', 'pid', 'tstamp']),
        ];
    }

    /**
     * @return list<Constraint>
     */
    public function getOrderConstraints(StoreContext $context): array
    {
        return [
            new Assert\Sequentially([
                new Assert\Collection(
                    fields: [
                        'line_items' => new Assert\Sequentially([
                            new Assert\NotNull(),
                            new Assert\Type('array'),
                            new Assert\Count(min: 1),
                            new Assert\All($this->getLineItemConstraints()),
                        ]),
                    ],
                    allowExtraFields: true,
                ),
                new Assert\Callback(
                    function (array $order, ExecutionContextInterface $validationContext) use ($context): void {
                        $this->checkPrices($order, $context, $validationContext);
                    },
                ),
            ]),
        ];
    }

    private function getPriceDataConstraint(): Constraint
    {
        $taxRate = new Assert\Collection(
            fields: [
                'id' => [new Assert\NotNull(), new Assert\Type('string')],
                'tax_rate' => [new Assert\NotNull(), new Assert\Type('finite-number')],
                'percentage' => [new Assert\NotNull(), new Assert\Type('finite-number')],
                'label' => [new Assert\Type('string')],
            ],
            allowExtraFields: true,
        );

        $tax = new Assert\Collection(
            fields: [
                'id' => [new Assert\NotNull(), new Assert\Type('string')],
                'amount' => [new Assert\NotNull(), new Assert\Type('finite-number')],
                'tax_rate' => [new Assert\NotNull(), new Assert\Type('finite-number')],
                'price' => [new Assert\NotNull(), new Assert\Type('finite-number')],
                'label' => [new Assert\Type('string')],
                'number' => [new Assert\NotNull(), new Assert\Type('integer'), new Assert\PositiveOrZero()],
            ],
            allowExtraFields: true,
        );

        return new Assert\Collection(
            fields: [
                'unit_price' => [new Assert\NotNull(), new Assert\Type('finite-number')],
                'total_price' => [new Assert\NotNull(), new Assert\Type('finite-number')],
                'quantity' => [new Assert\NotNull(), new Assert\Type('integer'), new Assert\Positive()],
                'unit_label' => new Assert\Optional([new Assert\Type('string')]),
                'is_range' => new Assert\Optional([new Assert\NotNull(), new Assert\Type('bool')]),
                'calculated_taxes' => new Assert\Optional(new Assert\Sequentially([
                    new Assert\NotNull(),
                    new Assert\Type('array'),
                    new Assert\All([new Assert\NotNull(), $tax]),
                ])),
                'tax_rates' => new Assert\Optional([
                    new Assert\NotNull(),
                    new Assert\Type('array'),
                    new Assert\Collection(
                        fields: [
                            'rates' => new Assert\Optional([new Assert\NotNull(), new Assert\Type('array'), new Assert\All([new Assert\NotNull(), $taxRate])]),
                            'included' => new Assert\Optional([new Assert\NotNull(), new Assert\Type('array'), new Assert\All([new Assert\NotNull(), $taxRate])]),
                        ],
                        allowExtraFields: true,
                    ),
                ]),
            ],
            allowExtraFields: true,
        );
    }

    private function checkPriceData(array $item, ExecutionContextInterface $context): void
    {
        $data = $item['price_data'];

        if (null === $data) {
            if (!FloatComparator::equals($item['price'], 0)) {
                $context->buildViolation('A priced line item must have price data.')->atPath('[price_data]')->addViolation();
            }

            return;
        }

        try {
            json_encode($data, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $context->buildViolation('Price data must be JSON serializable.')->atPath('[price_data]')->addViolation();
        }

        if ($item['quantity'] !== $data['quantity']) {
            $context->buildViolation('The price quantity does not match the line item quantity.')->atPath('[price_data][quantity]')->addViolation();
        }

        if (!FloatComparator::equals($item['price'], $data['total_price'])) {
            $context->buildViolation('The price total does not match the line item price.')->atPath('[price_data][total_price]')->addViolation();
        }

        if (!FloatComparator::equals($data['unit_price'] * $data['quantity'], $data['total_price'])) {
            $context->buildViolation('The unit price multiplied by the quantity does not match the price total.')->atPath('[price_data][unit_price]')->addViolation();
        }
    }

    private function checkPrices(array $order, StoreContext $context, ExecutionContextInterface $validationContext): void
    {
        $amounts = [];
        $taxes = [];

        foreach ($order['line_items'] as $item) {
            if (\in_array($item['type'], [LineItem::TYPE_SUBTOTAL, LineItem::TYPE_TOTAL], true)) {
                continue;
            }

            $level = (int) ($item['level'] ?? 0);
            $amounts[$level] = FloatComparator::cast(($amounts[$level] ?? 0) + $item['price']);

            if (PriceDisplay::Net !== $context->getPriceDisplay()) {
                continue;
            }

            $itemTaxes = $item['price_data']['calculated_taxes'] ?? [];

            foreach ($itemTaxes as $tax) {
                $taxes[$tax['id']] = FloatComparator::cast(($taxes[$tax['id']] ?? 0) + FloatComparator::cast($tax['amount'] * $item['quantity']));
            }
        }

        ksort($amounts);

        $subtotal = 0.0;
        $subtotals = [];

        foreach ($amounts as $level => $amount) {
            $subtotal = FloatComparator::cast($subtotal + $amount);
            $subtotals[$level] = $subtotal;
        }

        $total = $subtotal;

        if (PriceDisplay::Net === $context->getPriceDisplay()) {
            $taxTotal = 0.0;

            foreach ($taxes as $tax) {
                $taxTotal += $context->getMoneyFormat()->mathRound($tax);
            }

            $total = $context->getMoneyFormat()->moneyRound($subtotal + $taxTotal);
        }

        foreach (['subtotal' => $subtotal, 'total' => $total] as $field => $expected) {
            if (isset($order[$field]) && is_numeric($order[$field]) && !FloatComparator::equals((float) $order[$field], $expected)) {
                $validationContext->buildViolation('This amount does not match the sum of the line item prices.')
                    ->atPath('['.$field.']')
                    ->addViolation()
                ;
            }
        }

        foreach ($order['line_items'] as $index => $item) {
            if (!\in_array($item['type'], [LineItem::TYPE_SUBTOTAL, LineItem::TYPE_TOTAL], true)) {
                continue;
            }

            $expected = $total;

            if (LineItem::TYPE_SUBTOTAL === $item['type']) {
                $expected = 0.0;

                foreach ($subtotals as $level => $amount) {
                    if ($level <= (int) ($item['level'] ?? 0)) {
                        $expected = $amount;
                    }
                }
            }

            if (!FloatComparator::equals($item['price'], $expected)) {
                $validationContext->buildViolation('This amount does not match the sum of the line item prices.')
                    ->atPath('[line_items]['.$index.'][price]')
                    ->addViolation()
                ;
            }
        }
    }
}
