<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope;

use Contao\Backend as ContaoBackend;
use Contao\BackendUser;
use Contao\Controller;
use Contao\CoreBundle\Exception\NoContentResponseException;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\Database;
use Contao\DataContainer;
use Contao\Input;
use Contao\Model;
use Contao\Session;
use Contao\System;
use Contao\Widget;
use Isotope\Backend\Product\Permission;
use Isotope\Model\Config;
use Isotope\Model\Group;
use Isotope\Model\OrderStatus;
use Isotope\Model\ProductCache;
use Isotope\Model\RequestCache;
use Isotope\Model\TypeAgent;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provide methods to handle Isotope back end components.
 */
class Backend extends ContaoBackend
{

    /**
     * Truncate the product cache table if a product is changed
     * Second parameter and return value is to use the method as save_callback
     *
     * @param mixed $varValue
     *
     * @return mixed
     */
    public static function truncateProductCache($varValue = null)
    {
        ProductCache::purge();

        return $varValue;
    }

    /**
     * Truncate the request cache table
     */
    public static function truncateRequestCache()
    {
        RequestCache::purge();
    }

    /**
     * Get array of subdivisions, delay loading of file if not necessary
     *
     * @return array
     */
    public static function getSubdivisions()
    {
        static $arrSubdivisions = null;

        if (null === $arrSubdivisions) {

            System::loadLanguageFile('subdivisions');

            foreach ($GLOBALS['TL_LANG']['DIV'] as $strCountry => $arrSubdivision) {
                foreach ($arrSubdivision as $strCode => $varValue) {
                    if (\is_array($varValue)) {
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
     * Returns the label for a subdivision of a country.
     *
     * @param string $country
     * @param string $subdivision
     *
     * @return string
     */
    public static function getLabelForSubdivision($country, $subdivision)
    {
        $country = strtolower($country);
        $arrSubdivisions = Backend::getSubdivisions();

        if (isset($arrSubdivisions[$country][$subdivision])) {
            return $arrSubdivisions[$country][$subdivision];
        }

        if (\is_array($arrSubdivisions[$country])) {
            foreach ($arrSubdivisions[$country] as $groupCode => $regionGroup) {
                if (\is_array($regionGroup)) {
                    foreach ($regionGroup as $groupLabel => $regions) {
                        if (isset($regions[$subdivision])) {
                            return $regions[$subdivision];
                        }
                    }
                }
            }
        }

        return '';
    }

    /**
     * DCA for setup module tables is "closed" to hide the "new" button. Re-enable it when clicking on a button
     *
     * @param DataContainer $dc
     */
    public function initializeSetupModule($dc)
    {
        if (Input::get('act') != '') {
            $GLOBALS['TL_DCA'][$dc->table]['config']['closed'] = false;
        }
    }

    /**
     * Return all Isotope modules
     *
     * @return array
     */
    public function getIsotopeModules()
    {
        $arrModules = array();

        foreach ($GLOBALS['ISO_MOD'] as $k => $v) {
            $arrModules[str_replace(':hide', '', $k)] = array_keys($v);
        }

        return $arrModules;
    }

    /**
     * List template from all themes, show theme name
     *
     * @param string $strPrefix
     *
     * @return array
     */
    public static function getTemplates($strPrefix)
    {
        $arrTemplates = Controller::getTemplateGroup($strPrefix);

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

                    if (\is_array($arrConfigTemplates)) {
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
     *
     * @return array
     */
    public static function getOrderStatus()
    {
        $arrStatus = array();

        if (($objStatus = OrderStatus::findAll(array('order' => 'sorting'))) !== null) {

            /** @var OrderStatus $status */
            foreach ($objStatus as $status) {
                $arrStatus[$status->id] = $status->getName();
            }
        }

        return $arrStatus;
    }

    /**
     * Show messages for new order status
     *
     * @return string
     */
    public function getOrderMessages()
    {
        $objUser = BackendUser::getInstance();

        if (!Database::getInstance()->tableExists(OrderStatus::getTable())
            || !$objUser->hasAccess('iso_orders', 'modules')
        ) {
            return '';
        }

        // Can't see any orders if user does not have access to any shop config
        $strConfig = '';
        if (!BackendUser::getInstance()->isAdmin) {
            $arrConfigs = BackendUser::getInstance()->iso_configs;

            if (empty($arrConfigs) || !\is_array($arrConfigs)) {
                return '';
            }

            $strConfig = "AND c.config_id IN (" . implode(',', $arrConfigs) . ")";
        }

        $arrMessages = array();
        $objOrders   = Database::getInstance()->query("
            SELECT COUNT(*) AS total, s.name
            FROM tl_iso_product_collection c
            LEFT JOIN tl_iso_orderstatus s ON c.order_status=s.id
            WHERE c.type='order' AND s.welcomescreen='1' $strConfig
            GROUP BY s.id"
        );

        while ($objOrders->next()) {
            $arrMessages[] = '<p class="tl_new">' . sprintf($GLOBALS['TL_LANG']['MSC']['newOrders'], $objOrders->total, $objOrders->name) . '</p>';
        }

        return implode("\n", $arrMessages);
    }

    /**
     * Returns an array of all allowed product IDs and variant IDs for the current backend user
     *
     * @return array|bool
     *
     * @deprecated will be removed in Isotope 3.0
     */
    public static function getAllowedProductIds()
    {
        return Permission::getAllowedIds();
    }

    /**
     * Check the Ajax pre actions
     *
     * @param string $action
     */
    public function executePreActions($action)
    {
        switch ($action) {
            // Move the product
            case 'moveProduct':
                Session::getInstance()->set('iso_products_gid', (int) Input::post('value'));
                throw new NoContentResponseException();

            // Move multiple products
            case 'moveProducts':
                Session::getInstance()->set('iso_products_gid', (int) Input::post('value'));
                throw new NoContentResponseException();

            // Filter the groups
            case 'filterGroups':
                Session::getInstance()->set('iso_products_gid', (int) Input::post('value'));
                Controller::reload();
                break;

            // Filter the pages
            case 'filterPages':
                $filter = Session::getInstance()->get('filter');
                $filter['tl_iso_product']['iso_page'] = (int) Input::post('value');
                Session::getInstance()->set('filter', $filter);
                Controller::reload();
                break;

            // Filter product collection by product
            case 'filterProducts':
                $filter = Session::getInstance()->get('filter');
                $filter['tl_iso_product_collection']['iso_product'] = (int) Input::post('value');
                Session::getInstance()->set('filter', $filter);
                Controller::reload();
                break;
        }
    }


    /**
     * Check the Ajax post actions
     *
     * @param string         $action
     * @param DataContainer $dc
     */
    public function executePostActions($action, $dc)
    {
        switch ($action) {
            case 'uploadMediaManager':
                $arrData = array(
                    'strTable' => $dc->table,
                    'id'       => $this->strAjaxName ?: $dc->id,
                    'name'     => Input::post('name'),
                );

                /** @var \Isotope\Widget\MediaManager $objWidget */
                $objWidget = new $GLOBALS['BE_FFL']['mediaManager']($arrData, $dc);
                $objWidget->ajaxUpload();
                break; // $objWidget->ajaxUpload() will throw a ResponseException

            case 'reloadMediaManager':
                $intId    = Input::get('id');
                $strField = $dc->field = Input::post('name');
                $this->import('Database');

                // Handle the keys in "edit multiple" mode
                if ('editAll' === Input::get('act')) {
                    $intId    = preg_replace('/.*_([0-9a-zA-Z]+)$/', '$1', $strField);
                    $strField = preg_replace('/(.*)_[0-9a-zA-Z]+$/', '$1', $strField);
                }

                // The field does not exist
                if (!isset($GLOBALS['TL_DCA'][$dc->table]['fields'][$strField])) {
                    System::log('Field "' . $strField . '" does not exist in DCA "' . $dc->table . '"', __METHOD__, TL_ERROR);
                    header('HTTP/1.1 400 Bad Request');
                    die('Bad Request');
                }

                // Load the value
                if ($intId > 0 && $this->Database->tableExists($dc->table)) {
                    $objRow = $this->Database->prepare("SELECT * FROM {$dc->table} WHERE id=?")
                                             ->execute($intId);

                    // The record does not exist
                    if ($objRow->numRows < 1) {
                        System::log('A record with the ID "' . $intId . '" does not exist in table "' . $dc->table . '"', __METHOD__, TL_ERROR);
                        header('HTTP/1.1 400 Bad Request');
                        die('Bad Request');
                    }
                }

                $varValue = Input::post('value', true);

                // Include the uploaded files in the value
                if (Input::post('files', true)) {
                    $varValue['files'] = Input::post('files', true);
                }

                // Build the attributes based on the "eval" array
                $arrAttribs = $GLOBALS['TL_DCA'][$dc->table]['fields'][$strField]['eval'];

                $arrAttribs['id']           = $dc->field;
                $arrAttribs['name']         = $dc->field;
                $arrAttribs['value']        = $varValue;
                $arrAttribs['strTable']     = $dc->table;
                $arrAttribs['strField']     = $strField;
                $arrAttribs['activeRecord'] = $dc->activeRecord;

                /** @var \Isotope\Widget\MediaManager $objWidget */
                $objWidget = new $GLOBALS['BE_FFL']['mediaManager']($arrAttribs);
                throw new ResponseException(new Response($objWidget->generate()));
        }
    }

    /**
     * Load type agent model help
     *
     * @param string $strTable
     */
    public function loadTypeAgentHelp($strTable)
    {
        $strKey = Input::get('table');
        $strField = Input::get('field');

        if ($strKey !== $strTable) {
            return;
        }

        if ('tl_iso_producttype' === $strKey) {
            $strKey = 'tl_iso_product';
        }

        if (
            TL_SCRIPT !== 'contao/help.php' ||
            !isset($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]) ||
            !is_subclass_of(Model::getClassFromTable($strKey), TypeAgent::class)
        ) {
            return;
        }

        $arrField = &$GLOBALS['TL_DCA'][$strTable]['fields'][$strField];

        // Get the field type
        /** @var Widget $strClass */
        $strClass = $GLOBALS['BE_FFL'][$arrField['inputType']];

        // Abort if the class is not defined
        if (!class_exists($strClass)) {
            return;
        }

        $arrFieldComplete = $strClass::getAttributesFromDca($arrField, $strField);

        if (
            !$arrFieldComplete ||
            !$arrFieldComplete['helpwizard'] ||
            !\is_array($arrFieldComplete['options']) ||
            $arrField['explanation'] != '' ||
            isset($GLOBALS['TL_LANG']['XPL']['type'])
        ) {

            return;
        }

        // try to load a type agent model help description
        $arrField['explanation'] = 'type';
        foreach ($arrFieldComplete['options'] as $arrOption) {
            $arrLabel = $GLOBALS['TL_LANG']['MODEL'][$strKey][$arrOption['value']];
            if ($arrLabel) {
                $GLOBALS['TL_LANG']['XPL']['type'][] = $arrLabel;
            }
        }
    }


    /**
     * Adjust the product groups manager view
     *
     * @param Template|\stdClass $objTemplate
     */
    public function adjustGroupsManager($objTemplate)
    {
        if (Input::get('popup')
            && 'iso_products' === Input::get('do')
            && Group::getTable() === Input::get('table')
            && 'be_main' === $objTemplate->getName()
        ) {
            $objTemplate->managerHref = ampersand($this->Session->get('groupPickerRef'));
            $objTemplate->manager     = $GLOBALS['TL_LANG']['MSC']['groupPickerHome'];
        }
    }
}
