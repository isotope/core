<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\Rule;

use Contao\Backend;
use Contao\BackendUser;
use Contao\Controller;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\Database;
use Contao\DataContainer;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Isotope\Haste\Util\Format;


class Callback extends Backend
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Return an array of enabled rules but not the active one.
     */
    public function getRules($dc)
    {
        $arrRules = array();
        $objRules = Database::getInstance()->execute("SELECT * FROM tl_iso_rule WHERE enabled='1' AND id!={$dc->id}");

        while ($objRules->next()) {
            $arrRules[$objRules->id] = $objRules->name;
        }

        return $arrRules;
    }

    /**
     * Load rule restrictions from linked table
     */
    public function loadRestrictions($varValue, $dc)
    {
        $varValue = Database::getInstance()->execute("SELECT object_id FROM tl_iso_rule_restriction WHERE pid={$dc->activeRecord->id} AND type='{$dc->field}'")->fetchEach('object_id');

        if (!empty($GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['csv'])) {
            $varValue = implode($GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['csv'], $varValue);
        }

        return $varValue;
    }

    /**
     * Save rule restrictions to linked table. Only update what necessary to prevent the IDs from increasing on every save_callback
     */
    public function saveRestrictions($varValue, $dc)
    {
        if (!empty($GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['csv'])) {
            $arrNew = explode($GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['csv'], $varValue);
        } else {
            $arrNew = StringUtil::deserialize($varValue);
        }

        if (!\is_array($arrNew) || empty($arrNew)) {
            Database::getInstance()->query("DELETE FROM tl_iso_rule_restriction WHERE pid={$dc->activeRecord->id} AND type='{$dc->field}'");

        } else {
            $arrOld = Database::getInstance()->execute("SELECT object_id FROM tl_iso_rule_restriction WHERE pid={$dc->activeRecord->id} AND type='{$dc->field}'")->fetchEach('object_id');

            $arrInsert = array_diff($arrNew, $arrOld);
            $arrDelete = array_diff($arrOld, $arrNew);

            if (!empty($arrDelete)) {
                Database::getInstance()->query("DELETE FROM tl_iso_rule_restriction WHERE pid={$dc->activeRecord->id} AND type='{$dc->field}' AND object_id IN (" . implode(',', $arrDelete) . ")");
            }

            if (!empty($arrInsert)) {
                $time = time();
                Database::getInstance()->query("INSERT INTO tl_iso_rule_restriction (pid,tstamp,type,object_id) VALUES ({$dc->id}, $time, '{$dc->field}', " . implode("), ({$dc->id}, $time, '{$dc->field}', ", $arrInsert) . ")");
            }
        }

        return '';
    }

    /**
     * Get attributes that can be filtered
     *
     * @param   DataContainer
     * @return  array
     */
    public function getAttributeNames($dc)
    {
        $arrAttributes = array();

        foreach ($GLOBALS['TL_DCA']['tl_iso_product']['fields'] as $attribute => $config) {
            if (!empty($config['attributes']['legend']) && $attribute !== 'pages' && ($config['inputType'] ?? null) !== 'mediaManager') {
                $arrAttributes[$attribute] = Format::dcaLabel('tl_iso_product', $attribute);
            }
        }

        asort($arrAttributes);

        return $arrAttributes;
    }

    /**
     * Initialize the attribute value field
     *
     * @param    DataContainer
     * @return void
     */
    public function loadAttributeValues($dc)
    {
        if (Input::get('act') == 'edit') {
            $this->loadDataContainer('tl_iso_product');
            System::loadLanguageFile('tl_iso_product');

            $objRule = Database::getInstance()->execute("SELECT * FROM tl_iso_rule WHERE id=" . (int) $dc->id);

            if ($objRule->productRestrictions == 'attribute' && $objRule->attributeName != '') {
                $GLOBALS['TL_DCA']['tl_iso_rule']['fields']['attributeValue'] = array_merge($GLOBALS['TL_DCA']['tl_iso_product']['fields'][$objRule->attributeName], $GLOBALS['TL_DCA']['tl_iso_rule']['fields']['attributeValue']);
            }
        }
    }
}
