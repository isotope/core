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

namespace Isotope\Module;

/**
 * AbstractProductFilter provides basic methods to handle product filtering
 *
 * @property array  iso_searchFields
 * @property string iso_searchAutocomplete
 * @property array  iso_filterFields
 * @property bool   iso_filterHideSingle
 * @property array  iso_sortingFields
 * @property string iso_listingSortField
 * @property string iso_listingSortDirection
 * @property bool   iso_enableLimit
 * @property string iso_filterTpl
 */
abstract class AbstractProductFilter extends Module
{

    /**
     * Constructor.
     *
     * @param object $objModule
     * @param string $strColumn
     */
    public function __construct($objModule, $strColumn = 'main')
    {
        parent::__construct($objModule, $strColumn);

        $this->iso_filterFields  = deserialize($this->iso_filterFields);
        $this->iso_sortingFields = deserialize($this->iso_sortingFields);
        $this->iso_searchFields  = deserialize($this->iso_searchFields);

        if (!is_array($this->iso_filterFields)) {
            $this->iso_filterFields = array();
        }

        if (!is_array($this->iso_sortingFields)) {
            $this->iso_sortingFields = array();
        }

        if (!is_array($this->iso_searchFields)) {
            $this->iso_searchFields = array();
        }
    }

    /**
     * Returns an array of attribute values found in the product table
     *
     * @param string $attribute
     * @param array  $categories
     * @param string $sqlWhere
     *
     * @return array
     */
    protected function getUsedValuesForAttribute($attribute, array $categories, $sqlWhere = '')
    {
        $values     = array();
        $published1 = '';
        $published2 = '';

        if ('' != $sqlWhere) {
            $sqlWhere = " AND " . $sqlWhere;
        }

        if (BE_USER_LOGGED_IN !== true) {
            $time       = time();
            $published1 = "AND p1.published='1' AND (p1.start='' OR p1.start<$time) AND (p1.stop='' OR p1.stop>$time)";
            $published2 = "AND (p1.pid=0 OR (
                p2.published='1' AND (p2.start='' OR p2.start<$time) AND (p2.stop='' OR p2.stop>$time)
            ))";
        }

        $result = \Database::getInstance()->execute(
            "SELECT DISTINCT p1.$attribute AS options FROM tl_iso_product p1
                    LEFT OUTER JOIN tl_iso_product p2 ON p1.pid=p2.id
                    WHERE
                        p1.language=''
                        AND p1.$attribute!=''
                        " . $published1 . "
                        AND (
                            p1.id IN (
                                SELECT pid
                                FROM tl_iso_product_category
                                WHERE page_id IN (" . implode(',', $categories) . ")
                            )
                            OR p1.pid IN (
                                SELECT pid
                                FROM tl_iso_product_category
                                WHERE page_id IN (" . implode(',', $categories) . ")
                            )
                        )
                        " . $published2 . "
                        " . $sqlWhere
        );

        while ($result->next()) {
            $values = array_merge($values, deserialize($result->options, true));
        }

        return $values;
    }

    /**
     * Get the sorting labels (asc/desc) for an attribute
     *
     * @param string
     *
     * @return array
     */
    protected function getSortingLabels($field)
    {
        $arrData = &$GLOBALS['TL_DCA']['tl_iso_product']['fields'][$field];

        switch ($arrData['eval']['rgxp']) {
            case 'price':
            case 'digit':
                return array($GLOBALS['TL_LANG']['MSC']['low_to_high'], $GLOBALS['TL_LANG']['MSC']['high_to_low']);

            case 'date':
            case 'time':
            case 'datim':
                return array($GLOBALS['TL_LANG']['MSC']['old_to_new'], $GLOBALS['TL_LANG']['MSC']['new_to_old']);
        }

        return array($GLOBALS['TL_LANG']['MSC']['a_to_z'], $GLOBALS['TL_LANG']['MSC']['z_to_a']);
    }
}
