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

use Haste\Haste;
use Haste\Generator\RowClass;
use Haste\Http\Response\HtmlResponse;
use Isotope\Isotope;
use Isotope\Model\Attribute;
use Isotope\Model\Product;
use Isotope\Model\ProductCache;
use Isotope\Model\RequestCache;
use Isotope\RequestCache\Sort;


/**
 * Class ProductList
 *
 * The mother of all product lists.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class ProductList extends Module
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_iso_productlist';

    /**
     * Cache products. Can be disable in a child class, e.g. a "random products list"
     * @var boolean
     */
    protected $blnCacheProducts = true;


    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ISOTOPE ECOMMERCE: PRODUCT LIST ###';

            $objTemplate->title = $this->headline;
            $objTemplate->id    = $this->id;
            $objTemplate->link  = $this->name;
            $objTemplate->href  = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        // Hide product list in reader mode if the respective setting is enabled
        if ($this->iso_hide_list && \Haste\Input\Input::getAutoItem('product', false, true) != '') {
            return '';
        }

        $this->iso_filterModules = deserialize($this->iso_filterModules, true);
        $this->iso_productcache  = deserialize($this->iso_productcache, true);

        // Disable the cache in frontend preview or debug mode
        if (BE_USER_LOGGED_IN === true || $GLOBALS['TL_CONFIG']['debugMode']) {
            $this->blnCacheProducts = false;
        }

        // Apply limit from filter module
        $this->perPage = Isotope::getRequestCache()->getFirstLimitForModules($this->iso_filterModules, $this->perPage)->asInt();

        return parent::generate();
    }


    /**
     * Compile product list.
     *
     * This function is specially designed so you can keep it in your child classes and only override findProducts().
     * You will automatically gain product caching (see class property), grid classes, pagination and more.
     */
    protected function compile()
    {
        // return message if no filter is set
        if ($this->iso_emptyFilter && !\Input::get('isorc') && !\Input::get('keywords')) {
            $this->Template->message  = $this->replaceInsertTags($this->iso_noFilter);
            $this->Template->type     = 'noFilter';
            $this->Template->products = array();

            return;
        }

        global $objPage;
        $intPage     = ($this->iso_category_scope == 'article' ? $GLOBALS['ISO_CONFIG']['current_article']['pid'] : $objPage->id);
        $arrProducts = null;
        $arrCacheIds = null;

        // Try to load the products from cache
        if ($this->blnCacheProducts && ($objCache = ProductCache::findForPageAndModule($intPage, $this->id)) !== null) {
            $arrCacheIds = $objCache->getProductIds();

            // Use the cache if keywords match. Otherwise we will use the product IDs as a "limit" for findProducts()
            if ($objCache->keywords == \Input::get('keywords')) {
                $arrCacheIds = $this->generatePagination($arrCacheIds);

                $objProducts = Product::findAvailableByIds($arrCacheIds, array(
                    'order' => \Database::getInstance()->findInSet(Product::getTable().'.id', $arrCacheIds)
                ));

                $arrProducts = (null === $objProducts) ? array() : $objProducts->getModels();

                // Cache is wrong, drop everything and run findProducts()
                if (count($arrProducts) != count($arrCacheIds)) {
                    $arrCacheIds = null;
                    $arrProducts = null;
                }
            }
        }

        if (!is_array($arrProducts)) {

            // Display "loading products" message and add cache flag
            if ($this->blnCacheProducts) {
                $blnCacheMessage = (bool) $this->iso_productcache[$intPage][(int) \Input::get('isorc')];

                if ($blnCacheMessage && !\Input::get('buildCache')) {

                    // Do not index or cache the page
                    $objPage->noSearch = 1;
                    $objPage->cache    = 0;

                    $this->Template          = new \Isotope\Template('mod_iso_productlist_caching');
                    $this->Template->message = $GLOBALS['TL_LANG']['MSC']['productcacheLoading'];

                    return;
                }

                // Start measuring how long it takes to load the products
                $start = microtime(true);

                // Load products
                $arrProducts = $this->findProducts($arrCacheIds);

                // Decide if we should show the "caching products" message the next time
                $end = microtime(true) - $start;
                $this->blnCacheProducts = $end > 1 ? true : false;

                $arrCacheMessage = $this->iso_productcache;
                if ($blnCacheMessage != $this->blnCacheProducts) {
                    $arrCacheMessage[$intPage][(int) \Input::get('isorc')] = $this->blnCacheProducts;
                    \Database::getInstance()->prepare("UPDATE tl_module SET iso_productcache=? WHERE id=?")->execute(serialize($arrCacheMessage), $this->id);
                }

                // Do not write cache if table is locked. That's the case if another process is already writing cache
                if (ProductCache::isWritable()) {

                    \Database::getInstance()->lockTables(array(ProductCache::getTable() => 'WRITE', 'tl_iso_product' => 'READ'));

                    $arrIds = array();
                    foreach ($arrProducts as $objProduct) {
                        $arrIds[] = $objProduct->id;
                    }

                    // Delete existing cache if necessary
                    ProductCache::deleteForPageAndModuleOrExpired($intPage, $this->id);

                    $objCache          = ProductCache::createForPageAndModule($intPage, $this->id);
                    $objCache->expires = $this->getProductCacheExpiration();
                    $objCache->setProductIds($arrIds);
                    $objCache->save();

                    \Database::getInstance()->unlockTables();
                }
            } else {
                $arrProducts = $this->findProducts();
            }

            if (!empty($arrProducts)) {
                $arrProducts = $this->generatePagination($arrProducts);
            }
        }

        // No products found
        if (!is_array($arrProducts) || empty($arrProducts)) {
            $this->compileEmptyMessage();

            return;
        }

        $arrBuffer         = array();
        $arrDefaultOptions = $this->getDefaultProductOptions();

        foreach ($arrProducts as $objProduct) {
            $arrConfig = array(
                'module'        => $this,
                'template'      => ($this->iso_list_layout ?: $objProduct->getRelated('type')->list_template),
                'gallery'       => ($this->iso_gallery ?: $objProduct->getRelated('type')->list_gallery),
                'buttons'       => deserialize($this->iso_buttons, true),
                'useQuantity'   => $this->iso_use_quantity,
                'jumpTo'        => $this->findJumpToPage($objProduct),
            );

            if (\Environment::get('isAjaxRequest') && \Input::post('AJAX_MODULE') == $this->id && \Input::post('AJAX_PRODUCT') == $objProduct->getProductId()) {
                $objResponse = new HtmlResponse($objProduct->generate($arrConfig));
                $objResponse->send();
            }

            $objProduct->mergeRow($arrDefaultOptions);

            // Must be done after setting options to generate the variant config into the URL
            if ($this->iso_jump_first && \Haste\Input\Input::getAutoItem('product', false, true) == '') {
                \Controller::redirect($objProduct->generateUrl($arrConfig['jumpTo']));
            }

            $arrCSS = deserialize($objProduct->cssID, true);

            $arrBuffer[] = array(
                'cssID'     => ($arrCSS[0] != '') ? ' id="' . $arrCSS[0] . '"' : '',
                'class'     => trim('product ' . ($objProduct->isNew() ? 'new ' : '') . $arrCSS[1]),
                'html'      => $objProduct->generate($arrConfig),
                'product'   => $objProduct,
            );
        }

        // HOOK: to add any product field or attribute to mod_iso_productlist template
        if (isset($GLOBALS['ISO_HOOKS']['generateProductList']) && is_array($GLOBALS['ISO_HOOKS']['generateProductList'])) {
            foreach ($GLOBALS['ISO_HOOKS']['generateProductList'] as $callback) {
                $objCallback = \System::importStatic($callback[0]);
                $arrBuffer   = $objCallback->$callback[1]($arrBuffer, $arrProducts, $this->Template, $this);
            }
        }

        RowClass::withKey('class')->addCount('product_')->addEvenOdd('product_')->addFirstLast('product_')->addGridRows($this->iso_cols)->addGridCols($this->iso_cols)->applyTo($arrBuffer);

        $this->Template->products = $arrBuffer;
    }


    /**
     * Find all products we need to list.
     * @param   array|null
     * @return  array
     */
    protected function findProducts($arrCacheIds = null)
    {
        $arrColumns    = array();
        $arrCategories = $this->findCategories();

        list($arrFilters, $arrSorting, $strWhere, $arrValues) = $this->getFiltersAndSorting();

        if (!is_array($arrValues)) {
            $arrValues = array();
        }

        $arrColumns[] = "c.page_id IN (" . implode(',', $arrCategories) . ")";

        if (!empty($arrCacheIds) && is_array($arrCacheIds)) {
            $arrColumns[] = Product::getTable() . ".id IN (" . implode(',', $arrCacheIds) . ")";
        }

        // Apply new/old product filter
        if ($this->iso_newFilter == 'show_new') {
            $arrColumns[] = Product::getTable() . ".dateAdded>=" . Isotope::getConfig()->getNewProductLimit();
        } elseif ($this->iso_newFilter == 'show_old') {
            $arrColumns[] = Product::getTable() . ".dateAdded<" . Isotope::getConfig()->getNewProductLimit();
        }

        if ($this->iso_list_where != '') {
            $arrColumns[] = Haste::getInstance()->call('replaceInsertTags', $this->iso_list_where);
        }

        if ($strWhere != '') {
            $arrColumns[] = $strWhere;
        }

        $objProducts = Product::findAvailableBy(
            $arrColumns,
            $arrValues,
            array(
                 'order'   => 'c.sorting',
                 'filters' => $arrFilters,
                 'sorting' => $arrSorting,
            )
        );

        return (null === $objProducts) ? array() : $objProducts->getModels();
    }

    /**
     * Compile template to show a message if there are no products
     *
     * @param bool $disableSearchIndex
     */
    protected function compileEmptyMessage($disableSearchIndex = true)
    {
        global $objPage;

        // Do not index or cache the page
        if ($disableSearchIndex) {
            $objPage->noSearch = 1;
            $objPage->cache    = 0;
        }

        $this->Template->empty    = true;
        $this->Template->type     = 'empty';
        $this->Template->message  = $this->iso_emptyMessage ? $this->iso_noProducts : $GLOBALS['TL_LANG']['MSC']['noProducts'];
        $this->Template->products = array();
    }


    /**
     * Generate the pagination
     * @param   array
     * @return  array
     */
    protected function generatePagination($arrItems)
    {
        $offset = 0;
        $limit  = null;

        // Set the limit
        if ($this->numberOfItems > 0) {
            $limit = $this->numberOfItems;
        }

        $total = count($arrItems);

        // Split the results
        if ($this->perPage > 0 && (!isset($limit) || $limit > $this->perPage)) {

            // Adjust the overall limit
            if (isset($limit)) {
                $total = min($limit, $total);
            }

            // Get the current page
            $id   = 'page_iso' . $this->id;
            $page = \Input::get($id) ? : 1;

            // Do not index or cache the page if the page number is outside the range
            if ($page < 1 || $page > max(ceil($total / $this->perPage), 1)) {
                global $objPage;

                $objHandler = new $GLOBALS['TL_PTY']['error_404']();
                $objHandler->generate($objPage->id);
                exit;
            }

            // Set limit and offset
            $limit = $this->perPage;
            $offset += (max($page, 1) - 1) * $this->perPage;

            // Overall limit
            if ($offset + $limit > $total) {
                $limit = $total - $offset;
            }

            // Add the pagination menu
            $objPagination              = new \Pagination($total, $this->perPage, $GLOBALS['TL_CONFIG']['maxPaginationLinks'], $id);
            $this->Template->pagination = $objPagination->generate("\n  ");
        }

        if (isset($limit)) {
            $arrItems = array_slice($arrItems, $offset, $limit);
        }

        return $arrItems;
    }


    /**
     * Get filter & sorting configuration
     * @param boolean
     * @return array
     */
    protected function getFiltersAndSorting($blnNativeSQL = true)
    {
        $arrFilters = Isotope::getRequestCache()->getFiltersForModules($this->iso_filterModules);
        $arrSorting = Isotope::getRequestCache()->getSortingsForModules($this->iso_filterModules);

        if (empty($arrSorting) && $this->iso_listingSortField != '') {
            $arrSorting[$this->iso_listingSortField] = ($this->iso_listingSortDirection == 'DESC' ? Sort::descending() : Sort::ascending());
        }

        if ($blnNativeSQL) {
            list($arrFilters, $strWhere, $arrValues) = RequestCache::buildSqlFilters($arrFilters);

            return array($arrFilters, $arrSorting, $strWhere, $arrValues);
        }

        return array($arrFilters, $arrSorting);
    }

    /**
     * Get a list of default options based on filter attributes
     * @return array
     */
    protected function getDefaultProductOptions()
    {
        $arrFields  = array_merge(Attribute::getVariantOptionFields(), Attribute::getCustomerDefinedFields());

        if (empty($arrFields)) {
            return array();
        }

        $arrOptions = array();
        $arrFilters = Isotope::getRequestCache()->getFiltersForModules($this->iso_filterModules);

        foreach ($arrFilters as $arrConfig) {
            if (in_array($arrConfig['attribute'], $arrFields)
                && ($arrConfig['operator'] == '=' || $arrConfig['operator'] == '==' || $arrConfig['operator'] == 'eq')
            ) {
                $arrOptions[$arrConfig['attribute']] = $arrConfig['value'];
            }
        }

        return $arrOptions;
    }

    /**
     * Returns the timestamp when the product cache expires
     * @return int
     */
    protected function getProductCacheExpiration()
    {
        $time = time();

        // Find timestamp when the next product becomes available
        $expires = (int) \Database::getInstance()->execute("SELECT MIN(start) AS expires FROM tl_iso_product WHERE start>$time")->expires;

        // Find
        if ($this->iso_newFilter == 'show_new' || $this->iso_newFilter == 'show_old') {
            $added = \Database::getInstance()->execute("SELECT MIN(dateAdded) FROM tl_iso_product WHERE dateAdded>" . Isotope::getConfig()->getNewProductLimit());

            if ($added < $expires) {
                $expires = $added;
            }
        }

        return $expires;
    }
}
