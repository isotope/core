<?php

namespace Isotope\CoreBundle\Model;

use Contao\Model;

/**
 * @property int         $id
 * @property int         $tstamp
 * @property string      $name
 * @property bool        $fallback
 * @property int         $includes
 * @property string      $label
 * @property string|null $rates
 */
class TaxClassModel extends Model
{
    /**
     * @var string
     */
    protected static $strTable = 'tl_iso_tax_class';

    public static function findFallback(array $options = array()): self|null
    {
        return static::findOneBy('fallback', '1', $options);
    }
}
