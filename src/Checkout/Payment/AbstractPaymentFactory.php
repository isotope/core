<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Payment;

use Contao\Model;
use Isotope\CoreBundle\Checkout\AbstractCheckoutMethodFactory;
use Isotope\CoreBundle\Model\Payment;
use Isotope\CoreBundle\ModelType\SerializedPropertyTrait;

/**
 * @template TType of AbstractPayment
 *
 * @extends AbstractCheckoutMethodFactory<TType, Payment>
 */
abstract class AbstractPaymentFactory extends AbstractCheckoutMethodFactory
{
    use SerializedPropertyTrait;

    public function create(Model $model): AbstractPayment
    {
        if (!$model instanceof Payment) {
            throw new \LogicException(\sprintf('%s should be an instance of %s', $model::class, Payment::class));
        }

        /** @var AbstractPayment $payment */
        $payment = parent::create($model);
        $payment
            ->setAllowedCountries($model->countries ? explode(',', $model->countries) : null)
            ->setAllowedShippings($this->unserialize($model->shipping_modules, intval(...)))
        ;

        return $payment;
    }
}
