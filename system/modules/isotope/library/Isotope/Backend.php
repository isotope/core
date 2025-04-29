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
use Contao\System;
use Contao\Widget;
use Isotope\Backend\Product\Permission;
use Isotope\Model\Config;
use Isotope\Model\Group;
use Isotope\Model\OrderStatus;
use Isotope\Model\ProductCache;
use Isotope\Model\RequestCache;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;

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
            foreach ($arrSubdivisions[$country] as $regionGroup) {
                if (\is_array($regionGroup)) {
                    foreach ($regionGroup as $regions) {
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
                    $arrConfigTemplates = glob(\Contao\System::getContainer()->getParameter('kernel.project_dir') . '/' . $objConfig->templateGroup . '/' . $strPrefix . '*');

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
        /** @var AttributeBagInterface $sessionBag */
        $sessionBag = System::getContainer()->get('request_stack')->getSession()->getBag('contao_backend');

        switch ($action) {
            // Move the product
            case 'moveProduct':
                $sessionBag->set('iso_products_gid', (int) Input::post('value'));
                throw new NoContentResponseException();

            // Move multiple products
            case 'moveProducts':
                $sessionBag->set('iso_products_gid', (int) Input::post('value'));
                throw new NoContentResponseException();

            // Filter the groups
            case 'filterGroups':
                $sessionBag->set('iso_products_gid', (int) Input::post('value'));
                Controller::reload();
                break;

            // Filter the pages
            case 'filterPages':
                $filter = $sessionBag->get('filter');
                $filter['tl_iso_product']['iso_page'] = (int) Input::post('value');
                $sessionBag->set('filter', $filter);
                Controller::reload();
                break;

            // Filter product collection by product
            case 'filterProducts':
                $filter = $sessionBag->get('filter');
                $filter['tl_iso_product_collection']['iso_product'] = (int) Input::post('value');
                $sessionBag->set('filter', $filter);
                Controller::reload();
                break;
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

        //if (
        //    TL_SCRIPT !== 'contao/help.php' ||
        //    !isset($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]) ||
        //    !is_subclass_of(Model::getClassFromTable($strKey), TypeAgent::class)
        //) {
            return;
        //}

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
            /** @var AttributeBagInterface $sessionBag */
            $sessionBag = System::getContainer()->get('request_stack')->getSession()->getBag('contao_backend');

            $objTemplate->managerHref = \Contao\StringUtil::ampersand($sessionBag->get('groupPickerRef'));
            $objTemplate->manager     = $GLOBALS['TL_LANG']['MSC']['groupPickerHome'];
        }
    }
}
