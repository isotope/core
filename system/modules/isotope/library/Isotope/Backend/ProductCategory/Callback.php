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

namespace Isotope\Backend\ProductCategory;

use Isotope\Backend\Product\Label;
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

        return sprintf(
            '<span style="display:block;float:left;width:50px;">%s</span><span style="display:block;float:left;margin: 0 0 0 10px;padding: 18px 0;">%s</span>',
            Label::generateImage($objProduct),
            $objProduct->name
        );
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

        return '<a href="contao/main.php?do=feRedirect&page=' . $objPage->id . '" target="_blank" class="header_preview" title="' . specialchars($title) . '"' . $attributes . '>' . $label . '</a>';
    }
}
