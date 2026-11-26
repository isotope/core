<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Address;

use Contao\Controller;
use Contao\System;
use Contao\Widget;
use Isotope\CoreBundle\Model\AddressModel;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Validator\Constraints\DcaAttributes;
use Isotope\CoreBundle\Validator\Constraints\NotHasKey;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @experimental
 */
class AddressValidator
{
    public function __construct(private readonly ValidatorInterface $validator)
    {
    }

    public function isValid(AddressModel $address, AddressConfig $addressConfig): bool
    {
        return 0 === $this->validate($address, $addressConfig)->count();
    }

    public function validate(AddressModel $address, AddressConfig $addressConfig): ConstraintViolationListInterface
    {
        return $this->validator->validate(
            $address->row(),
            $this->getConstraint($addressConfig),
        );
    }

    public function getConstraint(AddressConfig $addressConfig): Constraint
    {
        System::loadLanguageFile('tl_iso_address');
        Controller::loadDataContainer('tl_iso_address');

        $fields = [];

        foreach ($addressConfig->getFields() as $field) {
            if (!isset($GLOBALS['TL_DCA']['tl_iso_address']['fields'][$field])) {
                continue;
            }

            $attributes = Widget::getAttributesFromDca($GLOBALS['TL_DCA']['tl_iso_address']['fields'][$field], $field, null, $field, 'tl_iso_address');
            $attributes['mandatory'] = $addressConfig->isMandatory($field);

            $constraints = [new DcaAttributes($attributes)];

            if ('country' === $field) {
                $constraints[] = new Assert\Country();

                if ([] !== $addressConfig->getCountryCodes()) {
                    $constraints[] = new Assert\Choice(choices: $addressConfig->getCountryCodes());
                }
            }

            if ($addressConfig->isMandatory($field)) {
                $fields[$field] = new Assert\Required(constraints: $constraints);
            } else {
                $fields[$field] = new Assert\Optional(constraints: $constraints);
            }
        }

        return new Assert\Collection(fields: $fields, allowExtraFields: true);
    }

    /**
     * @return list<Constraint>
     */
    public function getOrderConstraints(StoreContext $context): array
    {
        $reserved = array_map(static fn (string $field) => new NotHasKey($field), ['id', 'pid', 'ptable', 'tstamp', 'store_id']);

        return [
            new Assert\Collection(
                fields: [
                    'billing_address' => new Assert\Sequentially([
                        new Assert\NotNull(),
                        new Assert\Type('array'),
                        $this->getConstraint($context->getBillingAddressConfig()),
                        ...$reserved,
                    ]),
                    'shipping_address' => new Assert\Optional(new Assert\Sequentially([
                        new Assert\NotNull(),
                        new Assert\Type('array'),
                        $this->getConstraint($context->getShippingAddressConfig()),
                        ...$reserved,
                    ])),
                ],
                allowExtraFields: true,
            ),
        ];
    }
}
