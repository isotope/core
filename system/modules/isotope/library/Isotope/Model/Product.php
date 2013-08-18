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

use Isotope\Isotope;


/**
 * The basic Isotope product model
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
abstract class Product extends TypeAgent
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_iso_products';

    /**
     * Interface to validate attribute
     * @var string
     */
    protected static $strInterface = '\Isotope\Interfaces\IsotopeProduct';

    /**
     * List of types (classes) for this model
     * @var array
     */
    protected static $arrModelTypes = array();


    /**
     * Return select statement to load product data including multilingual fields
     * @param array an array of columns
     * @return string
     */
    public static function getSelectStatement($arrColumns=false)
    {
        static $strSelect = '';

        if ($strSelect == '' || $arrColumns !== false)
        {
            $arrSelect = ($arrColumns !== false) ? $arrColumns : array('p1.*');
            $arrSelect[] = "'".$GLOBALS['TL_LANGUAGE']."' AS language";

            foreach ($GLOBALS['ISO_CONFIG']['multilingual'] as $attribute)
            {
                if ($arrColumns !== false && !in_array('p1.'.$attribute, $arrColumns))
                    continue;

                $arrSelect[] = "IFNULL(p2.$attribute, p1.$attribute) AS {$attribute}";
            }

            foreach ($GLOBALS['ISO_CONFIG']['fetch_fallback'] as $attribute)
            {
                if ($arrColumns !== false && !in_array('p1.'.$attribute, $arrColumns))
                    continue;

                $arrSelect[] = "p1.$attribute AS {$attribute}_fallback";
            }

            $strQuery = "
SELECT
    " . implode(', ', $arrSelect) . ",
    t.class AS product_class,
    c.sorting
FROM tl_iso_products p1
INNER JOIN tl_iso_producttypes t ON t.id=p1.type
LEFT OUTER JOIN tl_iso_products p2 ON p1.id=p2.pid AND p2.language='" . $GLOBALS['TL_LANGUAGE'] . "'
LEFT OUTER JOIN tl_iso_product_categories c ON p1.id=c.pid";

            if ($arrColumns !== false)
            {
                return $strQuery;
            }

            $strSelect = $strQuery;
        }

        return $strSelect;
    }
}
