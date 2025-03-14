<?php

namespace Isotope\CoreBundle\Model;

use Contao\Model;

/**
 * @property int    $id
 * @property int    $tstamp
 * @property string $type
 * @property string $name
 * @property string $label
 * @property string $note
 * @property string $price
 * @property int    $tax_class
 * @property string $quantity_mode
 * @property int    $minimum_quantity
 * @property int    $maximum_quantity
 * @property float  $minimum_total
 * @property float  $maximum_total
 * @property string $product_types
 * @property string $product_types_condition
 * @property string $config_ids
 * @property string $countries
 * @property bool   $protected
 * @property array  $groups
 * @property bool   $debug
 * @property bool   $logging
 * @property bool   $enabled
 * @property int    $new_order_status
 * @property string $shipping_modules
 */
class Payment extends Model
{
    /**
     * @var string
     */
    protected static $strTable = 'tl_iso_payment';
}
