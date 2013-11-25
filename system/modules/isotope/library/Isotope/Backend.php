<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2013 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope;

use \Backend as Contao_Backend;
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
        $arrTemplates = array();

        // Get the default templates
        foreach (\TemplateLoader::getPrefixedFiles($strPrefix) as $strTemplate) {
            $arrTemplates[$strTemplate] = $strTemplate;
        }

        $arrCustomized = glob(TL_ROOT . '/templates/' . $strPrefix . '*');

        // Add the customized templates
        if (is_array($arrCustomized)) {
            foreach ($arrCustomized as $strFile) {

                $strTemplate = basename($strFile, strrchr($strFile, '.'));

                if (!isset($arrTemplates[$strTemplate])) {
                    $arrTemplates[''][$strTemplate] = $strTemplate;
                }
            }
        }

        // Do not look for back end templates in theme folders (see #5379)
        if ($strPrefix == 'be_') {
            return $arrTemplates;
        }

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

                    $strFolder          = sprintf($GLOBALS['TL_LANG']['MSC']['templatesConfig'], $objConfig->name);
                    $arrConfigTemplates = glob(TL_ROOT . '/' . $objConfig->templateGroup . '/' . $strPrefix . '*');

                    if (is_array($arrConfigTemplates)) {
                        foreach ($arrConfigTemplates as $strFile) {

                            $strTemplate = basename($strFile, strrchr($strFile, '.'));

                            if (!isset($arrTemplates[''][$strTemplate])) {
                                $arrTemplates[$strFolder][$strTemplate] = $strTemplate;
                            }
                        }
                    }
                }
            }
        }

        // Try to select the themes (see #5210)
        try {
            $objTheme = \ThemeModel::findAll(array('order' => 'name'));
        } catch (\Exception $e) {
            $objTheme = null;
        }

        // Add the theme templates
        if (null !== $objTheme) {
            while ($objTheme->next()) {
                if ($objTheme->templates != '') {

                    $strFolder         = sprintf($GLOBALS['TL_LANG']['MSC']['templatesTheme'], $objTheme->name);
                    $arrThemeTemplates = glob(TL_ROOT . '/' . $objTheme->templates . '/' . $strPrefix . '*');

                    if (is_array($arrThemeTemplates)) {
                        foreach ($arrThemeTemplates as $strFile) {

                            $strTemplate = basename($strFile, strrchr($strFile, '.'));

                            if (!isset($arrTemplates[''][$strTemplate])) {
                                $arrTemplates[$strFolder][$strTemplate] = $strTemplate;
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

            $strConfig = "AND o.config_id IN (" . implode(',', $arrConfigs) . ")";
        }

        $arrMessages = array();
        $objOrders   = \Database::getInstance()->query("SELECT COUNT(*) AS total, s.name FROM " . \Isotope\Model\ProductCollection::getTable() . " c LEFT JOIN " . \Isotope\Model\OrderStatus::getTable() . " s ON c.order_status=s.id WHERE c.type='Order' AND s.welcomescreen='1' $strConfig GROUP BY s.id");

        while ($objOrders->next()) {
            $arrMessages[] = '<p class="tl_new">' . sprintf($GLOBALS['TL_LANG']['MSC']['newOrders'], $objOrders->total, $objOrders->name) . '</p>';
        }

        return implode("\n", $arrMessages);
    }


    /**
     * Returns an array of all allowed product IDs and variant IDs for the current backend user
     * @return array|bool
     */
    public static function getAllowedProductIds()
    {
        $objUser = \BackendUser::getInstance();

        if ($objUser->isAdmin) {
            $arrProducts = true;
        } else {
            $arrNewRecords   = $_SESSION['BE_DATA']['new_records']['tl_iso_product'];
            $arrProductTypes = $objUser->iso_product_types;
            $arrGroups       = array();

            // Return false if there are no product types
            if (!is_array($arrProductTypes) || empty($arrProductTypes)) {
                return false;
            }

            // Find the user groups
            if (is_array($objUser->iso_groups) && count($objUser->iso_groups) > 0) {
                $arrGroups = array_merge($arrGroups, $objUser->iso_groups, \Database::getInstance()->getChildRecords($objUser->iso_groups, Group::getTable()));
            }

            $objProducts = \Database::getInstance()->execute("
                SELECT id FROM tl_iso_product
                WHERE pid=0
                    AND language=''
                    " . (empty($arrGroups) ? '' : "AND gid IN (" . implode(',', $arrGroups) . ")") . "
                    AND (
                        type IN (" . implode(',', $arrProductTypes) . ")" .
                        ((is_array($arrNewRecords) && !empty($arrNewRecords)) ? " OR id IN (".implode(',', $arrNewRecords).")" : '') .
                    ")
            ");

            if ($objProducts->numRows == 0) {
                return array();
            }

            $arrProducts = $objProducts->fetchEach('id');
            $arrProducts = array_merge($arrProducts, \Database::getInstance()->getChildRecords($arrProducts, 'tl_iso_product'));
        }

        // HOOK: allow extensions to define allowed products
        if (isset($GLOBALS['ISO_HOOKS']['getAllowedProductIds']) && is_array($GLOBALS['ISO_HOOKS']['getAllowedProductIds'])) {
            foreach ($GLOBALS['ISO_HOOKS']['getAllowedProductIds'] as $callback) {
                $objCallback = \System::importStatic($callback[0]);
                $arrAllowed  = $objCallback->$callback[1]();

                if ($arrAllowed === false) {
                    return false;
                } elseif (is_array($arrAllowed)) {
                    if ($arrProducts === true) {
                        $arrProducts = $arrAllowed;
                    } else {
                        $arrProducts = array_intersect($arrProducts, $arrAllowed);
                    }
                }
            }
        }

        // If all product are allowed, we don't need to filter
        if ($arrProducts === true || count($arrProducts) == \Database::getInstance()->execute("SELECT COUNT(id) as total FROM tl_iso_product")->total) {
            return true;
        }

        return $arrProducts;
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
                $this->Session->set('iso_products_gid', intval(\Input::post('value')));
                \Controller::redirect(html_entity_decode(\Input::post('redirect')));
                break;

            // Move multiple products
            case 'moveProducts':
                $this->Session->set('iso_products_gid', intval(\Input::post('value')));
                exit;
                break;

            // Filter the groups
            case 'filterGroups':
                $this->Session->set('iso_products_gid', intval(\Input::post('value')));
                $this->reload();
                break;

            // Filter the pages
            case 'filterPages':
                $filter = $this->Session->get('filter');
                $filter['tl_iso_product']['iso_page'] = (int) \Input::post('value');
                $this->Session->set('filter', $filter);
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
                    $arrResponse = array('success' => false, 'error' => $objWidget->getErrorsAsString(), 'preventRetry' => true);
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
        if (!isset($GLOBALS['TL_DCA'][$strTable]['fields']['type'])) {
            return;
        }

        $strScript = \Environment::get('script');
        $arrField = &$GLOBALS['TL_DCA'][$strTable]['fields']['type'];

        if (
            $strScript != 'contao/help.php' ||
            !$arrField ||
            !$arrField['eval']['helpwizard'] ||
            !is_array($arrField['options']) ||
            isset($GLOBALS['TL_LANG']['XPL']['type'])
        ) {
            return;
        }

        // try to load a type agent model help description
        $arrField['explanation'] = 'type';
        foreach (array_keys($arrField['options']) as $strKey) {
            $arrLabel = $GLOBALS['TL_LANG']['MODEL'][$strTable . '.' . $strKey];
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
