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
