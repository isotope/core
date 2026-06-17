<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Model;

use Contao\Model;
use Contao\Model\Collection;

/**
 * @property int         $id
 * @property int         $pid
 * @property int         $tstamp
 * @property string      $type
 * @property string      $identifier
 * @property mixed       $configuration
 * @property string      $sku
 * @property string      $label
 * @property int         $quantity
 * @property int         $level
 * @property float       $price
 * @property float       $tax_free_price
 * @property string      $price_data
 * @property string      $tax_id
 * @property int         $referencePage
 * @property string|null $unit_label
 */
class ProductCollectionItemModel extends Model
{
    /**
     * @var string
     */
    protected static $strTable = 'tl_iso_product_collection_item';

    /**
     * @return Collection<self>|null
     */
    public static function findByCollection(int $pid, array $options = []): Collection|null
    {
        return self::findBy('pid', $pid, $options);
    }

    public static function findOneByCollection(int $pid, string $identifier, array|null $configuration, array $options = []): self|null
    {
        return self::findOneBy(
            ['pid=?', 'identifier=?', 'configuration=?'],
            [$pid, $identifier, serialize($configuration)],
            $options,
        );
    }
}
