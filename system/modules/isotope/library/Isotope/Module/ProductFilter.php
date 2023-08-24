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
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\Environment;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Contao\Widget;
use Haste\Input\Input;
use Haste\Util\Format;
use Haste\Util\Url;
use Isotope\Interfaces\IsotopeAttributeWithOptions;
use Isotope\Interfaces\IsotopeFilterModule;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Isotope;
use Isotope\Model\Product;
use Isotope\Model\RequestCache;
use Isotope\RequestCache\CsvFilter;
use Isotope\RequestCache\Filter;
use Isotope\RequestCache\FilterQueryBuilder;
use Isotope\RequestCache\Limit;
use Isotope\RequestCache\Sort;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * ProductFilter allows to filter a product list by attributes.
 *
 * @property array $iso_searchExact
 */
class ProductFilter extends AbstractProductFilter implements IsotopeFilterModule
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'iso_filter_default';

    /**
     * Update request cache
     * @var bool
     */
    protected $blnUpdateCache = false;


    /**
     * Display a wildcard in the back end
     *
     * @return string
     */
    public function generate()
    {
        if ('BE' === TL_MODE) {
            return $this->generateWildcard();
        }

        $this->generateAjax();

        // Initialize module data.
        if (!$this->initializeFilters()) {
            return '';
        }

        // Hide product list in reader mode if the respective setting is enabled
        if ($this->iso_hide_list && Input::getAutoItem('product', false, true) != '') {
            return '';
        }

        $this->searchExactKeywords();

        $strBuffer = parent::generate();

        // Cache request in the database and redirect to the unique requestcache ID
        if ($this->blnUpdateCache) {
            $objCache = Isotope::getRequestCache()->saveNewConfiguration();

            // Include Environment::base or the URL would not work on the index page
            Controller::redirect(
                Environment::get('base') .
                Url::addQueryString(
                    'isorc='.$objCache->id,
                    Url::removeQueryStringCallback(
                        static function ($value, $key) {
                            return !str_starts_with($key, 'page_iso');
                        },
                        ($this->jumpTo ?: null)
                    )
                )
            );
        }

        return $strBuffer;
    }

    /**
     * Generate ajax
     *
     * @throws \Exception
     */
    public function generateAjax()
    {
        if (!Environment::get('isAjaxRequest')) {
            return;
        }

        if ($this->iso_searchAutocomplete && Input::get('iso_autocomplete') == $this->id) {
            /** @var IsotopeProduct[] $products */
            $products = Product::findPublishedByCategories($this->findCategories(), ['order' => 'c.sorting', 'return' => 'Array']);

            $products = array_filter($products, static function (IsotopeProduct $product) {
                return $product->isAvailableInFrontend();
            });

            if (empty($products)) {
                throw new ResponseException(new JsonResponse([]));
            }

            $data = array_map(function (IsotopeProduct $product) {
                $value = $product->{$this->iso_searchAutocomplete};

                return Controller::replaceInsertTags($value);
            }, $products);

            // Make sure we don't show duplicate autocomplete options and JSON generates an array not an object
            $data = array_values(array_unique($data));

            throw new ResponseException(new JsonResponse($data));
        }
    }

    /**
     * @inheritdoc
     */
    protected function getSerializedProperties()
    {
        $props = parent::getSerializedProperties();

        $props[] = 'iso_searchExact';

        return $props;
    }

    /**
     * Initialize module data. You can override this function in a child class
     *
     * @return bool
     */
    protected function initializeFilters()
    {
        if (!$this->iso_enableLimit
            && 0 === \count($this->iso_filterFields)
            && 0 === \count($this->iso_sortingFields)
            && 0 === \count($this->iso_searchFields)
        ) {
            return false;
        }

        if ($this->iso_filterTpl) {
            $this->strTemplate = $this->iso_filterTpl;
        }

        return true;
    }

    /**
     * Generate the module
     */
    protected function compile()
    {
        $this->blnUpdateCache = ('iso_filter_' . $this->id) === Input::post('FORM_SUBMIT');

        $this->generateFilters();
        $this->generateSorting();
        $this->generateLimit();

        // If we update the cache and reload the page, we don't need to build the template
        if ($this->blnUpdateCache) {
            return;
        }

        // Search does not affect request cache
        $this->generateSearch();

        $this->Template->id = $this->id;
        $this->Template->formId = 'iso_filter_'.$this->id;
        $this->Template->actionClear = ampersand(strtok(Environment::get('request'), '?'));
        $this->Template->clearLabel = $GLOBALS['TL_LANG']['MSC']['clearFiltersLabel'];
        $this->Template->slabel = $GLOBALS['TL_LANG']['MSC']['submitLabel'];
    }

    /**
     * Generate a search form
     *
     * @throws \Exception
     */
    protected function generateSearch()
    {
        $this->Template->hasSearch       = false;
        $this->Template->hasAutocomplete = $this->iso_searchAutocomplete ? true : false;

        $keywords = (string) Input::get('keywords');

        if (0 !== \count($this->iso_searchFields)) {
            if ('' !== $keywords
                && $keywords !== $GLOBALS['TL_LANG']['MSC']['defaultSearchText']
            ) {
                // Redirect to search result page if one is set (see #1068)
                if (!$this->blnUpdateCache
                    && null !== $this->objModel->getRelated('jumpTo')
                ) {
                    /** @var PageModel $objJumpTo */
                    $objJumpTo = $this->objModel->getRelated('jumpTo');
                    $strUrl    = $objJumpTo->getFrontendUrl() . '?' . Environment::get('queryString');

                    if (Environment::get('request') != $strUrl) {
                        // Include Environment::base or the URL would not work on the index page
                        Controller::redirect(Environment::get('base') . $strUrl);
                    }
                }

                $arrKeywords = StringUtil::trimsplit(' |-', $keywords);
                $arrKeywords = array_filter(array_unique($arrKeywords));

                foreach ($arrKeywords as $keyword) {
                    foreach ($this->iso_searchFields as $field) {
                        Isotope::getRequestCache()->addFilterForModule(
                            Filter::attribute($field)->contains($keyword)->groupBy('keyword: ' . $keyword),
                            $this->id
                        );
                    }
                }
            }

            $this->Template->hasSearch         = true;
            $this->Template->keywordsLabel     = $GLOBALS['TL_LANG']['MSC']['searchTermsLabel'];
            $this->Template->keywords          = $keywords;
            $this->Template->searchLabel       = $GLOBALS['TL_LANG']['MSC']['searchLabel'];
            $this->Template->defaultSearchText = $GLOBALS['TL_LANG']['MSC']['defaultSearchText'];
        }
    }

    /**
     * Generate a filter form
     */
    protected function generateFilters()
    {
        $this->Template->hasFilters = false;

        if (empty($this->iso_filterFields)) {
            return;
        }

        $arrFilters    = [];
        $arrInput      = Input::post('filter');
        $arrCategories = $this->findCategories();

        foreach ($this->iso_filterFields as $strField) {
            $arrValues = $this->getUsedValuesForAttribute(
                $strField,
                $arrCategories,
                $this->iso_newFilter,
                $this->iso_list_where
            );

            if ($this->blnUpdateCache && \in_array($arrInput[$strField], $arrValues)) {
                if ($this->isCsv($strField)) {
                    $filter = CsvFilter::attribute($strField)->contains($arrInput[$strField]);
                } else {
                    $filter = Filter::attribute($strField)->isEqualTo($arrInput[$strField]);
                }

                Isotope::getRequestCache()->setFilterForModule(
                    $strField,
                    $filter,
                    $this->id
                );

                continue;
            }

            if ($this->blnUpdateCache && empty($arrInput[$strField])) {
                Isotope::getRequestCache()->removeFilterForModule($strField, $this->id);
                continue;
            }

            // Request cache contains wrong value, delete it!
            if (null !== ($objFilter = Isotope::getRequestCache()->getFilterForModule($strField, $this->id))
                && $objFilter->valueNotIn($arrValues)
            ) {
                $this->blnUpdateCache = true;
                Isotope::getRequestCache()->removeFilterForModule($strField, $this->id);

                RequestCache::deleteById(Input::get('isorc'));
                continue;
            }

            // Only generate options if we do not reload anyway
            if (!$this->blnUpdateCache) {
                if (0 === \count($arrValues)) {
                    continue;
                }

                $arrData = $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$strField];

                // Use the default routine to initialize options data
                $arrWidget = Widget::getAttributesFromDca($arrData, $strField);
                $objFilter = Isotope::getRequestCache()->getFilterForModule($strField, $this->id);

                if (($objAttribute = $GLOBALS['TL_DCA']['tl_iso_product']['attributes'][$strField]) !== null
                    && $objAttribute instanceof IsotopeAttributeWithOptions
                ) {
                    $arrWidget['options'] = $objAttribute->getOptionsForProductFilter($arrValues);
                }

                // Generate options from database values (e.g. for text fields)
                if (!\is_array($arrWidget['options'])) {
                    $arrWidget['options'] = array_map(static fn ($v) => ['value' => $v, 'label' => $v], $arrValues);
                }

                foreach ($arrWidget['options'] as $k => $option) {
                    if ($option['value'] == '') {
                        $arrWidget['blankOptionLabel'] = $option['label'];
                        unset($arrWidget['options'][$k]);
                        continue;
                    }

                    // @deprecated IsotopeAttributeWithOptions::getOptionsForProductFilter already checks this
                    if ('-' === $option['value'] || !\in_array($option['value'], $arrValues)) {
                        unset($arrWidget['options'][$k]);
                        continue;
                    }

                    $arrWidget['options'][$k]['default'] = ((null !== $objFilter && $objFilter->valueEquals($option['value'])) ? '1' : '');
                }

                // Hide fields with just one option (if enabled)
                if ($this->iso_filterHideSingle && \count($arrWidget['options']) < 2) {
                    continue;
                }

                $arrFilters[$strField] = $arrWidget;
            }
        }

        // !HOOK: alter the filters
        if (isset($GLOBALS['ISO_HOOKS']['generateFilters']) && \is_array($GLOBALS['ISO_HOOKS']['generateFilters'])) {
            foreach ($GLOBALS['ISO_HOOKS']['generateFilters'] as $callback) {
                $arrFilters = System::importStatic($callback[0])->{$callback[1]}($arrFilters);
            }
        }

        if (!empty($arrFilters)) {
            $this->Template->hasFilters    = true;
            $this->Template->filterOptions = $arrFilters;
        }
    }

    /**
     * Generate a sorting form
     */
    protected function generateSorting()
    {
        $this->Template->hasSorting = false;

        if (0 !== \count($this->iso_sortingFields)) {
            $arrOptions = [];

            // Cache new request value
            // @todo should support multiple sorting fields
            $sortingField = Input::post('sorting');
            $sortingDirection = 'ASC';
            if (false !== strpos($sortingField, ':')) {
                [$sortingField, $sortingDirection] = explode(':', $sortingField);
            }

            if ($this->blnUpdateCache && \in_array($sortingField, $this->iso_sortingFields, true)) {
                Isotope::getRequestCache()->setSortingForModule(
                    $sortingField,
                    ('DESC' === $sortingDirection ? Sort::descending() : Sort::ascending()),
                    $this->id
                );

            } elseif (array_diff(
                array_keys(
                    Isotope::getRequestCache()->getSortingsForModules([$this->id])
                ),
                $this->iso_sortingFields
            )) {
                // Request cache contains wrong value, delete it!

                $this->blnUpdateCache = true;
                Isotope::getRequestCache()->unsetSortingsForModule($this->id);

                RequestCache::deleteById(Input::get('isorc'));

            } elseif (!$this->blnUpdateCache) {
                // No need to generate options if we reload anyway

                $first = Isotope::getRequestCache()->getFirstSortingFieldForModule($this->id);

                if ('' === $first) {
                    $first = $this->iso_listingSortField;
                    $objSorting = 'DESC' === $this->iso_listingSortDirection ? Sort::descending() : Sort::ascending();
                } else {
                    $objSorting = Isotope::getRequestCache()->getSortingForModule($first, $this->id);
                }

                foreach ($this->iso_sortingFields as $field) {
                    [$asc, $desc] = $this->getSortingLabels($field);
                    $isDefault = $first === $field && null !== $objSorting;

                    $arrOptions[] = [
                        'label'   => Format::dcaLabel('tl_iso_product', $field) . ', ' . $asc,
                        'value'   => $field . ':ASC',
                        'default' => ($isDefault && $objSorting->isAscending()) ? '1' : '',
                    ];

                    $arrOptions[] = [
                        'label'   => Format::dcaLabel('tl_iso_product', $field) . ', ' . $desc,
                        'value'   => $field . ':DESC',
                        'default' => ($isDefault && $objSorting->isDescending()) ? '1' : '',
                    ];
                }
            }

            $this->Template->hasSorting     = true;
            $this->Template->sortingLabel   = $GLOBALS['TL_LANG']['MSC']['orderByLabel'];
            $this->Template->sortingOptions = $arrOptions;
        }
    }

    /**
     * Generate a limit form
     */
    protected function generateLimit()
    {
        $this->Template->hasLimit = false;

        if ($this->iso_enableLimit) {
            $arrOptions = [];
            $arrLimit   = array_map('intval', StringUtil::trimsplit(',', $this->iso_perPage));
            $objLimit   = Isotope::getRequestCache()->getFirstLimitForModules([$this->id]);
            $arrLimit   = array_unique($arrLimit);
            sort($arrLimit);

            if ($this->blnUpdateCache && \in_array(Input::post('limit'), $arrLimit)) {
                // Cache new request value

                Isotope::getRequestCache()->setLimitForModule(Limit::to(Input::post('limit')), $this->id);

            } elseif ($objLimit->notIn($arrLimit)) {
                // Request cache contains wrong value, delete it!

                $this->blnUpdateCache = true;
                Isotope::getRequestCache()->setLimitForModule(Limit::to($arrLimit[0]), $this->id);

                RequestCache::deleteById(Input::get('isorc'));

            } elseif (!$this->blnUpdateCache) {
                // No need to generate options if we reload anyway

                foreach ($arrLimit as $limit) {
                    $arrOptions[] = [
                        'label'   => $limit,
                        'value'   => $limit,
                        'default' => $objLimit->equals($limit) ? '1' : '',
                    ];
                }

                $this->Template->hasLimit     = true;
                $this->Template->limitLabel   = $GLOBALS['TL_LANG']['MSC']['perPageLabel'];
                $this->Template->limitOptions = $arrOptions;
            }
        }
    }

    protected function searchExactKeywords()
    {
        $keywords = (string) Input::get('keywords');

        if (empty($this->iso_searchExact) || empty($keywords)) {
            return;
        }

        $filters = [];
        $arrCategories = $this->findCategories();

        foreach ($this->iso_searchExact as $field) {
            $filters[] = Filter::attribute($field)->isEqualTo($keywords)->groupBy('exact-match');
        }

        $filters = new FilterQueryBuilder($filters);
        $arrColumns = [$filters->getSqlWhere()];
        $arrValues = $filters->getSqlValues();

        if (1 === \count($arrCategories)) {
            $arrColumns[] = "c.page_id=".$arrCategories[0];
        } else {
            $arrColumns[] = "c.page_id IN (".implode(',', $arrCategories).")";
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

        $products = Product::findAvailableBy($arrColumns, $arrValues);

        if (null !== $products && $products->count() === 1) {
            /** @var Product $product */
            $product = $products->current();

            throw new RedirectResponseException($product->generateUrl($this->findJumpToPage($product), true));
        }
    }
}
