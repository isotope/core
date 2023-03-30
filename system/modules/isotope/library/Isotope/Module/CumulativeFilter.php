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
use Contao\Environment;
use Contao\StringUtil;
use Contao\Widget;
use Haste\Generator\RowClass;
use Haste\Input\Input;
use Haste\Util\Url;
use Isotope\Interfaces\IsotopeAttributeWithOptions;
use Isotope\Interfaces\IsotopeFilterModule;
use Isotope\Isotope;
use Isotope\Model\Attribute;
use Isotope\Model\Product;
use Isotope\RequestCache\CsvFilter;
use Isotope\RequestCache\Filter;
use Isotope\RequestCache\FilterQueryBuilder;
use Isotope\RequestCache\Sort;
use Isotope\Template;

/**
 * @property array $iso_cumulativeFields
 */
class CumulativeFilter extends AbstractProductFilter implements IsotopeFilterModule
{
    const QUERY_AND = 'and';
    const QUERY_OR  = 'or';
    const COUNT_ALL = 'all';
    const COUNT_NEW = 'new';

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_iso_cumulativefilter';

    /**
     * @var Filter[]
     */
    protected $activeFilters;

    /**
     * @var bool
     */
    private $canShowMatches;

    /**
     * Constructor.
     *
     * @param object $objModule
     * @param string $strColumn
     */
    public function __construct($objModule, $strColumn = 'main')
    {
        parent::__construct($objModule, $strColumn);

        $this->iso_cumulativeFields = StringUtil::deserialize($this->iso_cumulativeFields);
        $fields                     = array();

        if (\is_array($this->iso_cumulativeFields)) {
            foreach ($this->iso_cumulativeFields as $k => $v) {
                $attribute = $v['attribute'];
                unset($v['attribute']);

                $fields[$attribute] = $v;
            }
        }

        $this->iso_cumulativeFields = $fields;

        // Remove setting to prevent override of the module template
        $this->iso_filterTpl = '';
        $this->navigationTpl = $this->navigationTpl ?: 'nav_default';

        $this->activeFilters = Isotope::getRequestCache()->getFiltersForModules(array($this->id));

        // We cannot show matches if some of our filters are not applicable in SQL
        $dynamicFields = array_intersect(
            array_keys($this->iso_cumulativeFields),
            Attribute::getDynamicAttributeFields()
        );

        $this->canShowMatches = empty($dynamicFields);
    }

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

        // Hide product list in reader mode if the respective setting is enabled
        if ($this->iso_hide_list && Input::getAutoItem('product', false, true) != '') {
            return '';
        }

        if (empty($this->iso_cumulativeFields)) {
            return '';
        }

