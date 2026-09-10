<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Model;

use Contao\Model;

/**
 * @property int    $id
 * @property int    $pid
 * @property int    $tstamp
 * @property int    $type
 * @property string $document_number
 * @property string $file_id
 * @property string $file_name
 * @property string $mime_type
 */
class OrderDocumentModel extends Model
{
    /**
     * @var string
     */
    protected static $strTable = 'tl_iso_order_document';
}
