<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Model;

use Contao\Database;
use Contao\Model;
use Contao\Model\Collection;
use Isotope\CoreBundle\Product\CoreProduct;

/**
 * @property int    $pid
 * @property int    $tstamp
 * @property int    $sorting
 * @property int    $category
 * @property string $products
 */
class RelatedProductModel extends Model
{
    /**
     * @var string
     */
    protected static $strTable = 'tl_iso_related_product';

    /**
     * Find related products of a product.
     *
     * @return Collection<RelatedProductModel>|null
     */
    public static function findByProductAndCategories(CoreProduct $objProduct, array $arrCategories, array $arrOptions = []): Collection|null
    {
        $t = static::$strTable;

        if ($objProduct->isVariant()) {
            $pid = "($t.pid=? OR $t.pid=?)";
            $value = [$objProduct->pid, $objProduct->id];
        } else {
            $pid = "$t.pid=?";
            $value = [$objProduct->id];
        }

        $arrOptions = array_merge(
            [
                'column' => [$pid, "$t.category IN (".implode(',', $arrCategories).')'],
                'value' => $value,
                'order' => Database::getInstance()->findInSet("$t.category", $arrCategories),
                'return' => 'Collection',
            ],
            $arrOptions,
        );

        return static::find($arrOptions);
    }
}
