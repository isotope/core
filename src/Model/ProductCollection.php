<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Model;

use Contao\Model;
use Contao\Model\Collection;

/**
 * @property int    $id
 * @property int    $tstamp
 * @property string $type
 * @property int    $member
 * @property int    $store_id
 * @property int    $locked
 * @property mixed  $settings
 * @property int    $source_collection_id
 * @property string $uniqid
 * @property int    $config_id
 * @property int    $payment_id
 * @property int    $shipping_id
 * @property int    $billing_address_id
 * @property int    $shipping_address_id
 * @property float  $subtotal
 * @property float  $tax_free_subtotal
 * @property float  $total
 * @property float  $tax_free_total
 * @property string $currency
 * @property string $language
 * @property string $document_number
 * @property array $checkout_info
 * @property array $payment_data
 * @property array $shipping_data
 * @property string|null $order_status
 * @property string|null $transaction_status
 * @property string|null $delivery_status
 * @property int $date_paid
 * @property int $date_shipped
 * @property string $notes
 */
class ProductCollection extends Model
{
    /**
     * @var string
     */
    protected static $strTable = 'tl_iso_product_collection';

    public function __construct($objResult = null)
    {
        parent::__construct($objResult);

        if ($this->locked) {
            $this->blnPreventSaving = true;
        }
    }

    public function getOrderStatus(): string|null
    {
        return $this->order_status;
    }

    public function setOrderStatus(string $status): self
    {
        $this->order_status = $status;

        return $this;
    }

    public function getTransactionStatus(): string|null
    {
        return $this->transaction_status;
    }

    public function setTransactionStatus(string $status): self
    {
        $this->transaction_status = $status;

        return $this;
    }

    public function getDeliveryStatus(): string|null
    {
        return $this->delivery_status;
    }

    public function setDeliveryStatus(string $status): self
    {
        $this->delivery_status = $status;

        return $this;
    }

    /**
     * @return Collection<self>|null
     */
    public static function findByMember(int $memberId, string $type, int|null $storeId = null, array $options = []): Collection|null
    {
        $t = self::$strTable;

        $columns = ["$t.member=?", "$t.type=?"];
        $values = [$memberId, $type];

        if (null !== $storeId) {
            $columns[] = "$t.store_id=?";
            $values[] = $storeId;
        }

        return self::findBy($columns, $values, $options);
    }

    /**
     * @return Collection<self>|null
     */
    public static function findOneByMember(int $memberId, string $type, int $storeId = null, array $options = []): self|null
    {
        $t = self::$strTable;

        $columns = ["$t.member=?", "$t.type=?", "$t.store_id=?"];
        $values = [$memberId, $type, $storeId];

        return self::findOneBy($columns, $values, $options);
    }

    public static function findByIdentifier(string $identifier, string $type, int|null $storeId = null, array $options = []): self|null
    {
        $t = self::$strTable;

        $columns = ["$t.uniqid=?", "$t.type=?"];
        $values = [$identifier, $type];

        if (null !== $storeId) {
            $columns[] = "$t.store_id=?";
            $values[] = $storeId;
        }

        return self::findOneBy($columns, $values, $options);
    }
}
