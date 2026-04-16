<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Model;

use Contao\Model;

/**
 * @property int    $id
 * @property int    $tstamp
 * @property string $name
 * @property string $amount
 * @property string $label
 */
class BasePriceModel extends Model
{
    /**
     * @var string
     */
    protected static $strTable = 'tl_iso_baseprice';
}
