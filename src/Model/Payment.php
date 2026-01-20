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
 * @property int $new_order_status
 * @property int    $rule
 * @property string $shipping_modules
 * @property string $product_types
 * @property string $product_types_condition
 * @property string $price
 * @property int $tax_class
 * @property bool   $debug
 * @property bool   $logging
 * @property bool   $enabled
 */
class Payment extends Model
{
    /**
     * @var string
     */
    protected static $strTable = 'tl_iso_payment';
}
