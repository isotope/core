<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Model;

use Contao\Model;

/**
 * @property string $name
 * @property string $amount
 * @property string $label
 */
class BasePrice extends Model
{
    /**
     * @var string
     */
    protected static $strTable = 'tl_iso_baseprice';
}
