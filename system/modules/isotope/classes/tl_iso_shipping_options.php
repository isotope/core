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
     * Instantiate the shipping module and load options
     * @param object
     * @return void
     */
    public function loadModuleOptions($dc)
    {
        if (\Input::get('act') == 'create') {
            return;
        }

        if (\Input::get('act') == '' && \Input::get('key') == '') {
            $this->Shipping = Shipping::findByPk($dc->id);
        } else {
            $this->Shipping = Shipping::findOneBy('id=(SELECT pid FROM tl_iso_shipping_options WHERE id=?)', $dc->id);
        }

        if (null !== $this->Shipping) {
            $this->Shipping->moduleOptionsLoad();
        }
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
