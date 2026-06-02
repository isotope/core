<?php

namespace Isotope\CoreBundle\Model;

use Contao\Model;
use Contao\Model\Collection;
use Isotope\CoreBundle\ProductCollection\Order\Order;
use Symfony\Component\Uid\Uuid;

/**
 * @property int    $pid
 * @property int    $tstamp
 * @property string $uniqid
 * @property int    $payment_id
 * @property int    $created
 * @property int|null    $completed
 * @property string|null $label
 * @property string|null $amount
 * @property string|null $currency
 * @property string|null $status
 */
class OrderTransactionModel extends Model
{
    /**
     * @var string
     */
    protected static $strTable = 'tl_iso_order_transaction';

    public static function findByUniqid(string $uniqid, array $options = []): self|null
    {
        return self::findOneBy('uniqid', $uniqid, $options);
    }

    /**
     * @return Collection<self>|null
     */
    public static function findByOrderId(int|string $orderId, array $options = []): Collection|null
    {
        if (!isset($options['order'])) {
            $options['order'] = 'created';
        }

        return self::findBy('pid', $orderId, $options);
    }

    public static function createForOrder(Order $order): self
    {
        $payment = $order->getPaymentMethod();

        if (!$payment) {
            throw new \UnderflowException('Cannot create OrderTransactionModel if order has no payment');
        }

        $instance = new self();
        $instance->pid = $order->getId();
        $instance->tstamp = time();
        $instance->uniqid = Uuid::v4()->toString();
        $instance->payment_id = $payment->getId();
        $instance->created = time();
        $instance->label = $payment->getLabel();
        $instance->amount = $order->getTotal()->getTotalPrice();
        $instance->currency = $order->getContext()->getCurrency()->getCode();

        return $instance;
    }
}
