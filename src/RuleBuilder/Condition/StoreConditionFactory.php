<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\RuleBuilder\Condition;

use Contao\Model;
use Isotope\CoreBundle\Model\RuleConditionModel;
use Isotope\CoreBundle\ModelType\Exception\InvalidTypeException;
use Isotope\CoreBundle\ModelType\ModelInstanceTrait;
use Isotope\CoreBundle\ModelType\ModelTypeFactoryInterface;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ModelTypeFactoryInterface<RuleConditionInterface, RuleConditionModel>
 */
class StoreConditionFactory implements ModelTypeFactoryInterface
{
    use ModelInstanceTrait;

    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    /**
     * @param RuleConditionModel $model
     */
    public function create(Model $model, StoreContext $context): object
    {
        $this->validateInstanceOf($model, RuleConditionModel::class);

        return match ($model->type) {
            'store_config' => new StoreConfigCondition(
                $this->translator,
                $model->operator,
                array_map(intval(...), explode(',', (string) $model->config_ids)),
            ),

            'store_currency' => new StoreCurrencyCondition(
                $this->translator,
                $model->operator,
                explode(',', (string) $model->currencies),
            ),

            'store_date' => new StoreDateCondition(
                $this->getTimestamp($model->start),
                $this->getTimestamp($model->stop),
            ),

            'store_time' => new StoreTimeCondition(
                (int) $model->start_time,
                (int) $model->stop_time,
            ),

            default => throw new InvalidTypeException($model->type, self::class),
        };
    }

    private function getTimestamp(string|null $value): int|null
    {
        if ('' === (string) $value) {
            return null;
        }

        return (int) $value;
    }
}
