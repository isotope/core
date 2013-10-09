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

use Isotope\Isotope;
use Isotope\Model\Product;
use Isotope\Model\ProductCache;
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
        if (TL_MODE == 'BE')
        {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ISOTOPE ECOMMERCE: PRODUCT LIST ###';

            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        // Hide product list in reader mode if the respective setting is enabled
        if ($this->iso_hide_list && \Isotope\Frontend::getAutoItem('product') != '') {
            return '';
        }

        $this->iso_filterModules = deserialize($this->iso_filterModules, true);
        $this->iso_productcache = deserialize($this->iso_productcache, true);

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
            $this->Template->message = $this->replaceInsertTags($this->iso_noFilter);
            $this->Template->type = 'noFilter';
            $this->Template->products = array();
            return;
        }

        global $objPage;
        $intPage = ($this->iso_category_scope == 'article' ? $GLOBALS['ISO_CONFIG']['current_article']['pid'] : $objPage->id);
        $arrProducts = null;

		// Try to load the products from cache
        if ($this->blnCacheProducts && ($objCache = ProductCache::findForPageAndModule($intPage, $this->id)) !== null) {
            $arrCacheIds = $objCache->getProductIds();

            // Use the cache if keywords match. Otherwise we will use the product IDs as a "limit" for findProducts()
            if ($objCache->keywords == \Input::get('keywords')) {
            	$arrCacheIds = $this->generatePagination($arrCacheIds);
                $arrProducts = \Isotope\Frontend::getProducts($arrCacheIds);

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
                    $objPage->cache = 0;

                    $this->Template = new \Isotope\Template('mod_iso_productlist_caching');
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

                if ($blnCacheMessage != $this->blnCacheProducts)
                {
                    $arrCacheMessage = $this->iso_productcache;
                    $arrCacheMessage[$intPage][(int) \Input::get('isorc')] = $this->blnCacheProducts;
                    \Database::getInstance()->prepare("UPDATE tl_module SET iso_productcache=? WHERE id=?")->execute(serialize($arrCacheMessage), $this->id);
                }

                // Do not write cache if table is locked. That's the case if another process is already writing cache
                if (ProductCache::isWritable()) {

                    \Database::getInstance()->lockTables(array(ProductCache::getTable()=>'WRITE', 'tl_iso_products'=>'READ'));

                    $arrIds = array();
                    foreach ($arrProducts as $objProduct) {
                        $arrIds[] = $objProduct->id;
                    }

                    // Delete existing cache if necessary
                    ProductCache::deleteForPageAndModuleOrExpired($intPage, $this->id);

                    $objCache = ProductCache::createForPageAndModule($intPage, $this->id);
                    $objCache->expires = $this->getProductCacheExpiration();
                    $objCache->setProductIds($arrIds);
                    $objCache->save();

                    \Database::getInstance()->unlockTables();
                }
            } else {
                $arrProducts = $this->findProducts();
            }

            $arrProducts = $this->generatePagination($arrProducts);
        }

        // No products found
        if (!is_array($arrProducts) || empty($arrProducts)) {

            // Do not index or cache the page
            $objPage->noSearch = 1;
            $objPage->cache = 0;

            $this->Template->empty = true;
            $this->Template->type = 'empty';
            $this->Template->message = $this->iso_emptyMessage ? $this->iso_noProducts : $GLOBALS['TL_LANG']['MSC']['noProducts'];
            $this->Template->products = array();

            return;
        }

        $arrBuffer = array();

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

            if (\Environment::get('isAjaxRequest') && \Input::get('AJAX_MODULE') == $this->id && \Input::get('AJAX_PRODUCT') == $objProduct->id) {
                \Isotope\Frontend::ajaxResponse($objProduct->generate($arrConfig));
            }

            $objProduct->setOptions(array_merge($arrDefaultOptions, $objProduct->getOptions()));

            // Must be done after setting options to generate the variant config into the URL
            if ($this->iso_jump_first && \Isotope\Frontend::getAutoItem('product') == '') {
                \Controller::redirect($objProduct->generateUrl($arrConfig['jumpTo']));
            }

            $arrBuffer[] = array(
                'cssID'     => ($objProduct->cssID[0] != '') ? ' id="' . $objProduct->cssID[0] . '"' : '',
                'class'     => trim('product ' . ($objProduct->isNew() ? 'new ' : '') . $objProduct->cssID[1]),
                'html'      => $objProduct->generate($arrConfig),
                'product'   => $objProduct,
            );
        }

        // HOOK: to add any product field or attribute to mod_iso_productlist template
        if (isset($GLOBALS['ISO_HOOKS']['generateProductList']) && is_array($GLOBALS['ISO_HOOKS']['generateProductList']))
        {
            foreach ($GLOBALS['ISO_HOOKS']['generateProductList'] as $callback)
            {
                $objCallback = \System::importStatic($callback[0]);
                $arrBuffer = $objCallback->$callback[1]($arrBuffer, $arrProducts, $this->Template, $this);
            }
        }

        $this->Template->products = \Isotope\Frontend::generateRowClass($arrBuffer, 'product', 'class', $this->iso_cols);
    }


    /**
     * Find all products we need to list.
     * @return array
     */
    protected function findProducts($arrCacheIds=null)
    {
        $arrColumns = array();
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
            $arrColumns[] = $this->iso_list_where;
        }

        if ($strWhere != '') {
            $arrColumns[] = $strWhere;
        }

        $objProducts = Product::findPublishedBy($arrColumns, $arrValues, array('group'=>Product::getTable() . '.id', 'order'=>'c.sorting'));

        return \Isotope\Frontend::getProducts($objProducts, true, $arrFilters, $arrSorting);
    }


    /**
     * Generate the pagination
     * @param   array
     * @return  array
     */
    protected function generatePagination($arrItems)
    {
    	$offset = 0;
        $limit = null;

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
			$id = 'page_iso' . $this->id;
			$page = \Input::get($id) ?: 1;

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
			$objPagination = new \Pagination($total, $this->perPage, $GLOBALS['TL_CONFIG']['maxPaginationLinks'], $id);
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
    protected function getFiltersAndSorting($blnNativeSQL=true)
    {
        $arrFilters = Isotope::getRequestCache()->getFiltersForModules($this->iso_filterModules);
        $arrSorting = Isotope::getRequestCache()->getSortingsForModules($this->iso_filterModules);

        if (empty($arrSorting) && $this->iso_listingSortField != '') {
            $arrSorting[$this->iso_listingSortField] = ($this->iso_listingSortDirection == 'DESC' ? Sort::descending() : Sort::ascending());
        }

        if ($blnNativeSQL) {
            $strWhere = '';
            $arrWhere = array();
            $arrValues = array();
            $arrGroups = array();

            // Initiate native SQL filtering
            foreach ($arrFilters as $k => $objFilter) {
                if ($objFilter->hasGroup() && $arrGroups[$objFilter->getGroup()] !== false) {
                    if ($objFilter->isDynamicAttribute()) {
                        $arrGroups[$objFilter->getGroup()] = false;
                    } else {
                        $arrGroups[$objFilter->getGroup()][] = $k;
                    }
                } elseif (!$objFilter->hasGroup() && !$objFilter->isDynamicAttribute()) {
                    $arrWhere[] = $objFilter->sqlWhere();
                    $arrValues[] = $objFilter->sqlValue();
                    unset($arrFilters[$k]);
                }
            }

            if (!empty($arrGroups)) {
                foreach ($arrGroups as $arrGroup) {
                    $arrGroupWhere = array();

                    foreach ($arrGroup as $k) {
                        $objFilter = $arrFilters[$k];

                        $arrGroupWhere[] = $objFilter->sqlWhere();
                        $arrValues[] = $objFilter->sqlValue();
                        unset($arrFilters[$k]);
                    }

                    $arrWhere[] = '(' . implode(' OR ', $arrGroupWhere) . ')';
                }
            }

            if (!empty($arrWhere)) {
                $time = time();
                $t = Product::getTable();

                $strWhere = "((" . implode(' AND ', $arrWhere) . ") OR $t.id IN (SELECT $t.pid FROM tl_iso_products AS $t WHERE $t.language='' AND " . implode(' AND ', $arrWhere)
                            . (BE_USER_LOGGED_IN === true ? '' : " AND $t.published='1' AND ($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time)") . "))";
                $arrValues = array_merge($arrValues, $arrValues);
            }

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
        $arrOptions = array();
        $arrFilters = Isotope::getRequestCache()->getFiltersForModules($this->iso_filterModules);

        foreach ($arrFilters as $arrConfig)
        {
            if ($arrConfig['operator'] == '=' || $arrConfig['operator'] == '==' || $arrConfig['operator'] == 'eq')
            {
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
        $expires = (int) \Database::getInstance()->execute("SELECT MIN(start) AS expires FROM tl_iso_products WHERE start>$time")->expires;

        // Find
        if ($this->iso_newFilter == 'show_new' || $this->iso_newFilter == 'show_old') {
            $added = \Database::getInstance()->execute("SELECT MIN(dateAdded) FROM tl_iso_products WHERE dateAdded>" . Isotope::getConfig()->getNewProductLimit());

            if ($added < $expires) {
                $expires = $added;
            }
        }

        return $expires;
    }
}
