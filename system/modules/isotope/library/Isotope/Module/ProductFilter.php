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
use Haste\Http\Response\JsonResponse;
use Haste\Util\Format;
use Haste\Util\Url;
use Isotope\Interfaces\IsotopeAttributeWithOptions;
use Isotope\Interfaces\IsotopeFilterModule;
use Isotope\Isotope;
use Isotope\Model\Product;
use Isotope\Model\RequestCache;
use Isotope\RequestCache\Filter;
use Isotope\RequestCache\Limit;
use Isotope\RequestCache\Sort;


/**
 * Class ProductFilter
 *
 * Front end module Isotope "product filter".
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
class ProductFilter extends Module implements IsotopeFilterModule
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
        if (TL_MODE == 'BE') {
            $objTemplate           = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ISOTOPE ECOMMERCE: PRODUCT FILTERS ###';

            $objTemplate->title = $this->headline;
            $objTemplate->id    = $this->id;
            $objTemplate->link  = $this->name;
            $objTemplate->href  = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        $this->generateAjax();

        // Initialize module data.
        if (!$this->initializeFilters()) {
            return '';
        }

        // Hide product list in reader mode if the respective setting is enabled
        if ($this->iso_hide_list && \Haste\Input\Input::getAutoItem('product', false, true) != '') {
            return '';
        }

        $strBuffer = parent::generate();

        // Cache request in the database and redirect to the unique requestcache ID
        if ($this->blnUpdateCache) {
            $objCache = Isotope::getRequestCache()->saveNewConfiguration();

            // Include \Environment::base or the URL would not work on the index page
            \Controller::redirect(
                \Environment::get('base') .
                Url::addQueryString('isorc='.$objCache->id, ($this->jumpTo > 0 ? $this->jumpTo : null))
            );
        }

        return $strBuffer;
    }

    /**
     * Generate ajax
     */
    public function generateAjax()
    {
        if (!\Environment::get('isAjaxRequest')) {
            return;
        }

        if ($this->iso_searchAutocomplete && \Input::get('iso_autocomplete') == $this->id) {

            $objProducts = Product::findPublishedByCategories($this->findCategories(), array('order' => 'c.sorting'));

            if (null === $objProducts) {
                $objResponse = new JsonResponse(array());
                $objResponse->send();
            }

            $objResponse = new JsonResponse(array_values($objProducts->fetchEach($this->iso_searchAutocomplete)));
            $objResponse->send();
        }
    }

    /**
     * Initialize module data. You can override this function in a child class
     *
     * @return bool
     */
    protected function initializeFilters()
    {
        $this->iso_filterFields  = deserialize($this->iso_filterFields);
        $this->iso_sortingFields = deserialize($this->iso_sortingFields);
        $this->iso_searchFields  = deserialize($this->iso_searchFields);

        if (!$this->iso_enableLimit && !is_array($this->iso_filterFields) && !is_array($this->iso_sortingFields) && !is_array($this->iso_searchFields)) {
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
        $this->blnUpdateCache = \Input::post('FORM_SUBMIT') == 'iso_filter_' . $this->id ? true : false;

        $this->generateFilters();
        $this->generateSorting();
        $this->generateLimit();

        if (!$this->blnUpdateCache) {
            // Search does not affect request cache
            $this->generateSearch();

            $arrParams = array_filter(array_keys($_GET), function($key) {
                return (strpos($key, 'page_iso') === 0);
            });

            $this->Template->id          = $this->id;
            $this->Template->formId      = 'iso_filter_' . $this->id;
            $this->Template->action      = ampersand(Url::removeQueryString($arrParams));
            $this->Template->actionClear = ampersand(strtok(\Environment::get('request'), '?'));
            $this->Template->clearLabel  = $GLOBALS['TL_LANG']['MSC']['clearFiltersLabel'];
            $this->Template->slabel      = $GLOBALS['TL_LANG']['MSC']['submitLabel'];
        }
    }

    /**
     * Generate a search form
     */
    protected function generateSearch()
    {
        global $objPage;

        $this->Template->hasSearch       = false;
        $this->Template->hasAutocomplete = ($this->iso_searchAutocomplete) ? true : false;

        if (is_array($this->iso_searchFields) && count($this->iso_searchFields)) // Can't use empty() because its an object property (using __get)
        {
            if (\Input::get('keywords') != '' && \Input::get('keywords') != $GLOBALS['TL_LANG']['MSC']['defaultSearchText']) {

                // Redirect to search result page if one is set (see #1068)
                if (!$this->blnUpdateCache && $this->jumpTo != $objPage->id && null !== $this->objModel->getRelated('jumpTo')) {

                    /** @type \PageModel $objJumpTo */
                    $objJumpTo = $this->objModel->getRelated('jumpTo');

                    // Include \Environment::base or the URL would not work on the index page
                    \Controller::redirect(
                        \Environment::get('base') .
                        $objJumpTo->getFrontendUrl() .
                        '?' . $_SERVER['QUERY_STRING']
                    );
                }

                $arrKeywords = trimsplit(' |-', \Input::get('keywords'));
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
            $this->Template->keywords          = \Input::get('keywords');
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

        if (is_array($this->iso_filterFields) && count($this->iso_filterFields)) // Can't use empty() because its an object property (using __get)
        {
            $time          = time();
            $arrFilters    = array();
            $arrInput      = \Input::post('filter');
            $arrCategories = $this->findCategories();

            foreach ($this->iso_filterFields as $strField) {
                $arrValues = array();
                $objValues = \Database::getInstance()->execute("
                    SELECT DISTINCT p1.$strField FROM tl_iso_product p1
                    LEFT OUTER JOIN tl_iso_product p2 ON p1.pid=p2.id
                    WHERE
                        p1.language=''
                        AND p1.$strField!=''
                        " . (BE_USER_LOGGED_IN === true ? '' : "AND p1.published='1' AND (p1.start='' OR p1.start<$time) AND (p1.stop='' OR p1.stop>$time) ") . "
                        AND (
                            p1.id IN (
                                SELECT pid FROM " . \Isotope\Model\ProductCategory::getTable() . " WHERE page_id IN (" . implode(',', $arrCategories) . ")
                            )
                            OR p1.pid IN (
                                SELECT pid FROM " . \Isotope\Model\ProductCategory::getTable() . " WHERE page_id IN (" . implode(',', $arrCategories) . ")
                            )
                        )
                        " . (BE_USER_LOGGED_IN === true ? '' : " AND (p1.pid=0 OR (p2.published='1' AND (p2.start='' OR p2.start<$time) AND (p2.stop='' OR p2.stop>$time)))") . "
                        " . ($this->iso_list_where == '' ? '' : " AND " . Haste::getInstance()->call('replaceInsertTags', $this->iso_list_where))
                );

                while ($objValues->next()) {
                    $arrValues = array_merge($arrValues, deserialize($objValues->$strField, true));
                }

                if ($this->blnUpdateCache && in_array($arrInput[$strField], $arrValues)) {
                    Isotope::getRequestCache()->setFilterForModule(
                        $strField,
                        Filter::attribute($strField)->isEqualTo($arrInput[$strField]),
                        $this->id
                    );

                } elseif ($this->blnUpdateCache && $arrInput[$strField] == '') {
                    Isotope::getRequestCache()->removeFilterForModule($strField, $this->id);

                } elseif (($objFilter = Isotope::getRequestCache()->getFilterForModule($strField, $this->id)) !== null && $objFilter->valueNotIn($arrValues)) {
                    // Request cache contains wrong value, delete it!

                    $this->blnUpdateCache = true;
                    Isotope::getRequestCache()->removeFilterForModule($strField, $this->id);

                    RequestCache::deleteById(\Input::get('isorc'));

                } elseif (!$this->blnUpdateCache) {
                    // Only generate options if we do not reload anyway

                    if (empty($arrValues)) {
                        continue;
                    }

                    $arrData = $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$strField];

                    if (is_array($GLOBALS['ISO_ATTR'][$arrData['inputType']]['callback']) && !empty($GLOBALS['ISO_ATTR'][$arrData['inputType']]['callback'])) {
                        foreach ($GLOBALS['ISO_ATTR'][$arrData['inputType']]['callback'] as $callback) {
                            $objCallback = \System::importStatic($callback[0]);
                            $arrData     = $objCallback->{$callback[1]}($strField, $arrData, $this);
                        }
                    }

                    // Use the default routine to initialize options data
                    $arrWidget = \Widget::getAttributesFromDca($arrData, $strField);
                    $objFilter = Isotope::getRequestCache()->getFilterForModule($strField, $this->id);

                    if (($objAttribute = $GLOBALS['TL_DCA']['tl_iso_product']['attributes'][$strField]) !== null
                        && $objAttribute instanceof IsotopeAttributeWithOptions
                    ) {
                        $arrWidget['options'] = $objAttribute->getOptionsForProductFilter($arrValues);
                    }

                    // Must have options to apply the filter
                    if (!is_array($arrWidget['options'])) {
                        continue;
                    }

                    foreach ($arrWidget['options'] as $k => $option) {
                        if ($option['value'] == '') {
                            $arrWidget['blankOptionLabel'] = $option['label'];
                            unset($arrWidget['options'][$k]);
                            continue;

                        } elseif (!in_array($option['value'], $arrValues) || $option['value'] == '-') {
                            // @deprecated IsotopeAttributeWithOptions::getOptionsForProductFilter already checks this

                            unset($arrWidget['options'][$k]);
                            continue;
                        }

                        $arrWidget['options'][$k]['default'] = ((null !== $objFilter && $objFilter->valueEquals($option['value'])) ? '1' : '');
                    }

                    // Hide fields with just one option (if enabled)
                    if ($this->iso_filterHideSingle && count($arrWidget['options']) < 2) {
                        continue;
                    }

                    $arrFilters[$strField] = $arrWidget;
                }
            }

            // !HOOK: alter the filters
            if (isset($GLOBALS['ISO_HOOKS']['generateFilters']) && is_array($GLOBALS['ISO_HOOKS']['generateFilters'])) {
                foreach ($GLOBALS['ISO_HOOKS']['generateFilters'] as $callback) {
                    $objCallback = \System::importStatic($callback[0]);
                    $arrFilters  = $objCallback->$callback[1]($arrFilters);
                }
            }

            if (!empty($arrFilters)) {
                $this->Template->hasFilters    = true;
                $this->Template->filterOptions = $arrFilters;
            }
        }
    }

    /**
     * Generate a sorting form
     */
    protected function generateSorting()
    {
        $this->Template->hasSorting = false;

        if (is_array($this->iso_sortingFields) && count($this->iso_sortingFields)) {
            // Can't use empty() because its an object property (using __get)

            $arrOptions = array();

            // Cache new request value
            // @todo should support multiple sorting fields
            list($sortingField, $sortingDirection) = explode(':', \Input::post('sorting'));

            if ($this->blnUpdateCache && in_array($sortingField, $this->iso_sortingFields)) {
                Isotope::getRequestCache()->setSortingForModule(
                    $sortingField,
                    ($sortingDirection == 'DESC' ? Sort::descending() : Sort::ascending()),
                    $this->id
                );

            } elseif (array_diff(array_keys(Isotope::getRequestCache()->getSortingsForModules(array($this->id))), $this->iso_sortingFields)) {
                // Request cache contains wrong value, delete it!

                $this->blnUpdateCache = true;
                Isotope::getRequestCache()->unsetSortingsForModule($this->id);

                RequestCache::deleteById(\Input::get('isorc'));

            } elseif (!$this->blnUpdateCache) {
                // No need to generate options if we reload anyway

                $first = Isotope::getRequestCache()->getFirstSortingFieldForModule($this->id);

                foreach ($this->iso_sortingFields as $field) {
                    list($asc, $desc) = $this->getSortingLabels($field);
                    $objSorting = $first == $field ? Isotope::getRequestCache()->getSortingForModule($field, $this->id) : null;

                    $arrOptions[] = array
                    (
                        'label'   => (Format::dcaLabel('tl_iso_product', $field) . ', ' . $asc),
                        'value'   => $field . ':ASC',
                        'default' => ((null !== $objSorting && $objSorting->isAscending()) ? '1' : ''),
                    );

                    $arrOptions[] = array
                    (
                        'label'   => (Format::dcaLabel('tl_iso_product', $field) . ', ' . $desc),
                        'value'   => $field . ':DESC',
                        'default' => ((null !== $objSorting && $objSorting->isDescending()) ? '1' : ''),
                    );
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
            $arrOptions = array();
            $arrLimit   = array_map('intval', trimsplit(',', $this->iso_perPage));
            $objLimit   = Isotope::getRequestCache()->getFirstLimitForModules(array($this->id));
            $arrLimit   = array_unique($arrLimit);
            sort($arrLimit);

            if ($this->blnUpdateCache && in_array(\Input::post('limit'), $arrLimit)) {
                // Cache new request value

                Isotope::getRequestCache()->setLimitForModule(Limit::to(\Input::post('limit')), $this->id);

            } elseif ($objLimit->notIn($arrLimit)) {
                // Request cache contains wrong value, delete it!

                $this->blnUpdateCache = true;
                Isotope::getRequestCache()->setLimitForModule(Limit::to($arrLimit[0]), $this->id);

                RequestCache::deleteById(\Input::get('isorc'));

            } elseif (!$this->blnUpdateCache) {
                // No need to generate options if we reload anyway

                foreach ($arrLimit as $limit) {
                    $arrOptions[] = array
                    (
                        'label'   => $limit,
                        'value'   => $limit,
                        'default' => ($objLimit->equals($limit) ? '1' : ''),
                    );
                }

                $this->Template->hasLimit     = true;
                $this->Template->limitLabel   = $GLOBALS['TL_LANG']['MSC']['perPageLabel'];
                $this->Template->limitOptions = $arrOptions;
            }
        }
    }

    /**
     * Get the sorting labels (asc/desc) for an attribute
     *
     * @param string
     *
     * @return array
     */
    protected function getSortingLabels($field)
    {
        $arrData = $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$field];

        switch ($arrData['eval']['rgxp']) {
            case 'price':
            case 'digit':
                return array($GLOBALS['TL_LANG']['MSC']['low_to_high'], $GLOBALS['TL_LANG']['MSC']['high_to_low']);

            case 'date':
            case 'time':
            case 'datim':
                return array($GLOBALS['TL_LANG']['MSC']['old_to_new'], $GLOBALS['TL_LANG']['MSC']['new_to_old']);
        }

        return array($GLOBALS['TL_LANG']['MSC']['a_to_z'], $GLOBALS['TL_LANG']['MSC']['z_to_a']);
    }
}
