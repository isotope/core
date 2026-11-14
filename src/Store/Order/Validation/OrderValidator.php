<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Order\Validation;

use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Validator\Constraints\NotHasKey;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @internal
 */
class OrderValidator
{
    /**
     * @return list<Constraint>
     */
    public function getConstraints(StoreContext $context): array
    {
        return [
            new Assert\Collection(
                fields: [
                    'member_id' => new Assert\Required([
                        ...($context->getMemberId() ? [new Assert\NotNull()] : []),
                        new Assert\PositiveOrZero(),
                        new Assert\Choice($context->getMemberId() ? [$context->getMemberId()] : [0, null]),
                    ]),
                    'config_id' => [new Assert\NotBlank(), new Assert\Positive(), new Assert\EqualTo($context->getConfigId())],
                    'store_id' => [new Assert\NotBlank(), new Assert\PositiveOrZero(), new Assert\EqualTo($context->getStoreId())],
                    'locale' => [new Assert\NotBlank(), new Assert\Locale(), new Assert\EqualTo($context->getLocale())],
                    'source_collection_id' => [new Assert\NotBlank(), new Assert\PositiveOrZero()],

                    'payment_id' => [new Assert\NotBlank(), new Assert\PositiveOrZero()],
                    'shipping_id' => [new Assert\NotBlank(), new Assert\PositiveOrZero()],

                    'subtotal' => [new Assert\NotBlank(), new Assert\PositiveOrZero()],
                    'total' => [new Assert\NotBlank(), new Assert\PositiveOrZero()],
                    'currency' => [new Assert\NotBlank(), new Assert\Currency(), new Assert\EqualTo($context->getCurrency()->getCode())],

                    'order_status' => new Assert\Optional([new Assert\NotBlank(), new Assert\Positive()]),
                    'date_paid' => new Assert\Optional([new Assert\NotBlank(), new Assert\DateTime()]),
                    'date_shipped' => new Assert\Optional([new Assert\NotBlank(), new Assert\DateTime()]),

                    'extensions' => new Assert\Optional([new Assert\Type('array')]),
                ],
                allowExtraFields: true,
            ),
            ...array_map(
                static fn (string $field) => new NotHasKey($field),
                ['id', 'type', 'uniqid', 'tstamp', 'locked', 'document_number', 'billing_address_id', 'shipping_address_id', 'notes'],
            ),
        ];
    }
}
