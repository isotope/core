<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 *
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */

namespace Isotope;


/**
 * Class tl_iso_shipping_options
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_iso_shipping_options extends \Backend
{

    /**
     * The current shipping class. Instantiated by the onload_callback.
     * @param object
     */
    protected $Shipping;


    /**
     * Instantiate the shipping module and set the palette
     * @param object
     * @return void
     */
    public function getModulePalette($dc)
    {
        if (\Input::get('act') == 'create')
        {
            return;
        }

        if (!strlen(\Input::get('act')) && !strlen(\Input::get('key')))
        {
            $objModule = $this->Database->execute("SELECT * FROM tl_iso_shipping_modules WHERE id=".$dc->id);
        }
        else
        {
            $objModule = $this->Database->execute("SELECT m.* FROM tl_iso_shipping_modules m, tl_iso_shipping_options o WHERE o.pid=m.id AND o.id=".$dc->id);
        }

        try {
            $this->Shipping = \Isotope\Factory\Shipping::build($objModule->type, $objModule->row());
            $this->Shipping->moduleOptionsLoad();
        } catch (Exception $e) {}
    }


    /**
     * Get a formatted listing for this row from shipping module class
     * @param array
     * @return string
     */
    public function listRow($row)
    {
        if (!is_object($this->Shipping))
        {
            return '';
        }

        return $this->Shipping->moduleOptionsList($row);
    }
}
