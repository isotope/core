<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Model;

use Contao\Model;

/**
 * @property int    $id
 * @property int    $tstamp
 * @property string $name
 * @property string $type
 * @property string $documentTitle
 * @property string $fileTitle
 * @property string $numberPrefix
 * @property int    $numberDigits
 * @property string $documentTpl
 * @property int    $gallery
 */
class DocumentModel extends Model
{
    /**
     * @var string
     */
    protected static $strTable = 'tl_iso_document';
}
