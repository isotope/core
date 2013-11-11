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

namespace Isotope\Backend\ProductCategory;

use Isotope\Model\Product;


class Callback extends \Backend
{

    /**
     * List the products
     * @param array
     * @return string
     */
    public function listRows($row)
    {
        $objProduct = Product::findByPk($row['pid']);

        return $objProduct->name;
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
        $objPage = \PageModel::findWithDetails(\Input::get('page_id'));

        if (null === $objPage) {
            return '';
        }

        return '<a href="contao/main.php?do=feRedirect&page=' . $objPage->id . '" target="_blank" class="header_preview" title="'.specialchars($title).'"'.$attributes.'>'.$label.'</a>';
    }
}
