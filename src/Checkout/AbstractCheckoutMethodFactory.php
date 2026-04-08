<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout;

use Contao\Model;
use Isotope\CoreBundle\Model\Payment;
use Isotope\CoreBundle\Model\Shipping;
use Isotope\CoreBundle\ModelType\ContainerAwareInterface;
use Isotope\CoreBundle\ModelType\ContainerAwareTrait;
use Isotope\CoreBundle\ModelType\ModelInstanceTrait;
use Isotope\CoreBundle\ModelType\ModelTypeFactoryInterface;
use Isotope\CoreBundle\ModelType\SerializedPropertyTrait;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Price\PriceDefinition;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @template TType of AbstractCheckoutMethod
 * @template TModel of Payment|Shipping
 *
 * @template-implements ModelTypeFactoryInterface<TType, TModel>
 */
abstract class AbstractCheckoutMethodFactory implements ModelTypeFactoryInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;
    use ModelInstanceTrait;
    use SerializedPropertyTrait;

    public static function getSubscribedServices(): array
    {
        return [TranslatorInterface::class];
    }

    /**
     * @param TModel $model
     *
     * @return TType
     */
    public function create(Model $model, StoreContext $context): AbstractCheckoutMethod
    {
        $this->validateInstanceOf($model, [Payment::class, Shipping::class]);

        $instance = $this->doCreate($model, $context)
            ->setLabel($this->get(TranslatorInterface::class)->trans($model->label ?: $model->name, [], 'isotope'))
            ->setNote((string) $model->note)
            ->setPriceDefinition(PriceDefinition::fromString((string) $model->price, (int) $model->tax_class))
            ->setRule((int) $model->rule)
            ->setAllowedProductTypes($this->csv($model->product_types, intval(...)), ProductTypeCondition::from($model->product_types_condition))
            ->setDebug((bool) $model->debug)
            ->setLogging((bool) $model->logging)
            ->setEnabled((bool) $model->enabled)
        ;

        return $instance;
    }

    /**
     * @return TType
     */
    abstract protected function doCreate(Model $model, StoreContext $context): AbstractCheckoutMethod;
}
