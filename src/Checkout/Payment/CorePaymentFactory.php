<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Payment;

use Contao\Model;
use Isotope\CoreBundle\Model\PaymentModel;
use Isotope\CoreBundle\ModelType\Exception\InvalidTypeException;
use Isotope\CoreBundle\Store\Context\StoreContext;

/**
 * @extends AbstractPaymentFactory<CashPayment|PaypalPayment>
 */
class CorePaymentFactory extends AbstractPaymentFactory
{
    /**
     * @param PaymentModel $model
     */
    public function doCreate(Model $model, StoreContext $context): AbstractPayment
    {
        return match ($model->type) {
            'cash' => new CashPayment($model->id, $model->processing_status, $context),
            'paypal' => new PaypalPayment(
                $model->id,
                (string) $model->paypal_account,
                $model->processing_status,
                $model->failed_status,
                $context,
            ),
            default => throw new InvalidTypeException($model->type, self::class),
        };
    }
}
