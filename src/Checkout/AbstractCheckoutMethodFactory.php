<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout;

use Contao\Model;
use Contao\StringUtil;
use Isotope\CoreBundle\Model\Payment;
use Isotope\CoreBundle\Model\Shipping;
use Isotope\CoreBundle\ModelType\ModelInstanceTrait;
use Isotope\CoreBundle\ModelType\ModelTypeFactoryInterface;
use Isotope\CoreBundle\ModelType\SerializedPropertyTrait;

/**
 * @template TType of AbstractCheckoutMethod
 * @template TModel of Payment|Shipping
 *
 * @template-implements ModelTypeFactoryInterface<TType, TModel>
 */
abstract class AbstractCheckoutMethodFactory implements ModelTypeFactoryInterface
{
    use SerializedPropertyTrait, ModelInstanceTrait;

    /**
     * @param TModel $model
     *
     * @return TType
     */
    public function create(Model $model): AbstractCheckoutMethod
    {
        $this->validateInstanceOf($model, [Payment::class, Shipping::class]);

        $instance = $this->doCreate($model)
            ->setLabel($model->label ?: $model->name)
            ->setNote((string) $model->note)
            ->setPrice(...$this->getPrice((string) $model->price))
            ->setTaxClass((int) $model->tax_class)
            ->setProtected((bool) $model->protected, StringUtil::deserialize($model->groups, true))
            ->setMinMaxTotal((float) $model->minimum_total, (float) $model->maximum_total)
            ->setAllowedConfigs($this->unserialize($model->config_ids, intval(...)))
            ->setAllowedProductTypes($this->unserialize($model->product_types, intval(...)), ProductTypeCondition::from($model->product_types_condition))
            ->setDebug((bool) $model->debug)
            ->setLogging((bool) $model->logging)
            ->setEnabled((bool) $model->enabled)
        ;

        if (null !== ($quantityMode = QuantityMode::tryFrom($model->quantity_mode))) {
            $instance->setMinMaxQuantity((int) $model->minimum_quantity, (int) $model->maximum_quantity, $quantityMode);
        }

        return $instance;
    }

    /**
     * @return TType
     */
    abstract protected function doCreate(Model $model): AbstractCheckoutMethod;

    /**
     * @return array{0: float|null, 1: bool}
     */
    private function getPrice(string $price): array
    {
        if ('' === $price) {
            return [null, false];
        }

        $isPercentage = str_ends_with($price, '%');
        $price = $isPercentage ? substr($price, -1) : $price;

        return [(float) $price, $isPercentage];
    }
}
