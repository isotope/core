<?php

namespace Isotope\CoreBundle\Model;

use Contao\Model;

/**
 * @property int    $id
 * @property int    $pid
 * @property int    $tstamp
 * @property string $language
 * @property int    $dateAdded
 * @property int    $type
 * @property array  $inherit
 * @property bool   $fallback
 * @property string $alias
 * @property string $sku
 * @property string $name
 * @property string $teaser
 * @property string $description
 * @property string $meta_title
 * @property string $meta_description
 * @property string $price
 * @property string $prices
 * @property bool   $shipping_exempt
 * @property array  $images
 * @property bool   $protected
 * @property array  $groups
 * @property array  $cssID
 * @property bool   $published
 * @property string $start
 * @property string $stop
 */
class Product extends Model
{
    /**
     * @var string
     */
    protected static $strTable = 'tl_iso_product';
}
