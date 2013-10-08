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

namespace Isotope\Backend\DCA;


/**
 * Class tl_iso_product_category
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_iso_product_category extends \Backend
{

    /**
     * List the products
     * @param array
     * @return string
     */
    public function listRows($row)
    {
        $this->loadDataContainer('tl_iso_product');
        \System::loadLanguageFile('tl_iso_product');

        $objProduct = \Database::getInstance()->prepare("SELECT * FROM tl_iso_product WHERE id=?")->limit(1)->execute($row['pid']);

        $this->import('Isotope\Backend\ProductCallbacks', 'ProductCallbacks');

        return implode(' ', $this->ProductCallbacks->getRowLabel($objProduct->row(), '', (object) array('table'=>'tl_iso_products'), array()));
    }


    /**
     * Return the page view button
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @param array
     * @return string
     */
    public function getPageViewButton($href, $label, $title, $class, $attributes, $table, $root)
    {
        $objPage = $this->getPageDetails(\Input::get('id'));

        if (is_object($objPage))
        {
            $href  = (\Environment::get('ssl') ? 'https://' : 'http://') . ($objPage->dns == '' ? \Environment::get('host') : $objPage->dns) . (TL_PATH == '' ? '' : TL_PATH) . '/';
            $href .= \Controller::generateFrontendUrl($objPage->row());

            return ' &#160; :: &#160; <a href="'.$href.'" target="_blank" class="header_preview" title="'.specialchars($title).'"'.$attributes.'>'.$label.'</a> ';
        }

        return '';
    }
}
