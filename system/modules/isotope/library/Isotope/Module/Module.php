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

namespace Isotope\Module;

use \Module as Contao_Module;
use Isotope\Isotope;


/**
 * Class ModuleIsotope
 *
 * Parent class for Isotope modules.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
abstract class Module extends Contao_Module
{

    /**
     * Disable caching of the frontend page if this module is in use.
     * Usefule to enable in a child classes.
     * @var bool
     */
    protected $blnDisableCache = false;

    /**
     * Cache category lookup
     * @var array
     */
    private $arrCategories;


    /**
     * Load libraries and scripts
     * @param object
     * @param string
     * @return void
     */
    public function __construct($objModule, $strColumn='main')
    {
        parent::__construct($objModule, $strColumn);

        Isotope::initialize();

        if (TL_MODE == 'FE')
        {
            // Load Isotope javascript and css
            $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/isotope/assets/isotope' . (ISO_DEBUG ? '' : '.min') . '.js';
            $GLOBALS['TL_CSS'][] = 'system/modules/isotope/assets/isotope' . (ISO_DEBUG ? '' : '.min') . '.css';

            // Disable caching for pages with certain modules (eg. Cart)
            if ($this->blnDisableCache)
            {
                try {
                    global $objPage;
                    $objPage->cache = 0;
                } catch (\Exception $e) {}
            }
        }
    }


    /**
     * Include messages if enabled
     * @return string
     */
    public function generate()
    {
        $strBuffer = parent::generate();

        // Prepend any messages to the module output
        if ($this->iso_includeMessages)
        {
            $strBuffer = \Isotope\Frontend::getIsotopeMessages() . $strBuffer;
        }

        return $strBuffer;
    }


    /**
     * Method that returns a closure to sort product collection items
     * @return  Closure
     */
    public function getProductCollectionItemsSortingCallable()
    {
        $arrSortingSettings = explode('_', $this->iso_orderCollectionBy, 2);
        $strSortingAttribute = $arrSortingSettings[1];

        if ($arrSortingSettings[0] == 'asc') {

            return function($arrItems) use ($strSortingAttribute) {
                uasort($arrItems, function($objItem1, $objItem2) use ($strSortingAttribute) {
                    if ($objItem1->$strSortingAttribute == $objItem2->$strSortingAttribute) {
                        return 0;
                    }

                    return $objItem1->$strSortingAttribute < $objItem2->$strSortingAttribute ? -1 : 1;
                });

                return $arrItems;
            };

        } elseif ($arrSortingSettings[0] == 'desc') {

            return function($arrItems) use ($strSortingAttribute) {
                uasort($arrItems, function($objItem1, $objItem2) use ($strSortingAttribute) {
                    if ($objItem1->$strSortingAttribute == $objItem2->$strSortingAttribute) {
                        return 0;
                    }

                    return $objItem1->$strSortingAttribute > $objItem2->$strSortingAttribute ? -1 : 1;
                });

                return $arrItems;
            };
        }

        return null;
    }


    /**
     * The ids of all pages we take care of. This is what should later be used eg. for filter data.
     * @return array
     */
    protected function findCategories()
    {
        if (null === $this->arrCategories) {

            if ($this->defineRoot && $this->rootPage > 0) {
                $objPage = $this->getPageDetails($this->rootPage);
            } else {
                global $objPage;
            }

            switch ($this->iso_category_scope) {

                case 'global':
                    $arrCategories = \Database::getInstance()->getChildRecords($objPage->rootId, 'tl_page');
                    $arrCategories[] = $objPage->rootId;
                    break;

                case 'current_and_first_child':
                    $arrCategories = \Database::getInstance()->execute("SELECT id FROM tl_page WHERE pid={$objPage->id}")->fetchEach('id');
                    $arrCategories[] = $objPage->id;
                    break;

                case 'current_and_all_children':
                    $arrCategories = \Database::getInstance()->getChildRecords($objPage->id, 'tl_page');
                    $arrCategories[] = $objPage->id;
                    break;

                case 'parent':
                    $arrCategories = array($objPage->pid);
                    break;

                case 'product':
                    $objProduct = \Isotope\Frontend::getProductByAlias(\Isotope\Frontend::getAutoItem('product'));

                    if ($objProduct !== null) {
                        $arrCategories = $objProduct->getCategories();
                    } else {
                        $arrCategories = array(0);
                    }
                    break;

                case 'article':
                    $arrCategories = array($GLOBALS['ISO_CONFIG']['current_article']['pid'] ?: $objPage->id);
                    break;

                case '':
                case 'current_category':
                    $arrCategories = array($objPage->id);
                    break;

                default:
                    // @todo change this to a hook to allow custom category scope
                    $arrCategories = array($objPage->id);
                    break;
            }

            $this->arrCategories = empty($arrCategories) ? array(0) : $arrCategories;
        }

        return $this->arrCategories;
    }


    /**
     * Find jumpTo page for current category scope
     * @param   Product
     * @return  PageModel
     */
    protected function findJumpToPage($objProduct)
    {
        global $objPage;
        global $objIsotopeListPage;

        $arrCategories = array();

        if ($this->iso_category_scope != 'current_category' && $this->iso_category_scope != '' && $objPage->alias != 'index') {
            $arrCategories = array_intersect($objProduct->getCategories(), $this->findCategories());
        }

        // If our current category scope does not match with any product category, use the first product category in the current root page
        if (empty($arrCategories)) {
            $arrCategories = array_intersect($objProduct->getCategories(), \Database::getInstance()->getChildRecords($objPage->rootId, $objPage->getTable()));
        }

        foreach ($arrCategories as $intCategory) {
            $objCategory = \PageModel::findByPk($intCategory);

            if ($objCategory->alias == 'index' && count($arrCategories) > 1) {
                continue;
            }

            return $objCategory;
        }

        return $objIsotopeListPage ?: $objPage;
    }


    /**
     * Generate the URL from existing $_GET parameters.
     * Use \Input::setGet('var', null) to remove a parameter from the final URL.
     * @return string
     */
    protected function generateRequestUrl()
    {
        if (\Environment::get('request') == '')
        {
            return '';
        }

        $strRequest = preg_replace('/\?.*$/i', '', \Environment::get('request'));
        $strRequest = preg_replace('/' . preg_quote($GLOBALS['TL_CONFIG']['urlSuffix'], '/') . '$/i', '', $strRequest);
        $arrFragments = explode('/', $strRequest);

        // Skip index.php
        if (strtolower($arrFragments[0]) == 'index.php')
        {
            array_shift($arrFragments);
        }

        // HOOK: add custom logic
        if (isset($GLOBALS['TL_HOOKS']['getPageIdFromUrl']) && is_array($GLOBALS['TL_HOOKS']['getPageIdFromUrl']))
        {
            foreach ($GLOBALS['TL_HOOKS']['getPageIdFromUrl'] as $callback)
            {
                $objCallback = \System::importStatic($callback[0]);
                $arrFragments = $objCallback->$callback[1]($arrFragments);
            }
        }

        $strParams = '';
        $arrGet = array();

        // Add fragments to URL params
        for ($i=1, $count=count($arrFragments); $i<$count; $i+=2)
        {
            if (isset($_GET[$arrFragments[$i]]))
            {
                $key = urldecode($arrFragments[$i]);
                \Input::setGet($key, null);
                $strParams .= '/' . $key . '/' . urldecode($arrFragments[$i+1]);
            }
        }

        // Add get parameters to URL
        if (is_array($_GET) && !empty($_GET))
        {
            foreach ($_GET as $key => $value)
            {
                // Ignore the language parameter
                if ($key == 'language' && $GLOBALS['TL_CONFIG']['addLanguageToUrl'])
                {
                    continue;
                }

                $arrGet[] = $key . '=' . $value;
            }
        }

        global $objPage;

        return \Controller::generateFrontendUrl($objPage->row(), $strParams) . (!empty($arrGet) ? ('?'.implode('&', $arrGet)) : '');
    }
}
