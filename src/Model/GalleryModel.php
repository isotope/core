<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Model;

use Contao\Model;

/**
 * @property int         $id
 * @property int         $tstamp
 * @property string      $name
 * @property string      $type
 * @property string      $anchor
 * @property string|null $placeholder
 * @property string      $main_size
 * @property string      $gallery_size
 * @property string      $lightbox_size
 * @property string      $customTpl
 */
class GalleryModel extends Model
{
    /**
     * @var string
     */
    protected static $strTable = 'tl_iso_gallery';
}
