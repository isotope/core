<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Payment;

use Contao\Model;
use Isotope\CoreBundle\Checkout\AbstractCheckoutMethodFactory;
use Isotope\CoreBundle\Model\Payment;
use Isotope\CoreBundle\ModelType\ModelInstanceTrait;
use Isotope\CoreBundle\ModelType\SerializedPropertyTrait;

/**
 * @template TType of AbstractPayment
 *
 * @extends AbstractCheckoutMethodFactory<TType, Payment>
 */
abstract class AbstractPaymentFactory extends AbstractCheckoutMethodFactory
{
    use SerializedPropertyTrait, ModelInstanceTrait;

    /**
     * @param Payment $model
     */
    public function create(Model $model): AbstractPayment
    {
        $this->validateInstanceOf($model, Payment::class);

        /** @var AbstractPayment $payment */
        $payment = parent::create($model);
        $payment
            ->setAllowedCountries($model->countries ? explode(',', $model->countries) : null)
            ->setAllowedShippings($this->unserialize($model->shipping_modules, intval(...)))
        ;

        return $payment;
    }
}
