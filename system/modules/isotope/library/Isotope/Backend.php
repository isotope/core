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

namespace Isotope;

use Backend as Contao_Backend;
use Isotope\Model\Config;
use Isotope\Model\Group;
use Isotope\Model\OrderStatus;


/**
 * Class Isotope\Backend
 *
 * Provide methods to handle Isotope back end components.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 * @author     Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 */
class Backend extends Contao_Backend
{

    /**
     * Truncate the product cache table if a product is changed
     * Second parameter and return value is to use the method as save_callback
     * @param mixed
     * @return mixed
     */
    public static function truncateProductCache($varValue = null)
    {
        \Isotope\Model\ProductCache::purge();

        return $varValue;
    }


    /**
     * Truncate the request cache table
     */
    public static function truncateRequestCache()
    {
        \Isotope\Model\RequestCache::purge();
    }


    /**
     * Get array of subdivisions, delay loading of file if not necessary
     * @param object
     * @return array
     */
    public static function getSubdivisions()
    {
        static $arrSubdivisions = null;

        if (null === $arrSubdivisions) {

            \System::loadLanguageFile('subdivisions');

            foreach ($GLOBALS['TL_LANG']['DIV'] as $strCountry => $arrSubdivision) {
                foreach ($arrSubdivision as $strCode => $varValue) {
                    if (is_array($varValue)) {
                        $strGroup = $varValue[''];
                        unset($varValue['']);

                        $arrSubdivisions[$strCountry][$strCode][$strGroup] = $varValue;

                        continue;
                    }

                    $arrSubdivisions[$strCountry][$strCode] = $varValue;
                }
            }
        }

        return $arrSubdivisions;
    }


    /**
     * DCA for setup module tables is "closed" to hide the "new" button. Re-enable it when clicking on a button
     * @param object
     */
    public function initializeSetupModule($dc)
    {
        if (\Input::get('act') != '') {
            $GLOBALS['TL_DCA'][$dc->table]['config']['closed'] = false;
        }
    }


    /**
     * Return all Isotope modules
     * @return array
     */
    public function getIsotopeModules()
    {
        $arrModules = array();

        foreach ($GLOBALS['ISO_MOD'] as $k => $v) {
            $arrModules[$k] = array_keys($v);
        }

        return $arrModules;
    }


    /**
     * List template from all themes, show theme name
     * @param string
     * @param int
     * @return array
     */
    public static function getTemplates($strPrefix)
    {
        $arrTemplates = \Controller::getTemplateGroup($strPrefix);

        // Try to select the shop configs
        try {
            $objConfig = Config::findAll(array('order' => 'name'));
        } catch (\Exception $e) {
            $objConfig = null;
        }

        // Add the shop config templates
        if (null !== $objConfig) {
            while ($objConfig->next()) {
                if ($objConfig->templateGroup != '') {
                    $arrConfigTemplates = glob(TL_ROOT . '/' . $objConfig->templateGroup . '/' . $strPrefix . '*');

                    if (is_array($arrConfigTemplates)) {
                        foreach ($arrConfigTemplates as $strFile) {

                            $strTemplate = basename($strFile, strrchr($strFile, '.'));

                            if (!isset($arrTemplates[$strTemplate])) {
                                $arrTemplates[$strTemplate] = $strTemplate;
                            } else {
                                $arrTemplates[$strTemplate] = substr($arrTemplates[$strTemplate], 0, -1) . ', ' . sprintf($GLOBALS['TL_LANG']['MSC']['templatesConfig'], $objConfig->name) . ')';
                            }
                        }
                    }
                }
            }
        }

        return $arrTemplates;
    }


    /**
     * Get order status and return it as array
     * @return array
     */
    public static function getOrderStatus()
    {
        $arrStatus = array();
        if (($objStatus = OrderStatus::findAll(array('order' => 'sorting'))) !== null) {
            while ($objStatus->next()) {
                $arrStatus[$objStatus->id] = $objStatus->current()->getName();
            }
        }

        return $arrStatus;
    }


