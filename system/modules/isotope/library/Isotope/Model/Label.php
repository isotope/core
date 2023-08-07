<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model;


use Contao\Model;

/**
 * Label defines a translation
 *
 * @property int    $id
 * @property int    $tstamp
 * @property string $language
 * @property string $label
 * @property string $replacement
 */
class Label extends Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_label';

}
