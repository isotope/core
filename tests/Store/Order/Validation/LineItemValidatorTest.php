<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Store\Order\Validation;

use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\Store\Order\Transformer\LineItemTransformer;
use Isotope\CoreBundle\Store\Order\Validation\LineItemValidator;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use Isotope\CoreBundle\Store\Price\MoneyFormat;
use Isotope\CoreBundle\Store\Price\PriceDisplay;
use Isotope\CoreBundle\Store\Tax\CalculatedTax;
use Isotope\CoreBundle\Store\Tax\CalculatedTaxCollection;
use Isotope\CoreBundle\Test\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validation;

final class LineItemValidatorTest extends TestCase
{
    #[DataProvider('validItemProvider')]
    public function testValidItems(array $item): void
    {
        $validator = Validation::createValidator();
        $constraints = (new LineItemValidator())->getLineItemConstraints();

        $this->assertCount(0, $validator->validate($item, $constraints));
    }

    public static function validItemProvider(): iterable
    {
        foreach ([LineItem::TYPE_PRODUCT, LineItem::TYPE_SHIPPING, LineItem::TYPE_PAYMENT] as $type) {
            yield $type => [LineItemTransformer::transform(new LineItem($type, null))];
        }

        yield 'subtotal' => [LineItemTransformer::transformSubtotal(0, new CalculatedPrice(10, 10))];
        yield 'total' => [self::minimalItem()];
        yield 'free item' => [self::pricedItem('product', 0)];
        yield 'discount and custom type' => [array_replace(self::pricedItem('discount', -5), ['tax_free_price' => -5])];
        yield 'nullable properties' => [array_replace(self::minimalItem(), ['identifier' => null, 'sku' => null, 'configuration' => null, 'referencePage' => null, 'unit_label' => null])];
        yield 'configuration and additional properties' => [array_replace(self::minimalItem(), [
            'configuration' => ['color' => 'red', 'custom' => ['value' => 42]],
            'custom_column' => 'custom value',
            'extensions' => ['custom' => ['value' => 42]],
            'referencePage' => 17,
        ])];
    }

    #[DataProvider('invalidValueProvider')]
    public function testInvalidFields(string $field, mixed $value): void
    {
        $validator = Validation::createValidator();
        $constraints = (new LineItemValidator())->getLineItemConstraints();
        $violations = $validator->validate(array_replace(self::minimalItem(), [$field => $value]), $constraints);

        $this->assertGreaterThan(0, $violations->count());
        $this->assertSame('['.$field.']', $violations[0]->getPropertyPath());
    }

    public static function invalidValueProvider(): iterable
    {
        yield 'empty type' => ['type', ''];
        yield 'null sorting' => ['sorting', null];
        yield 'zero quantity' => ['quantity', 0];
        yield 'negative quantity' => ['quantity', -1];
        yield 'fractional quantity' => ['quantity', 1.5];
        yield 'string quantity' => ['quantity', '2'];
        yield 'null quantity' => ['quantity', null];
        yield 'null price' => ['price', null];
        yield 'non-numeric price' => ['price', 'invalid'];
        yield 'infinite price' => ['price', INF];
        yield 'NaN price' => ['price', NAN];
        yield 'invalid price data' => ['price_data', 'invalid'];
        yield 'invalid configuration' => ['configuration', 'invalid'];
        yield 'invalid tax-free price' => ['tax_free_price', 'invalid'];
        yield 'priced item requires price data' => ['price_data', null];

        foreach (['id', 'pid', 'tstamp'] as $field) {
            yield 'reserved '.$field => [$field, null];
        }
    }

    #[DataProvider('invalidPriceDataProvider')]
    public function testPriceDataConsistency(array $changes, string $expectedPath): void
    {
        $item = self::pricedItem('product', 10, quantity: 2);
        $item['price_data'] = array_replace($item['price_data'], $changes);
        $violations = Validation::createValidator()->validate($item, (new LineItemValidator())->getLineItemConstraints());
        $paths = array_map(static fn (ConstraintViolationInterface $violation) => $violation->getPropertyPath(), iterator_to_array($violations));

        $this->assertContains($expectedPath, $paths);
    }

