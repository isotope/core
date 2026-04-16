<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Model;

use Contao\Model;

/**
 * @property int          $id
 * @property int          $tstamp
 * @property string       $name
 * @property string       $field_name
 * @property string       $type
 * @property string       $legend
 * @property string       $description
 * @property string       $optionsSource
 * @property string|array $options
 * @property string       $foreignKey
 * @property bool         $includeBlankOption
 * @property string       $blankOptionLabel
 * @property bool         $variant_option
 * @property bool         $customer_defined
 * @property bool         $be_search
 * @property bool         $be_filter
 * @property bool         $mandatory
 * @property bool         $fe_filter
 * @property bool         $fe_search
 * @property bool         $fe_sorting
 * @property bool         $multiple
 * @property int          $size
 * @property string       $extensions
 * @property string       $rte
 * @property bool         $multilingual
 * @property bool         $rgxp
 * @property bool         $placeholder
 * @property int          $minlength
 * @property int          $maxlength
 * @property bool         $datepicker
 */
class AttributeModel extends Model
{
    /**
     * @var string
     */
    protected static $strTable = 'tl_iso_attribute';
}
