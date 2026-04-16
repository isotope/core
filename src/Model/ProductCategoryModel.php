<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Model;

use Contao\Model;

/**
 * @property int $id
 * @property int $pid
 * @property int $sorting
 * @property int $tstamp
 * @property int $product_id
 * @property int $product_sorting
 */
class ProductCategoryModel extends Model
{
    /**
     * @var string
     */
    protected static $strTable = 'tl_iso_product_category';
}