    public static function invalidPriceDataProvider(): iterable
    {
        yield 'quantity differs from item' => [['quantity' => 3], '[price_data][quantity]'];
        yield 'total differs from item' => [['total_price' => 11], '[price_data][total_price]'];
        yield 'unit price times quantity differs from total' => [['unit_price' => 9], '[price_data][unit_price]'];
        yield 'null unit price' => [['unit_price' => null], '[price_data][unit_price]'];
        yield 'non-numeric unit price' => [['unit_price' => []], '[price_data][unit_price]'];
        yield 'infinite total price' => [['total_price' => INF], '[price_data][total_price]'];
        yield 'NaN unit price' => [['unit_price' => NAN], '[price_data][unit_price]'];
        yield 'fractional price quantity' => [['quantity' => 1.5], '[price_data][quantity]'];
        yield 'zero price quantity' => [['quantity' => 0], '[price_data][quantity]'];
        yield 'invalid unit label' => [['unit_label' => []], '[price_data][unit_label]'];
        yield 'invalid range flag' => [['is_range' => []], '[price_data][is_range]'];
        yield 'null tax collection' => [['calculated_taxes' => null], '[price_data][calculated_taxes]'];
        yield 'null tax entry' => [['calculated_taxes' => [null]], '[price_data][calculated_taxes][0]'];
        yield 'invalid tax rates' => [['tax_rates' => 'invalid'], '[price_data][tax_rates]'];
        yield 'null tax rate entry' => [['tax_rates' => ['rates' => [null]]], '[price_data][tax_rates][rates][0]'];
        yield 'null included tax rate entry' => [['tax_rates' => ['included' => [null]]], '[price_data][tax_rates][included][0]'];
        yield 'unserializable additional price data' => [['custom' => INF], '[price_data]'];

        $tax = (new CalculatedTax('1', 1, 10, 10))->jsonSerialize();

        foreach (['id' => [], 'amount' => INF, 'tax_rate' => [], 'price' => null, 'label' => [], 'number' => []] as $field => $value) {
            yield 'invalid calculated tax '.$field => [['calculated_taxes' => [array_replace($tax, [$field => $value])]], '[price_data][calculated_taxes][0]['.$field.']'];
        }

        $rate = ['id' => '1', 'tax_rate' => 10, 'percentage' => 100, 'label' => null];

        foreach (['id' => [], 'tax_rate' => INF, 'percentage' => [], 'label' => []] as $field => $value) {
            yield 'invalid tax rate '.$field => [['tax_rates' => ['rates' => [array_replace($rate, [$field => $value])]]], '[price_data][tax_rates][rates][0]['.$field.']'];
        }
    }

    public function testMissingPriceDataFieldsAreRejected(): void
    {
        foreach (['unit_price', 'total_price', 'quantity'] as $field) {
            $item = self::pricedItem('product', 10);
            unset($item['price_data'][$field]);
            $violations = Validation::createValidator()->validate($item, (new LineItemValidator())->getLineItemConstraints());

            $this->assertCount(1, $violations);
            $this->assertSame('[price_data]['.$field.']', $violations[0]->getPropertyPath());
        }
    }

    public function testValidPriceDataCanBeRestored(): void
    {
        $item = self::pricedItem('product', 10, quantity: 2, tax: 0.5);
        $item['price_data']['tax_rates'] = [
            'rates' => [['id' => '1', 'tax_rate' => 10, 'percentage' => 100, 'label' => null]],
            'included' => [['id' => '1', 'tax_rate' => 10, 'percentage' => 100, 'label' => null]],
        ];
        $violations = Validation::createValidator()->validate($item, (new LineItemValidator())->getLineItemConstraints());
        $this->assertCount(0, $violations);
        $restored = CalculatedPrice::createFromJson(json_encode($item['price_data'], JSON_THROW_ON_ERROR));
        $this->assertInstanceOf(CalculatedPrice::class, $restored);
        $this->assertSame($item['price'], $restored->getTotalPrice());
        $this->assertSame($item['quantity'], $restored->getQuantity());
        $this->assertCount(1, $restored->getCalculatedTaxes());
        $this->assertCount(1, $restored->getTaxRates());
        $this->assertCount(1, $restored->getIncludedTaxRates());
    }

    #[DataProvider('requiredFieldProvider')]
    public function testMissingRequiredFields(string $field): void
    {
        $item = self::minimalItem();
        unset($item[$field]);
        $validator = Validation::createValidator();
        $constraints = (new LineItemValidator())->getLineItemConstraints();
        $violations = $validator->validate($item, $constraints);

        $this->assertCount(1, $violations);
        $this->assertSame('['.$field.']', $violations[0]->getPropertyPath());
    }

    public static function requiredFieldProvider(): iterable
    {
        foreach (['type', 'sorting', 'quantity', 'price', 'price_data'] as $field) {
            yield $field => [$field];
        }
    }

    #[DataProvider('orderPricesProvider')]
    public function testOrderPrices(array $order, PriceDisplay $display, array $expectedPaths, MoneyFormat $format = new MoneyFormat()): void
    {
        $context = $this->createStoreContext(priceDisplay: $display, moneyFormat: $format);
        $constraints = (new LineItemValidator())->getOrderConstraints($context);
        $violations = Validation::createValidator()->validate($order, $constraints);
        $paths = array_map(static fn (ConstraintViolationInterface $violation) => $violation->getPropertyPath(), iterator_to_array($violations));
        sort($paths);
        sort($expectedPaths);

        $this->assertSame($expectedPaths, $paths);
    }

