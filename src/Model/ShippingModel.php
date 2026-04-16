<?php

declare(strict_types=1);

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
 * @property int    $rule
 * @property string $shipping_weight
 * @property float  $minimum_weight
 * @property float  $maximum_weight
 * @property string $product_types
 * @property string $product_types_condition
 * @property bool   $debug
 * @property bool   $logging
 * @property bool   $enabled
 * @property bool   $inherit
 * @property string $group_methods
 * @property string $group_calculation
 * @property string $flatCalculation
 * @property string $flatProductTypes
 * @property string $flatWeight
 * @property string $productCalculation
 */
class ShippingModel extends Model
{
    /**
     * @var string
     */
    protected static $strTable = 'tl_iso_shipping';
}
