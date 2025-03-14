<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Payment;

use Contao\Model;
use Isotope\CoreBundle\Checkout\AbstractCheckoutMethodFactory;
use Isotope\CoreBundle\Model\Payment;

/**
 * @template TType of AbstractPayment
 *
 * @extends AbstractCheckoutMethodFactory<TType, Payment>
 */
abstract class AbstractPaymentFactory extends AbstractCheckoutMethodFactory
{
    public function create(Model $model): AbstractPayment
    {
        if (!$model instanceof Payment) {
            throw new \RuntimeException(sprintf('%s should be an instance of %s', $model::class, Payment::class));
        }

        return parent::create($model)
            ->setAllowedCountries($this->unserialize($model->countries, strval(...)))
            ->setAllowedShippings($this->unserialize($model->shipping_modules, intval(...)))
        ;
    }
}
