<?php

namespace Isotope\CoreBundle\Model;

use Contao\Model;

/**
 * @property int    $id
 * @property int    $pid
 * @property int    $tstamp
 * @property string $name
 * @property string $label
 * @property string $address
 * @property string $countries
 * @property string $subdivisions
 * @property string $postalCodes
 * @property string $rate
 * @property int    $rule
 */
class TaxRateModel extends Model
{
    /**
     * @var string
     */
    protected static $strTable = 'tl_iso_tax_rate';
}
