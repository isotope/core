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
