<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\RuleBuilder\Condition;

use Contao\CoreBundle\Intl\Countries;
use Contao\Model;
use Isotope\CoreBundle\Intl\Subdivisions;
use Isotope\CoreBundle\Model\RuleCondition;
use Isotope\CoreBundle\ModelType\ModelInstanceTrait;
use Isotope\CoreBundle\ModelType\ModelTypeFactoryInterface;
use Isotope\CoreBundle\RuleBuilder\Exception\InvalidTypeException;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Util\PostalCodeUtil;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ModelTypeFactoryInterface<RuleConditionInterface, RuleCondition>
 */
class CartConditionFactory implements ModelTypeFactoryInterface
{
    use ModelInstanceTrait;

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly Countries $countries,
        private readonly Subdivisions $subdivisions,
    ) {
    }

    /**
     * @param RuleCondition $model
     */
    public function create(Model $model, StoreContext $context): object
    {
        $this->validateInstanceOf($model, RuleCondition::class);

        return match ($model->type) {
            'cart_address' => new CartAddressCondition(
                $this->translator,
                $this->countries,
                $this->subdivisions,
                $model->address_type,
                $model->countries ? explode(',', $model->countries) : null,
                $model->countries && $model->subdivisions ? explode(',', $model->subdivisions) : null,
                !empty($model->postalcodes) ? PostalCodeUtil::parseRange($model->postalcodes) : null,
                (string) $model->postalcodes,
            ),
            'cart_total' => new CartTotalCondition($this->translator, $model->operator, (float) $model->amount, (float) $model->amount_from, (float) $model->amount_to),
            'cart_quantity' => new CartQuantityCondition($this->translator, $model->operator, (int) $model->quantity, (int) $model->quantity_from, (int) $model->quantity_to),
            'cart_products' => new CartProductsCondition($this->translator, $model->operator, (int) $model->quantity, (int) $model->quantity_from, (int) $model->quantity_to),
            default => throw new InvalidTypeException($model->type, self::class),
        };
    }
}