    public static function orderPricesProvider(): iterable
    {
        $items = [
            self::pricedItem('product', 100, quantity: 2, tax: 5),
            self::pricedItem('shipping', 10, level: 1, tax: 1),
            self::pricedItem('payment', 2, level: 1, tax: 0.2),
            self::pricedItem('discount', -5, level: 1, tax: -0.5),
            self::pricedItem('subtotal', 100),
            self::pricedItem('subtotal', 107, level: 1),
            self::pricedItem('total', 107),
        ];
        $order = ['subtotal' => 107, 'total' => 107, 'line_items' => $items, 'custom_column' => 'value'];

        yield 'gross prices include all charges once' => [$order, PriceDisplay::Gross, []];
        yield 'fixed prices include taxes already' => [$order, PriceDisplay::Fixed, []];
        yield 'incorrect subtotal' => [array_replace($order, ['subtotal' => 108]), PriceDisplay::Gross, ['[subtotal]']];
        yield 'incorrect total' => [array_replace($order, ['total' => 108]), PriceDisplay::Gross, ['[total]']];

        $subtotalItems = $items;
        $subtotalItems[5] = self::pricedItem('subtotal', 108, level: 1);
        yield 'incorrect subtotal row' => [array_replace($order, ['line_items' => $subtotalItems]), PriceDisplay::Gross, ['[line_items][5][price]']];
        $totalItems = $items;
        $totalItems[6] = self::pricedItem('total', 108);
        yield 'incorrect total row' => [array_replace($order, ['line_items' => $totalItems]), PriceDisplay::Gross, ['[line_items][6][price]']];

        $netItems = $items;
        $netItems[6] = self::pricedItem('total', 117.7);
        $netOrder = ['subtotal' => 107, 'total' => 117.7, 'line_items' => $netItems];
        yield 'net prices add taxes for every unit' => [$netOrder, PriceDisplay::Net, []];
        yield 'net total must include taxes' => [array_replace($netOrder, ['total' => 107]), PriceDisplay::Net, ['[total]']];

        $roundingItems = [self::pricedItem('product', 0.1, tax: 0.0049), self::pricedItem('product', 0.1, tax: 0.0049)];
        yield 'taxes are aggregated before rounding' => [['subtotal' => 0.2, 'total' => 0.21, 'line_items' => $roundingItems], PriceDisplay::Net, []];
        $roundingItems[1]['price_data']['calculated_taxes'][1]['id'] = '2';
        yield 'different tax rates round separately' => [['subtotal' => 0.2, 'total' => 0.2, 'line_items' => $roundingItems], PriceDisplay::Net, []];

        yield 'cash rounding' => [
            ['subtotal' => 10.02, 'total' => 10.8, 'line_items' => [self::pricedItem('product', 10.02, tax: 0.8)]],
            PriceDisplay::Net, [], new MoneyFormat(roundingIncrement: 0.05),
        ];
        yield 'floating point arithmetic' => [['subtotal' => 0.3, 'total' => 0.3, 'line_items' => [self::pricedItem('product', 0.1), self::pricedItem('product', 0.2)]], PriceDisplay::Gross, []];
        yield 'free order' => [['subtotal' => 0, 'total' => 0, 'line_items' => [self::pricedItem('product', 0)]], PriceDisplay::Net, []];
        yield 'changed charge' => [['subtotal' => 10, 'total' => 10, 'line_items' => [self::pricedItem('product', 11)]], PriceDisplay::Gross, ['[subtotal]', '[total]']];

        $invalidTax = self::pricedItem('product', 10);
        $invalidTax['price_data']['calculated_taxes'] = 'invalid';
        yield 'malformed tax collection' => [['subtotal' => 10, 'total' => 10, 'line_items' => [$invalidTax]], PriceDisplay::Net, ['[line_items][0][price_data][calculated_taxes]']];
        $invalidTax = self::pricedItem('product', 10);
        $invalidTax['price_data']['calculated_taxes'][1]['amount'] = 'invalid';
        yield 'malformed tax amount' => [['subtotal' => 10, 'total' => 10, 'line_items' => [$invalidTax]], PriceDisplay::Net, ['[line_items][0][price_data][calculated_taxes][1][amount]']];
    }

    private static function pricedItem(string $type, float $price, int $quantity = 1, int $level = 0, float $tax = 0): array
    {
        $taxes = new CalculatedTaxCollection();
        $taxes->put('1', new CalculatedTax('1', $tax, 10, $price / $quantity));
        $item = (new LineItem($type, null, $quantity))->setLevel($level);
        $item->setPrice(new CalculatedPrice($price / $quantity, $price, $taxes, quantity: $quantity));

        return LineItemTransformer::transform($item);
    }

    private static function minimalItem(): array
    {
        return [
            'type' => LineItem::TYPE_TOTAL,
            'sorting' => 1,
            'quantity' => 1,
            'price' => 10.0,
            'price_data' => (new CalculatedPrice(10, 10))->jsonSerialize(),
        ];
    }
}
