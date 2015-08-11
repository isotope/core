<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model;


/**
 * Label defines a translation
 *
 * @property int    $id
 * @property int    $tstamp
 * @property string $language
 * @property string $label
 * @property string $replacement
 */
class Label extends \Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_label';

}
