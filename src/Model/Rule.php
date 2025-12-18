<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Model;

use Contao\Model;

/**
 * @property int    $id
 * @property int    $tstamp
 * @property string $name
 * @property bool   $enabled
 */
class Rule extends Model
{
    /**
     * @var string
     */
    protected static $strTable = 'tl_iso_rule';
}
