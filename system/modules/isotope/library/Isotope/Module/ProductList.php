<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Module;

use Haste\Generator\RowClass;
use Haste\Http\Response\HtmlResponse;
use Haste\Input\Input;
use Isotope\Isotope;
use Isotope\Model\Attribute;
use Isotope\Model\Product;
use Isotope\Model\ProductCache;
use Isotope\Model\ProductType;
use Isotope\RequestCache\FilterQueryBuilder;
use Isotope\RequestCache\Sort;
use Isotope\Template;

/**
 * @property string $iso_list_layout
 * @property int    $iso_cols
 * @property bool   $iso_use_quantity
 * @property int    $iso_gallery
 * @property array  $iso_filterModules
 * @property array  $iso_productcache
 * @property string $iso_listingSortField
 * @property string $iso_listingSortDirection
 * @property bool   $iso_jump_first
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
     *
     * @deprecated Deprecated since version 2.3, to be removed in 3.0.
     *             Implement getCacheKey() to always cache result.
     */
    protected $blnCacheProducts = true;

    /**
     * @inheritDoc
     */
    protected function getSerializedProperties()
    {
        $props = parent::getSerializedProperties();

        $props[] = 'iso_filterModules';
        $props[] = 'iso_productcache';

        return $props;
    }

    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        if ('BE' === TL_MODE) {
            return $this->generateWildcard();
        }

        // Hide product list in reader mode if the respective setting is enabled
        if ($this->iso_hide_list && Input::getAutoItem('product', false, true) != '') {
            return '';
        }

        // Disable the cache in frontend preview or debug mode
        if (BE_USER_LOGGED_IN === true || $GLOBALS['TL_CONFIG']['debugMode']) {
            $this->blnCacheProducts = false;
        }

        // Apply limit from filter module
        $this->perPage = Isotope::getRequestCache()
            ->getFirstLimitForModules($this->iso_filterModules, $this->perPage)
            ->asInt()
        ;

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
       $productCount = 0;
       $queryBuilder = null;

        // return message if no filter is set
        if ($this->iso_emptyFilter && !\Input::get('isorc') && !\Input::get('keywords')) {
            $this->Template->message  = \Controller::replaceInsertTags($this->iso_noFilter);
            $this->Template->type     = 'noFilter';
            $this->Template->products = array();

            return;
        }

        global $objPage;
        $cacheKey      = $this->getCacheKey();
        $arrProducts   = null;
        $arrCacheIds   = null;

        // Try to load the products from cache
        if ($this->blnCacheProducts && ($objCache = ProductCache::findByUniqid($cacheKey)) !== null) {
            $arrCacheIds = $objCache->getProductIds();
            $productCount=count($arrCacheIds);

            // Use the cache if keywords match. Otherwise we will use the product IDs as a "limit" for findProducts()
            if ($objCache->keywords == \Input::get('keywords')) {
                $arrCacheIds = $this->generatePagination($arrCacheIds);

                $objProducts = Product::findAvailableByIds($arrCacheIds, array(
                    'order' => \Database::getInstance()->findInSet(Product::getTable().'.id', $arrCacheIds)
                ));

                $arrProducts = (null === $objProducts) ? array() : $objProducts->getModels();

        $queryBuilder  = new FilterQueryBuilder(
            Isotope::getRequestCache()->getFiltersForModules($this->iso_filterModules)
        );
                // Cache is wrong, drop everything and run findProducts()
                if (count($arrProducts) != count($arrProducts)) {
                    $productCount=count($arrCacheIds);
                    $arrCacheIds = null;
                    $arrProducts = null;
                }
            }
        }

        if (!is_array($arrProducts)) {
            // Display "loading products" message and add cache flag
            if ($this->blnCacheProducts) {
                $blnCacheMessage = (bool) $this->iso_productcache[$cacheKey];

                if ($blnCacheMessage && !\Input::get('buildCache')) {
                    // Do not index or cache the page
                    $objPage->noSearch = 1;
                    $objPage->cache    = 0;

                    $this->Template          = new Template('mod_iso_productlist_caching');
                    $this->Template->message = $GLOBALS['TL_LANG']['MSC']['productcacheLoading'];

                    return;
                }

                // Start measuring how long it takes to load the products
                $start = microtime(true);

                // Load products
                $arrProducts = $this->findProducts($arrCacheIds);
		$productCount=count($arrProducts);
        $queryBuilder  = new FilterQueryBuilder(
            Isotope::getRequestCache()->getFiltersForModules($this->iso_filterModules)
        );

                // Decide if we should show the "caching products" message the next time
                $end = microtime(true) - $start;
                $this->blnCacheProducts = $end > 1 ? true : false;

                $arrCacheMessage = $this->iso_productcache;
                if ($blnCacheMessage != $this->blnCacheProducts) {
                    $arrCacheMessage[$cacheKey] = $this->blnCacheProducts;

                    \Database::getInstance()
                        ->prepare('UPDATE tl_module SET iso_productcache=? WHERE id=?')
                        ->execute(serialize($arrCacheMessage), $this->id)
                    ;
                }

                // Do not write cache if table is locked. That's the case if another process is already writing cache
                if (ProductCache::isWritable()) {
                    \Database::getInstance()
                        ->lockTables(array(ProductCache::getTable() => 'WRITE', 'tl_iso_product' => 'READ'))
                    ;

                    $arrIds = array();
                    foreach ($arrProducts as $objProduct) {
                        $arrIds[] = $objProduct->id;
                    }

                    // Delete existing cache if necessary
                    ProductCache::deleteByUniqidOrExpired($cacheKey);

                    $objCache          = ProductCache::createForUniqid($cacheKey);
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

        /** @var \Isotope\Model\Product\Standard $objProduct */
        foreach ($arrProducts as $objProduct) {
            /** @var ProductType $type */
            $type = $objProduct->getRelated('type');

            $arrConfig = array(
                'module'        => $this,
                'template'      => $this->iso_list_layout ?: $type->list_template,
                'gallery'       => $this->iso_gallery ?: $type->list_gallery,
                'buttons'       => $this->iso_buttons,
                'useQuantity'   => $this->iso_use_quantity,
                'jumpTo'        => $this->findJumpToPage($objProduct),
            );

            if (\Environment::get('isAjaxRequest')
                && \Input::post('AJAX_MODULE') == $this->id
                && \Input::post('AJAX_PRODUCT') == $objProduct->getProductId()
            ) {
                $objResponse = new HtmlResponse($objProduct->generate($arrConfig));
                $objResponse->send();
            }

            $objProduct->mergeRow($arrDefaultOptions);

            // Must be done after setting options to generate the variant config into the URL
            if ($this->iso_jump_first && Input::getAutoItem('product', false, true) == '') {
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
        if (isset($GLOBALS['ISO_HOOKS']['generateProductList'])
            && is_array($GLOBALS['ISO_HOOKS']['generateProductList'])
        ) {
            foreach ($GLOBALS['ISO_HOOKS']['generateProductList'] as $callback) {
                $objCallback = \System::importStatic($callback[0]);
                $arrBuffer   = $objCallback->{$callback[1]}($arrBuffer, $arrProducts, $this->Template, $this);
            }
        }

        RowClass::withKey('class')
            ->addCount('product_')
            ->addEvenOdd('product_')
            ->addFirstLast('product_')
            ->addGridRows($this->iso_cols)
            ->addGridCols($this->iso_cols)
            ->applyTo($arrBuffer)
        ;

        $this->Template->filters=$queryBuilder;
        $this->Template->countProducts=$productCount;
        $this->Template->products = $arrBuffer;
    }

    /**
     * Find all products we need to list.
     *
     * @param array|null $arrCacheIds
     *
     * @return array
     */
    protected function findProducts($arrCacheIds = null)
    {
        $arrColumns    = array();
        $arrCategories = $this->findCategories();
        $queryBuilder  = new FilterQueryBuilder(
            Isotope::getRequestCache()->getFiltersForModules($this->iso_filterModules)
        );

        $arrColumns[]  = "c.page_id IN (" . implode(',', $arrCategories) . ")";

        if (!empty($arrCacheIds) && is_array($arrCacheIds)) {
            $arrColumns[] = Product::getTable() . ".id IN (" . implode(',', $arrCacheIds) . ")";
        }

        // Apply new/old product filter
        if ('show_new' === $this->iso_newFilter) {
            $arrColumns[] = Product::getTable() . ".dateAdded>=" . Isotope::getConfig()->getNewProductLimit();
        } elseif ('show_old' === $this->iso_newFilter) {
            $arrColumns[] = Product::getTable() . ".dateAdded<" . Isotope::getConfig()->getNewProductLimit();
        }

        if ($this->iso_list_where != '') {
            $arrColumns[] = $this->iso_list_where;
        }

        if ($queryBuilder->hasSqlCondition()) {
            $arrColumns[] = $queryBuilder->getSqlWhere();
        }

        $arrSorting = Isotope::getRequestCache()->getSortingsForModules($this->iso_filterModules);

        if (empty($arrSorting) && $this->iso_listingSortField != '') {
            $direction = ('DESC' === $this->iso_listingSortDirection ? Sort::descending() : Sort::ascending());
            $arrSorting[$this->iso_listingSortField] = $direction;
        }

        $objProducts = Product::findAvailableBy(
            $arrColumns,
            $queryBuilder->getSqlValues(),
            array(
                 'order'   => 'c.sorting',
                 'filters' => $queryBuilder->getFilters(),
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

        $message = $this->iso_emptyMessage ? $this->iso_noProducts : $GLOBALS['TL_LANG']['MSC']['noProducts'];

        $this->Template->empty    = true;
        $this->Template->type     = 'empty';
        $this->Template->message  = $message;
        $this->Template->products = array();
    }

    /**
     * Generate the pagination
     *
     * @param array $arrItems
     *
     * @return array
     */
    protected function generatePagination($arrItems)
    {
        $offset = 0;
        $limit  = null;

        // Set the limit
        if ($this->numberOfItems > 0) {
            $limit = $this->numberOfItems;
        }

        $pagination = '';
        $page       = 1;
        $total      = count($arrItems);

        // Split the results
        if ($this->perPage > 0 && (!isset($limit) || $limit > $this->perPage)) {
            // Adjust the overall limit
            if (isset($limit)) {
                $total = min($limit, $total);
            }

            // Get the current page
            $id   = 'page_iso' . $this->id;
            $page = \Input::get($id) ?: 1;

            // Do not index or cache the page if the page number is outside the range
            if ($page < 1 || $page > max(ceil($total / $this->perPage), 1)) {
                global $objPage;

                /** @var \PageError404 $objHandler */
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
            $objPagination = new \Pagination($total, $this->perPage, $GLOBALS['TL_CONFIG']['maxPaginationLinks'], $id);

            $pagination = $objPagination->generate("\n  ");
        }

        $this->Template->pagination = $pagination;
        $this->Template->total      = count($arrItems);
        $this->Template->page       = $page;
        $this->Template->offset     = $offset;
        $this->Template->limit      = $limit;

        if (isset($limit)) {
            $arrItems = array_slice($arrItems, $offset, $limit);
        }

        return $arrItems;
    }


    /**
     * Get filter & sorting configuration
     *
     * @param boolean
     *
     * @return array
     *
     * @deprecated Deprecated since Isotope 2.3, to be removed in 3.0.
     *             Use Isotope\RequestCache\FilterQueryBuilder instead.
     */
    protected function getFiltersAndSorting($blnNativeSQL = true)
    {
        $arrFilters = Isotope::getRequestCache()->getFiltersForModules($this->iso_filterModules);
        $arrSorting = Isotope::getRequestCache()->getSortingsForModules($this->iso_filterModules);

        if (empty($arrSorting) && $this->iso_listingSortField != '') {
            $direction = ('DESC' === $this->iso_listingSortDirection ? Sort::descending() : Sort::ascending());
            $arrSorting[$this->iso_listingSortField] = $direction;
        }

        if (!$blnNativeSQL) {
            return array($arrFilters, $arrSorting);
        }

        $queryBuilder = new FilterQueryBuilder($arrFilters);

        return array(
            $queryBuilder->getFilters(),
            $arrSorting,
            $queryBuilder->getSqlWhere(),
            $queryBuilder->getSqlValues()
        );
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
                && ('=' === $arrConfig['operator'] || '==' === $arrConfig['operator'] || 'eq' === $arrConfig['operator'])
            ) {
                $arrOptions[$arrConfig['attribute']] = $arrConfig['value'];
            }
        }

        return $arrOptions;
    }

    /**
     * Generates a unique cache key for the product cache.
     * Child classes should likely overwrite this, see RelatedProducts class for an example.
     *
     * @return string A 32 char cache key (e.g. MD5)
     */
    protected function getCacheKey()
    {
        $categories = $this->findCategories();

        // Sort categories so cache key is always the same
        sort($categories);

        return md5(
            'productlist=' . $this->id . ':'
            . 'where=' . $this->iso_list_where . ':'
            . 'isorc=' . (int) \Input::get('isorc') . ':'
            . implode(',', $categories)
        );
    }

    /**
     * Returns the timestamp when the product cache expires
     *
     * @return int
     */
    protected function getProductCacheExpiration()
    {
        $time = \Date::floorToMinute();

        // Find timestamp when the next product becomes available
        $expires = (int) \Database::getInstance()
            ->execute("SELECT MIN(start) AS expires FROM tl_iso_product WHERE start>'$time'")
            ->expires
        ;

        // Find
        if ('show_new' === $this->iso_newFilter || 'show_old' === $this->iso_newFilter) {
            $added = \Database::getInstance()
                ->execute("
                    SELECT MIN(dateAdded)
                    FROM tl_iso_product
                    WHERE dateAdded>" . Isotope::getConfig()->getNewProductLimit() . "
                ")
            ;

            if ($added < $expires) {
                $expires = $added;
            }
        }

        return $expires;
    }
}