    /**
     * Show messages for new order status
     * @return string
     */
    public function getOrderMessages()
    {
        if (!\Database::getInstance()->tableExists(\Isotope\Model\OrderStatus::getTable()) || !\BackendUser::getInstance()->hasAccess('iso_orders', 'modules')) {
            return '';
        }

        // Can't see any orders if user does not have access to any shop config
        $strConfig = '';
        if (!\BackendUser::getInstance()->isAdmin) {
            $arrConfigs = \BackendUser::getInstance()->iso_configs;

            if (empty($arrConfigs) || !is_array($arrConfigs)) {
                return '';
            }

            $strConfig = "AND c.config_id IN (" . implode(',', $arrConfigs) . ")";
        }

        $arrMessages = array();
        $objOrders   = \Database::getInstance()->query("SELECT COUNT(*) AS total, s.name FROM " . \Isotope\Model\ProductCollection::getTable() . " c LEFT JOIN " . \Isotope\Model\OrderStatus::getTable() . " s ON c.order_status=s.id WHERE c.type='order' AND s.welcomescreen='1' $strConfig GROUP BY s.id");

        while ($objOrders->next()) {
            $arrMessages[] = '<p class="tl_new">' . sprintf($GLOBALS['TL_LANG']['MSC']['newOrders'], $objOrders->total, $objOrders->name) . '</p>';
        }

        return implode("\n", $arrMessages);
    }


    /**
     * Returns an array of all allowed product IDs and variant IDs for the current backend user
     * @return array|bool
     * @deprecated will be removed in Isotope 3.0
     */
    public static function getAllowedProductIds()
    {
        return \Isotope\Backend\Product\Permission::getAllowedIds();
    }


    /**
     * Check the Ajax pre actions
     * @param string
     * @param object
     * @return string
     */
    public function executePreActions($action)
    {
        switch ($action) {
            // Move the product
            case 'moveProduct':
                \Session::getInstance()->set('iso_products_gid', intval(\Input::post('value')));
                \Controller::redirect(html_entity_decode(\Input::post('redirect')));
                break;

            // Move multiple products
            case 'moveProducts':
                \Session::getInstance()->set('iso_products_gid', intval(\Input::post('value')));
                exit;
                break;

            // Filter the groups
            case 'filterGroups':
                \Session::getInstance()->set('iso_products_gid', intval(\Input::post('value')));
                $this->reload();
                break;

            // Filter the pages
            case 'filterPages':
                $filter = \Session::getInstance()->get('filter');
                $filter['tl_iso_product']['iso_page'] = (int) \Input::post('value');
                \Session::getInstance()->set('filter', $filter);
                $this->reload();
                break;

            // Sorty products by page
            case 'sortByPage':
                if (\Input::post('value') > 0) {
                    \Controller::redirect(\Backend::addToUrl('table=tl_iso_product_category&amp;id=' . (int) \Input::post('value') . '&amp;page_id=' . (int) \Input::post('value')));
                } else {
                    \Controller::reload();
                }
        }
    }


