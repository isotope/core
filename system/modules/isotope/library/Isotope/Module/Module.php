<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Module;

use Contao\Controller;
use Contao\Database;
use Contao\Date;
use Contao\Environment;
use Contao\FrontendUser;
use Contao\System;
use Haste\Frontend\AbstractFrontendModule;
use Haste\Input\Input;
use Haste\Util\Debug;
use Isotope\CompatibilityHelper;
use Isotope\Frontend;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Isotope;
use Isotope\Message;
use Isotope\Model\Product;
use Isotope\Model\Product\AbstractProduct;
use Isotope\RequestCache\CategoryFilter;
use Contao\PageModel;


/**
 * Module implements a parent class for Isotope modules
 *
 * @property string $iso_category_scope
 * @property string $iso_list_where
 * @property string $iso_includeMessages
 * @property bool   $iso_hide_list
 * @property bool   $iso_disable_options
 * @property bool   $iso_emptyMessage
 * @property string $iso_noProducts
 * @property bool   $iso_emptyFilter
 * @property string $iso_newFilter
 * @property string $iso_noFilter
 * @property array  $iso_buttons
 * @property bool   $iso_link_primary
 */
abstract class Module extends AbstractFrontendModule
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
     *
     * @param \Contao\ModuleModel $objModule
     * @param string $strColumn
     */
    public function __construct($objModule, $strColumn = 'main')
    {
        parent::__construct($objModule, $strColumn);

        if ($this->iso_list_where != '') {
            $this->iso_list_where = Controller::replaceInsertTags($this->iso_list_where);
        }

        Isotope::initialize();

        // Load Isotope JavaScript and style sheet
        if (CompatibilityHelper::isFrontend()) {
            $GLOBALS['TL_JAVASCRIPT'][] = Debug::uncompressedFile(
                'system/modules/isotope/assets/js/isotope.min.js|static'
            );

            $GLOBALS['TL_CSS'][] = Debug::uncompressedFile(
                'system/modules/isotope/assets/css/isotope.min.css|screen|static'
            );

            // Disable caching for pages with certain modules (eg. Cart)
            if ($this->blnDisableCache) {
                global $objPage;
                $objPage->cache = 0;
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function getSerializedProperties()
    {
        return ['iso_buttons'];
    }

    /**
     * Include messages if enabled
     *
     * @return string
     */
    public function generate()
    {
        $strBuffer = parent::generate();

        // Prepend any messages to the module output
        if (!CompatibilityHelper::isBackend() && $this->iso_includeMessages) {
            $strBuffer = Message::generate() . $strBuffer;
        }

        return $strBuffer;
    }

    /**
     * The ids of all pages we take care of. This is what should later be used eg. for filter data.
     *
     * @return array|null
     */
    protected function findCategories(array &$arrFilters = null)
    {
        if (null !== $arrFilters) {
            $arrCategories = null;

            foreach ($arrFilters as $k => $filter) {
                if ($filter instanceof CategoryFilter) {
                    unset($arrFilters[$k]);

                    if (!\is_array($arrCategories)) {
                        $arrCategories = $filter['value'];
                    } else {
                        $arrCategories = array_intersect($arrCategories, $filter['value']);
                    }
                }
            }

            if (\is_array($arrCategories)) {
                return empty($arrCategories) ? array(0) : array_map('intval', $arrCategories);
            }
        }

        if (null !== $this->arrCategories) {
            return $this->arrCategories;
        }

        if ($this->defineRoot && $this->rootPage > 0) {
            $objPage = PageModel::findWithDetails($this->rootPage);
        } else {
            global $objPage;
        }

        $t = PageModel::getTable();
        $arrCategories = null;
        $strWhere = "$t.type!='error_403' AND $t.type!='error_404'";

        if (!\Contao\System::getContainer()->get('contao.security.token_checker')->isPreviewMode()) {
            $time = Date::floorToMinute();
            $strWhere .= " AND ($t.start='' OR $t.start<'$time') AND ($t.stop='' OR $t.stop>'" . ($time + 60) . "') AND $t.published='1'";
        }

        switch ($this->iso_category_scope) {
            case 'global':
                $arrCategories = [$objPage->rootId];
                $arrCategories = Database::getInstance()->getChildRecords($objPage->rootId, 'tl_page', false, $arrCategories, $strWhere);
                break;

            case 'current_and_first_child':
                $arrCategories   = Database::getInstance()->execute("SELECT id FROM tl_page WHERE pid={$objPage->id} AND $strWhere")->fetchEach('id');
                $arrCategories[] = $objPage->id;
                break;

            case 'current_and_all_children':
                $arrCategories = [$objPage->id];
                $arrCategories = Database::getInstance()->getChildRecords($objPage->id, 'tl_page', false, $arrCategories, $strWhere);
                break;

            case 'parent':
                $arrCategories = [$objPage->pid];
                break;

            case 'product':
                /** @var \Isotope\Model\Product\Standard $objProduct */
                $objProduct = Product::findAvailableByIdOrAlias(Input::getAutoItem('product'));
                $arrCategories = [0];

                if ($objProduct !== null) {
                    $arrCategories = $objProduct->getCategories(true);
                }
                break;

            case 'article':
                $arrCategories = array($GLOBALS['ISO_CONFIG']['current_article']['pid'] ? : $objPage->id);
                break;

            case '':
            case 'current_category':
                $arrCategories = [$objPage->id];
                break;

            default:
                if (isset($GLOBALS['ISO_HOOKS']['findCategories']) && \is_array($GLOBALS['ISO_HOOKS']['findCategories'])) {
                    foreach ($GLOBALS['ISO_HOOKS']['findCategories'] as $callback) {
                        $arrCategories = System::importStatic($callback[0])->{$callback[1]}($this);

                        if ($arrCategories !== false) {
                            break;
                        }
                    }
                }
                break;
        }

        $this->arrCategories = empty($arrCategories) ? array(0) : array_map('intval', $arrCategories);

        return $this->arrCategories;
    }

    /**
     * Find jumpTo page for current category scope
     *
     *
     * @return PageModel
     */
    protected function findJumpToPage(IsotopeProduct $objProduct)
    {
        global $objPage;
        global $objIsotopeListPage;

        $productCategories = $objProduct instanceof AbstractProduct ? $objProduct->getCategories(true) : [];
        $arrCategories = array();

        if (!$this->iso_link_primary) {
            $arrCategories = array_intersect(
                $productCategories,
                $this->findCategories()
            );
        }

        // If our current category scope does not match with any product category,
        // use the first allowed product category in the current root page
        if (empty($arrCategories)) {
            $arrCategories = $productCategories;
        }

        $arrCategories = Frontend::getPagesInCurrentRoot(
            $arrCategories,
            FrontendUser::getInstance()
        );

        if (!empty($arrCategories)
         && ($objCategories = PageModel::findMultipleByIds($arrCategories)) !== null
        ) {
            $blnMoreThanOne = $objCategories->count() > 1;
            foreach ($objCategories as $objCategory) {

                if ('index' === $objCategory->alias && $blnMoreThanOne) {
                    continue;
                }

                return $objCategory;
            }
        }

        return $objIsotopeListPage ? : $objPage;
    }

    /**
     * Generate the URL from existing $_GET parameters.
     * Use Input::setGet('var', null) to remove a parameter from the final URL.
     *
     * @return string
     * @deprecated use \Haste\Util\Url::addQueryString instead
     */
    protected function generateRequestUrl()
    {
        if (Environment::get('request') == '') {
            return '';
        }

        $strRequest   = preg_replace('/\?.*$/i', '', Environment::get('request'));
        $strRequest   = preg_replace('/' . preg_quote($GLOBALS['TL_CONFIG']['urlSuffix'], '/') . '$/i', '', $strRequest);
        $arrFragments = explode('/', $strRequest);

        // Skip index.php
        if ('index.php' === strtolower($arrFragments[0])) {
            array_shift($arrFragments);
        }

        // HOOK: add custom logic
        if (isset($GLOBALS['TL_HOOKS']['getPageIdFromUrl']) && \is_array($GLOBALS['TL_HOOKS']['getPageIdFromUrl'])) {
            foreach ($GLOBALS['TL_HOOKS']['getPageIdFromUrl'] as $callback) {
                $arrFragments = System::importStatic($callback[0])->{$callback[1]}($arrFragments);
            }
        }

        $strParams = '';
        $arrGet    = array();

        // Add fragments to URL params
        for ($i = 1, $count = \count($arrFragments); $i < $count; $i += 2) {
            if (isset($_GET[$arrFragments[$i]])) {
                $key = urldecode($arrFragments[$i]);
                Input::setGet($key, null);
                $strParams .= '/' . $key . '/' . urldecode($arrFragments[$i + 1]);
            }
        }

        // Add get parameters to URL
        if (\is_array($_GET) && !empty($_GET)) {
            foreach ($_GET as $key => $value) {
                // Ignore the language parameter
                if ('language' === $key && $GLOBALS['TL_CONFIG']['addLanguageToUrl']) {
                    continue;
                }

                $arrGet[] = $key . '=' . $value;
            }
        }

        /** @var PageModel $objPage */
        global $objPage;

        return $objPage->getFrontendUrl($strParams) . (!empty($arrGet) ? ('?' . implode('&', $arrGet)) : '');
    }
}
