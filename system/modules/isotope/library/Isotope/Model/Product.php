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
    protected static function buildQueryString(array $arrOptions, $arrJoinAliases=array('t'=>'tl_iso_producttypes'))
    {
        $objBase = new \DcaExtractor($arrOptions['table']);

        $arrJoins = array();
        $arrFields = array($arrOptions['table'] . ".*", "'".$GLOBALS['TL_LANGUAGE']."' AS language");

        foreach ($GLOBALS['ISO_CONFIG']['multilingual'] as $attribute)
        {
            $arrFields[] = "IFNULL(translation.$attribute, " . $arrOptions['table'] . ".$attribute) AS $attribute";
        }

        foreach ($GLOBALS['ISO_CONFIG']['fetch_fallback'] as $attribute)
        {
            $arrFields[] = "{$arrOptions['table']}.$attribute AS {$attribute}_fallback";
        }

        $arrFields[] = "c.sorting";

        $arrJoins[] = " LEFT OUTER JOIN tl_iso_product_categories c ON {$arrOptions['table']}.id=c.pid";
        $arrJoins[] = " LEFT OUTER JOIN " . $arrOptions['table'] . " translation ON " . $arrOptions['table'] . ".id=translation.pid AND translation.language='" . $GLOBALS['TL_LANGUAGE'] . "'";


        if ($objBase->hasRelations()) {

            $intCount = 0;

            foreach ($objBase->getRelations() as $strKey=>$arrConfig)
            {
                // Automatically join the single-relation records
                if ($arrConfig['load'] == 'eager' || $arrOptions['eager'])
                {
                    if ($arrConfig['type'] == 'hasOne' || $arrConfig['type'] == 'belongsTo')
                    {
                        $key = array_search($arrConfig['table'], $arrJoinAliases);
                        if (false !== $key) {
                            $strJoinAlias = $key;
                            unset($arrJoinAliases[$key]);
                        } else {
                            ++$intCount;
                            $strJoinAlias = 'j' . $intCount;
                        }

                        $objRelated = new \DcaExtractor($arrConfig['table']);

                        foreach (array_keys($objRelated->getFields()) as $strField)
                        {
                            $arrFields[] = $strJoinAlias . '.' . $strField . ' AS ' . $strKey . '__' . $strField;
                        }

                        $arrJoins[] = " LEFT JOIN " . $arrConfig['table'] . " $strJoinAlias ON " . $arrOptions['table'] . "." . $strKey . "=$strJoinAlias.id";
                    }
                }
            }
        }

        // Generate the query
        $strQuery = "SELECT " . implode(', ', $arrFields) . " FROM " . $arrOptions['table'] . implode("", $arrJoins);

        // Where condition
        $arrOptions['value'] = (array) $arrOptions['value'];

        if (!is_array($arrOptions['column']))
        {
            $arrOptions['column'] = array($arrOptions['table'] . '.' . $arrOptions['column'].'=?');
        }

        // The model must never find a language record
        $arrOptions['column'][] = "{$arrOptions['table']}.language=''";

        $strQuery .= " WHERE " . implode(" AND ", $arrOptions['column']);

        // Group by
        if ($arrOptions['group'] !== null)
        {
            $strQuery .= " GROUP BY " . $arrOptions['group'];
        }

        // Order by
        if ($arrOptions['order'] !== null)
        {
            $strQuery .= " ORDER BY " . $arrOptions['order'];
        }

        return $strQuery;
    }
}
