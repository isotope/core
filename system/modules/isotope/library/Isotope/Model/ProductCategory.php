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


class ProductCategory extends \Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_product_category';


    /**
     * Find categories by product id if the respective page is published
     *
     * @param int   $intProduct
     * @param array $arrOptions
     *
     * @return \Model\Collection|null
     */
    public static function findByPidForPublishedPages($intProduct, array $arrOptions = array())
    {
        $arrOptions['eager'] = true;
        $arrOptions['having'] = "page_id__type!='error_403' AND page_id__type!='error_404'";

        if (!BE_USER_LOGGED_IN) {
            $time = time();
            $arrOptions['having'] .= " AND (page_id__start='' OR page_id__start<$time) AND (page_id__stop='' OR page_id__stop>$time) AND page_id__published='1'";
        }

        return parent::findBy('pid', $intProduct, $arrOptions);
    }
}