    /**
     * Check the Ajax post actions
     * @param string
     * @param object
     * @return string
     */
    public function executePostActions($action, $dc)
    {
        switch ($action) {
            case 'loadProductTree':
                $arrData['strTable'] = $dc->table;
                $arrData['id']       = strlen($this->strAjaxName) ? $this->strAjaxName : $dc->id;
                $arrData['name']     = \Input::post('name');

                $this->loadDataContainer($dc->table);
                $arrData = array_merge($GLOBALS['TL_DCA'][$dc->table]['fields'][$arrData['name']]['eval'], $arrData);

                $objWidget = new $GLOBALS['BE_FFL']['productTree']($arrData, $dc);

                echo json_encode(array
                                 (
                                 'content' => $objWidget->generateAjax($this->strAjaxId, \Input::post('field'), intval(\Input::post('level'))),
                                 'token'   => REQUEST_TOKEN
                                 ));
                exit;

            case 'loadProductGroupTree':
                $arrData['strTable'] = $dc->table;
                $arrData['id']       = strlen($this->strAjaxName) ? $this->strAjaxName : $dc->id;
                $arrData['name']     = \Input::post('name');

                $objWidget = new $GLOBALS['BE_FFL']['productGroupSelector']($arrData, $dc);
                echo $objWidget->generateAjax($this->strAjaxId, \Input::post('field'), intval(\Input::post('level')));
                exit;

            case 'uploadMediaManager':
                $arrData['strTable'] = $dc->table;
                $arrData['id']       = strlen($this->strAjaxName) ? $this->strAjaxName : $dc->id;
                $arrData['name']     = \Input::post('name');

                $objWidget = new $GLOBALS['BE_FFL']['mediaManager']($arrData, $dc);
                $strFile   = $objWidget->validateUpload();

                if ($objWidget->hasErrors()) {
                    $arrResponse = array('success' => false, 'error' => $objWidget->getErrorAsString(), 'preventRetry' => true);
                } else {
                    $arrResponse = array('success' => true, 'file' => $strFile);
                }

                echo json_encode($arrResponse);
                exit;

            case 'reloadMediaManager':
                $intId    = \Input::get('id');
                $strField = $dc->field = \Input::post('name');
                $this->import('Database');

                // Handle the keys in "edit multiple" mode
                if (\Input::get('act') == 'editAll') {
                    $intId    = preg_replace('/.*_([0-9a-zA-Z]+)$/', '$1', $strField);
                    $strField = preg_replace('/(.*)_[0-9a-zA-Z]+$/', '$1', $strField);
                }

                // The field does not exist
                if (!isset($GLOBALS['TL_DCA'][$dc->table]['fields'][$strField])) {
                    $this->log('Field "' . $strField . '" does not exist in DCA "' . $dc->table . '"', __METHOD__, TL_ERROR);
                    header('HTTP/1.1 400 Bad Request');
                    die('Bad Request');
                }

                $objRow   = null;
                $varValue = null;

                // Load the value
                if ($GLOBALS['TL_DCA'][$dc->table]['config']['dataContainer'] == 'File') {
                    $varValue = $GLOBALS['TL_CONFIG'][$strField];
                } elseif ($intId > 0 && $this->Database->tableExists($dc->table)) {
                    $objRow = $this->Database->prepare("SELECT * FROM " . $dc->table . " WHERE id=?")
                                             ->execute($intId);

                    // The record does not exist
                    if ($objRow->numRows < 1) {
                        $this->log('A record with the ID "' . $intId . '" does not exist in table "' . $dc->table . '"', __METHOD__, TL_ERROR);
                        header('HTTP/1.1 400 Bad Request');
                        die('Bad Request');
                    }

                    $varValue         = $objRow->$strField;
                    $dc->activeRecord = $objRow;
                }

                $varValue = \Input::post('value', true);

                // Include the uploaded files in the value
                if (\Input::post('files', true)) {
                    $varValue['files'] = \Input::post('files', true);
                }

                // Build the attributes based on the "eval" array
                $arrAttribs = $GLOBALS['TL_DCA'][$dc->table]['fields'][$strField]['eval'];

                $arrAttribs['id']           = $dc->field;
                $arrAttribs['name']         = $dc->field;
                $arrAttribs['value']        = $varValue;
                $arrAttribs['strTable']     = $dc->table;
                $arrAttribs['strField']     = $strField;
                $arrAttribs['activeRecord'] = $dc->activeRecord;

                $objWidget = new $GLOBALS['BE_FFL']['mediaManager']($arrAttribs);
                echo $objWidget->generate();
                exit;
        }
    }

    /**
     * Load type agent model help
     * @param   string
     */
    public function loadTypeAgentHelp($strTable)
    {
        switch ($strTable) {
            case 'tl_iso_producttype':
                $strField = 'class';
                $strKey = 'tl_iso_product';
                break;

            default:
                $strField = 'type';
                $strKey = $strTable;
                break;
        }

        if (
            \Environment::get('script') !== 'contao/help.php' ||
            !isset($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]) ||
            !is_subclass_of(\Model::getClassFromTable($strKey), 'Isotope\Model\TypeAgent')
        ) {
            return;
        }

        $arrField = &$GLOBALS['TL_DCA'][$strTable]['fields'][$strField];

        // Get the field type
        $strClass = $GLOBALS['BE_FFL'][$arrField['inputType']];

        // Abort if the class is not defined
        if (!class_exists($strClass)) {
            return;
        }

        $arrFieldComplete = $strClass::getAttributesFromDca($arrField, $strField);

        if (
            !$arrFieldComplete ||
            !$arrFieldComplete['helpwizard'] ||
            !is_array($arrFieldComplete['options']) ||
            $arrField['explanation'] != '' ||
            isset($GLOBALS['TL_LANG']['XPL']['type'])
        ) {
            return;
        }

        // try to load a type agent model help description
        $arrField['explanation'] = 'type';
        foreach ($arrFieldComplete['options'] as $arrOption) {
            $arrLabel = $GLOBALS['TL_LANG']['MODEL'][$strKey . '.' . $arrOption['value']];
            if ($arrLabel) {
                $GLOBALS['TL_LANG']['XPL']['type'][] = $arrLabel;
            }
        }
    }


    /**
     * Adjust the product groups manager view
     * @param object
     */
    public function adjustGroupsManager($objTemplate)
    {
        if (\Input::get('popup') && \Input::get('do') == 'iso_products' && \Input::get('table') == Group::getTable() && $objTemplate->getName() == 'be_main') {
            $objTemplate->managerHref = ampersand($this->Session->get('groupPickerRef'));
            $objTemplate->manager     = $GLOBALS['TL_LANG']['MSC']['groupPickerHome'];
        }
    }
}