        return parent::generate();
    }

    /**
     * Compile the module
     */
    protected function compile()
    {
        $arrFilter = explode(';', base64_decode(Input::get('cumulativefilter', true)), 4);

        if ($arrFilter[0] == $this->id && isset($this->iso_cumulativeFields[$arrFilter[2]])) {
            $this->saveFilter($arrFilter[1], $arrFilter[2], $arrFilter[3]);
            return;
        }

        $this->generateFilter();

        $this->Template->linkClearAll  = ampersand(preg_replace('/\?.*/', '', Environment::get('request')));
        $this->Template->labelClearAll = $GLOBALS['TL_LANG']['MSC']['clearFiltersLabel'];
    }

    /**
     * Generates the filter
     */
    protected function generateFilter()
    {
        $blnShowClear  = false;
        $arrFilters    = array();

        foreach ($this->iso_cumulativeFields as $strField => $config) {
            $item = $this->generateAttribute($strField, $config['queryType'], $config['matchCount'], $blnShowClear);

            if (null !== $item) {
                $arrFilters[$strField] = $item;
            }
        }

        RowClass::withKey('class')->addFirstLast()->applyTo($arrFilters);

        $this->Template->filters   = $arrFilters;
        $this->Template->showClear = $blnShowClear;
    }

    /**
     * @param string $attribute
     * @param string $queryType
     * @param string $countType
     * @param bool   $showClear
     *
     * @return array|null
     */
    protected function generateAttribute($attribute, $queryType, $countType, &$showClear)
    {
        $isActive  = false;
        $label     = $attribute; // Will be updated by getOptionsForAttribute()
        $options   = $this->getOptionsForAttribute($attribute, $label);

        // Must have options to apply the filter
        if (empty($options)) {
            return null;
        }

        $arrItems = $this->generateOptions($attribute, $options, $queryType, $countType, $isActive);

        // Hide fields with just one option (if enabled)
        if (empty($arrItems) || ($this->iso_filterHideSingle && \count($arrItems) < 2)) {
            return null;
        }

        $objClass = RowClass::withKey('class')->addFirstLast();

        if ($isActive) {
            $objClass->addCustom('sibling');
            $showClear = true;
        }

        $objClass->applyTo($arrItems);

        /** @var Template|object $objTemplate */
        $objTemplate = new Template($this->navigationTpl);

        $objTemplate->level = 'level_2';
        $objTemplate->items = $arrItems;

        $class = $attribute . ' query_' . strtolower($queryType) . ' count_' . $countType;

        if ($this->isMultiple($attribute)) {
            $class .= ' multiple';
        }

        if ($isActive) {
            $class .= ' trail';
        }

        return array(
            'label'    => $label,
            'subitems' => $objTemplate->parse(),
            'isActive' => $isActive,
            'class'    => $class
        );
    }

    /**
     * @param string $attribute
     * @param array  $options
     * @param string $queryType
     * @param string $countType
     * @param bool   $filterActive
     *
     * @return array
     */
    protected function generateOptions($attribute, array $options, $queryType, $countType, &$filterActive)
    {
        $arrItems  = array();

        foreach ($options as $option) {
            $value = $option['value'];

            // skip zero values (includeBlankOption)
            // @deprecated drop "-" when we only have the database table as options source
            if ($value === '' || $value === '-') {
                continue;
            }

            $count        = false;
            $activeOption = false;
            $strFilterKey = $this->generateFilterKey($attribute, $value);

            if (null !== Isotope::getRequestCache()->getFilterForModule($strFilterKey, $this->id)) {
                $activeOption = true;
                $filterActive = true;

            } elseif ($this->canShowMatches && self::COUNT_NEW === $countType) {
                $count = $this->countNewMatches(
                    $attribute,
                    $value,
                    $this->getExistingFiltersForQueryType($attribute, $queryType)
                );
            } elseif ($this->canShowMatches && self::COUNT_ALL === $countType) {
                $count = $this->countAllMatches(
                    $attribute,
                    $value,
                    $this->getExistingFiltersForQueryType($attribute, $queryType)
                );
            }

            $arrItems[] = $this->generateOptionItem(
                $attribute,
                $option['label'],
                $value,
                $count,
                $activeOption,
                $option
            );
        }

        return $arrItems;
    }

    /**
     * @param string   $attribute
     * @param string   $label
     * @param string   $value
     * @param int|bool $matchCount
     * @param bool     $isActive
     *
     * @return array
     */
    protected function generateOptionItem($attribute, $label, $value, $matchCount, $isActive, array $option = [])
    {
        $value = base64_encode($this->id . ';' . ($isActive ? 'del' : 'add') . ';' . $attribute . ';' . $value);
        $link  = $label;
        $class = $option['cssClass'] ?? '';

        $href  = Url::addQueryString(
            'cumulativefilter=' . $value,
            Url::removeQueryStringCallback(function ($value, $key) {
                return strpos($key, 'page_iso') !== 0;
            })
        );

        if (false !== $matchCount) {
            $link = sprintf('%s <i class="result_count">(%d)</i>', $label, $matchCount);
        }

        return array(
            'href'  => $href,
            'class' => trim($class.($isActive ? ' active' : '') . ($matchCount === 0 ? ' empty' : '')),
            'title' => StringUtil::specialchars($label),
            'pageTitle' => '',
            'link'  => $link,
            'label' => $label,
            'count' => $matchCount,
            'nofollow' => true,
            'isActive' => $isActive,

            // Default keys necessary for nav_default template
            'accesskey' => '',
            'tabindex' => '',
            'target' => '',
        );
    }

    /**
     * Gets the used and available options for given attribute
     *
     * @param string $attribute The attribute name
     * @param string $label     Set to the label of the attribute
     *
     * @return array
     */
    protected function getOptionsForAttribute($attribute, &$label)
    {
        $usedValues = $this->getUsedValuesForAttribute(
            $attribute,
            $this->findCategories(),
            $this->iso_newFilter,
            $this->iso_list_where
        );

        if (empty($usedValues)) {
            return array();
        }

        // Use the default routine to initialize options data
        $arrWidget = Widget::getAttributesFromDca(
            $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$attribute],
            $attribute
        );

        $label   = $arrWidget['label'];
        $options = $arrWidget['options'];

        if (($objAttribute = $GLOBALS['TL_DCA']['tl_iso_product']['attributes'][$attribute]) !== null
            && $objAttribute instanceof IsotopeAttributeWithOptions
        ) {
            $options = $objAttribute->getOptionsForProductFilter($usedValues);

        } elseif (\is_array($options)) {
            $options = array_filter(
                $options,
                function ($option) use ($usedValues) {
                    return \in_array($option['value'], $usedValues);
                }
            );
        } else {
            $options = array();
        }

        return $options;
    }

    private function countNewMatches($attribute, $value, array $filters)
    {
        $old = $this->countCurrentMatches();
        $new = $this->countProductsForFilter(
            $this->addFilter($filters, $attribute, $value)
        );

        if (false === $old) {
            return $new;
        }

        return $new - $old;
    }

    private function countAllMatches($attribute, $value, array $filters)
    {
        return $this->countProductsForFilter(
            $this->addFilter($filters, $attribute, $value)
        );
    }

    private function getExistingFiltersForQueryType($attribute, $queryType)
    {
        $filters = $this->activeFilters;

        if (self::QUERY_AND === $queryType && !$this->isMultiple($attribute)) {
            $filters = array_filter(
                $filters,
                function ($filter) use ($attribute) {
                    return $filter['attribute'] != $attribute;
                }
            );
        }

        return $filters;
    }

    private function countCurrentMatches()
    {
        static $matches;

        if (null === $matches) {
            $matches = empty($this->activeFilters) ? false : $this->countProductsForFilter($this->activeFilters);
        }

        return $matches;
    }

    private function countProductsForFilter(array $filters)
    {
        $arrColumns    = array();
        $arrCategories = $this->findCategories();
        $queryBuilder  = new FilterQueryBuilder($filters);

        $arrColumns[]  = "c.page_id IN (" . implode(',', $arrCategories) . ")";

        // Apply new/old product filter
        if ($this->iso_newFilter == self::FILTER_NEW) {
            $arrColumns[] = Product::getTable() . ".dateAdded>=" . Isotope::getConfig()->getNewProductLimit();
        } elseif ($this->iso_newFilter == self::FILTER_OLD) {
            $arrColumns[] = Product::getTable() . ".dateAdded<" . Isotope::getConfig()->getNewProductLimit();
        }

        if ($this->iso_list_where != '') {
            $arrColumns[] = $this->iso_list_where;
        }

        if ($queryBuilder->hasSqlCondition()) {
            $arrColumns[] = $queryBuilder->getSqlWhere();
        }

        return Product::countPublishedBy(
            $arrColumns,
            $queryBuilder->getSqlValues(),
            ['group' => 'tl_iso_product.id']
        );
    }

    /**
     * @param string $action
     * @param string $attribute
     * @param string $value
     */
    private function saveFilter($action, $attribute, $value)
    {
        if ('add' === $action) {
            Isotope::getRequestCache()->setFiltersForModule(
                $this->addFilter($this->activeFilters, $attribute, $value),
                $this->id
            );

            if ('' === Isotope::getRequestCache()->getFirstSortingFieldForModule($this->id)) {
                Isotope::getRequestCache()->setSortingForModule(
                    $this->iso_listingSortField,
                    'DESC' === $this->iso_listingSortDirection ? Sort::descending() : Sort::ascending(),
                    $this->id
                );
            }
        } else {
            Isotope::getRequestCache()->removeFilterForModule(
                $this->generateFilterKey($attribute, $value),
                $this->id
            );

            Isotope::getRequestCache()->removeSortingForModule(
                $this->iso_listingSortField,
                $this->id
            );
        }

        $objCache = Isotope::getRequestCache()->saveNewConfiguration();

        // Include Environment::base or the URL would not work on the index page
        Controller::redirect(
            Environment::get('base') .
            Url::addQueryString(
                'isorc='.$objCache->id,
                Url::removeQueryStringCallback(
                    static function ($value, $key) {
                        return 'cumulativefilter' !== $key && !str_starts_with($key, 'page_iso');
                    },
                    ($this->jumpTo ?: null)
                )
            )
        );
    }

    /**
     * @param Filter[] $filters
     * @param string   $attribute
     * @param string   $value
     *
     * @return Filter[]
     */
    private function addFilter(array $filters, $attribute, $value)
    {
        if ($this->isCsv($attribute)) {
            $filter = CsvFilter::attribute($attribute)->contains($value);
        } else {
            $filter = Filter::attribute($attribute)->isEqualTo($value);
        }

        if (!$this->isMultiple($attribute) || self::QUERY_OR === $this->iso_cumulativeFields[$attribute]['queryType']) {
            $group = 'cumulative_' . $attribute;
            $filter->groupBy($group);

            if (self::QUERY_AND === $this->iso_cumulativeFields[$attribute]['queryType']) {
                foreach ($filters as $k => $oldFilter) {
                    if ($oldFilter->getGroup() == $group) {
                        unset($filters[$k]);
                    }
                }
            }
        }

        $filters[$this->generateFilterKey($attribute, $value)] = $filter;

        return $filters;
    }

    /**
     * Generates a filter key for the field and value.
     *
     * @param string $field
     * @param string $value
     *
     * @return string
     */
    private function generateFilterKey($field, $value)
    {
        return $field . '=' . $value;
    }
}
