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

use Isotope\Isotope;
use Isotope\Message;
use Isotope\Model\Product;
use Module as Contao_Module;
use PageModel;


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
    public function __construct($objModule, $strColumn = 'main')
    {
        parent::__construct($objModule, $strColumn);

        Isotope::initialize();

        if (TL_MODE == 'FE') {
            // Load Isotope javascript and css
            $GLOBALS['TL_JAVASCRIPT'][] = \Haste\Util\Debug::uncompressedFile('system/modules/isotope/assets/js/isotope.min.js');
            $GLOBALS['TL_CSS'][]        = \Haste\Util\Debug::uncompressedFile('system/modules/isotope/assets/css/isotope.min.css');

            // Disable caching for pages with certain modules (eg. Cart)
            if ($this->blnDisableCache) {
                try {
                    global $objPage;
                    $objPage->cache = 0;
                } catch (\Exception $e) {
                }
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
        if ($this->iso_includeMessages) {
            $strBuffer = Message::generate() . $strBuffer;
        }

        return $strBuffer;
    }


    /**
     * The ids of all pages we take care of. This is what should later be used eg. for filter data.
     * @return array
     */
    protected function findCategories()
    {
        if (null === $this->arrCategories) {

            if ($this->defineRoot && $this->rootPage > 0) {
                $objPage = PageModel::findWithDetails($this->rootPage);
            } else {
                global $objPage;
            }

            $t = PageModel::getTable();
            $arrCategories = null;
            $strWhere = "$t.type!='error_403' AND $t.type!='error_404'";

            if (!BE_USER_LOGGED_IN) {
                $time = time();
                $strWhere .= " AND ($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time) AND $t.published='1'";
            }

            switch ($this->iso_category_scope) {

                case 'global':
                    $arrCategories = array($objPage->rootId);
                    $arrCategories = \Database::getInstance()->getChildRecords($objPage->rootId, 'tl_page', false, $arrCategories, $strWhere);
                    break;

                case 'current_and_first_child':
                    $arrCategories   = \Database::getInstance()->execute("SELECT id FROM tl_page WHERE pid={$objPage->id} AND $strWhere")->fetchEach('id');
                    $arrCategories[] = $objPage->id;
                    break;

                case 'current_and_all_children':
                    $arrCategories = array($objPage->id);
                    $arrCategories = \Database::getInstance()->getChildRecords($objPage->id, 'tl_page', false, $arrCategories, $strWhere);
                    break;

                case 'parent':
                    $arrCategories = array($objPage->pid);
                    break;

                case 'product':
                    $objProduct = Product::findAvailableByIdOrAlias(\Haste\Input\Input::getAutoItem('product'));

                    if ($objProduct !== null) {
                        $arrCategories = $objProduct->getCategories(true);
                    } else {
                        $arrCategories = array(0);
                    }
                    break;

                case 'article':
                    $arrCategories = array($GLOBALS['ISO_CONFIG']['current_article']['pid'] ? : $objPage->id);
                    break;

                case '':
                case 'current_category':
                    $arrCategories = array($objPage->id);
                    break;

                default:
                    if (isset($GLOBALS['ISO_HOOKS']['findCategories']) && is_array($GLOBALS['ISO_HOOKS']['findCategories'])) {
                        foreach ($GLOBALS['ISO_HOOKS']['findCategories'] as $callback) {
                            $objCallback   = \System::importStatic($callback[0]);
                            $arrCategories = $objCallback->$callback[1]($this);

                            if ($arrCategories !== false) {
                                break;
                            }
                        }
                    }
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

        return $objIsotopeListPage ? : $objPage;
    }


    /**
     * Generate the URL from existing $_GET parameters.
     * Use \Input::setGet('var', null) to remove a parameter from the final URL.
     * @return      string
     * @deprecated  use \Haste\Util\Url::addQueryString instead
     */
    protected function generateRequestUrl()
    {
        if (\Environment::get('request') == '') {
            return '';
        }

        $strRequest   = preg_replace('/\?.*$/i', '', \Environment::get('request'));
        $strRequest   = preg_replace('/' . preg_quote($GLOBALS['TL_CONFIG']['urlSuffix'], '/') . '$/i', '', $strRequest);
        $arrFragments = explode('/', $strRequest);

        // Skip index.php
        if (strtolower($arrFragments[0]) == 'index.php') {
            array_shift($arrFragments);
        }

        // HOOK: add custom logic
        if (isset($GLOBALS['TL_HOOKS']['getPageIdFromUrl']) && is_array($GLOBALS['TL_HOOKS']['getPageIdFromUrl'])) {
            foreach ($GLOBALS['TL_HOOKS']['getPageIdFromUrl'] as $callback) {
                $objCallback  = \System::importStatic($callback[0]);
                $arrFragments = $objCallback->$callback[1]($arrFragments);
            }
        }

        $strParams = '';
        $arrGet    = array();

        // Add fragments to URL params
        for ($i = 1, $count = count($arrFragments); $i < $count; $i += 2) {
            if (isset($_GET[$arrFragments[$i]])) {
                $key = urldecode($arrFragments[$i]);
                \Input::setGet($key, null);
                $strParams .= '/' . $key . '/' . urldecode($arrFragments[$i + 1]);
            }
        }

        // Add get parameters to URL
        if (is_array($_GET) && !empty($_GET)) {
            foreach ($_GET as $key => $value) {
                // Ignore the language parameter
                if ($key == 'language' && $GLOBALS['TL_CONFIG']['addLanguageToUrl']) {
                    continue;
                }

                $arrGet[] = $key . '=' . $value;
            }
        }

        global $objPage;

        return \Controller::generateFrontendUrl($objPage->row(), $strParams) . (!empty($arrGet) ? ('?' . implode('&', $arrGet)) : '');
    }
}
