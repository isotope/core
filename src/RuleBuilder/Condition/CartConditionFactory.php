<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\RuleBuilder\Condition;

use Contao\Model;
use Isotope\CoreBundle\Model\RuleCondition;
use Isotope\CoreBundle\ModelType\ModelInstanceTrait;
use Isotope\CoreBundle\ModelType\ModelTypeFactoryInterface;
use Isotope\CoreBundle\RuleBuilder\Exception\InvalidTypeException;
use Isotope\CoreBundle\Store\StoreManager;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ModelTypeFactoryInterface<RuleConditionInterface, RuleCondition>
 */
class CartConditionFactory implements ModelTypeFactoryInterface
{
    use ModelInstanceTrait;

    public function __construct(
        private readonly StoreManager $storeManager,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * @param RuleCondition $model
     */
    public function create(Model $model): object
    {
        $this->validateInstanceOf($model, RuleCondition::class);

        $class = match($model->type) {
            'cart_total' => CartTotalCondition::class,
            default => throw new InvalidTypeException($model->type, self::class),
        };

        return new $class(
            $this->storeManager,
            $this->translator,
            $model->operator,
            (float) $model->amount,
            (float) $model->amount_from,
            (float) $model->amount_to,
        );
    }
}
