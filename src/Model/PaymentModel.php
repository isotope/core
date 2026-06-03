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
 * @property string $processing_status
 * @property string $paid_status
 * @property string $failed_status
 * @property int    $rule
 * @property string $shipping_modules
 * @property string $product_types
 * @property string $product_types_condition
 * @property string $price
 * @property int    $tax_class
 * @property bool   $debug
 * @property bool   $logging
 * @property bool   $enabled
 * @property string $paypal_account
 */
class PaymentModel extends Model
{
    /**
     * @var string
     */
    protected static $strTable = 'tl_iso_payment';
}
