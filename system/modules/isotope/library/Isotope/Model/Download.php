<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Model;


/**
 * Download model represents a file or folder download for a product
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class Download extends \Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_downloads';


    public function getExpirationTimestamp($intFrom=null)
    {
        if ($this->expires == '') {
            return null;
        }

        $arrExpires = deserialize($this->expires, true);

        if ($arrExpires['value'] == 0 || $arrExpires['unit'] == '') {
            return null;
        }

        return strtotime('+' . $arrExpires['value'] . ' ' . $arrExpires['unit'], $intFrom);
    }
}
