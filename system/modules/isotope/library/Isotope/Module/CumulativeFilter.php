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

use Haste\Generator\RowClass;
use Haste\Input\Input;
use Haste\Util\Url;
use Isotope\Interfaces\IsotopeAttributeWithOptions;
use Isotope\Interfaces\IsotopeFilterModule;
use Isotope\Isotope;
use Isotope\RequestCache\Filter;
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
     * Constructor.
     *
     * @param object $objModule
     * @param string $strColumn
     */
    public function __construct($objModule, $strColumn = 'main')
    {
        parent::__construct($objModule, $strColumn);

        $this->iso_cumulativeFields = deserialize($this->iso_cumulativeFields);
        $fields                     = array();

        if (is_array($this->iso_cumulativeFields)) {
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
    }

    /**
     * Display a wildcard in the back end
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
            /** @var \BackendTemplate|object $objTemplate */
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ISOTOPE ECOMMERCE: CUMULATIVE FILTER ###';
            $objTemplate->title    = $this->headline;
            $objTemplate->id       = $this->id;
            $objTemplate->link     = $this->name;
            $objTemplate->href     = 'contao/main.php?do=themes&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
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
        $arrFilter = explode(';', base64_decode(\Input::get('cumulativefilter', true)), 4);

        if ($arrFilter[0] == $this->id && isset($this->iso_cumulativeFields[$arrFilter[2]])) {
            $this->saveFilter($arrFilter[1], $arrFilter[2], $arrFilter[3]);
            return;
        }

        $this->generateFilter();

        $this->Template->linkClearAll  = ampersand(preg_replace('/\?.*/', '', \Environment::get('request')));
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
        if (empty($arrItems) || ($this->iso_filterHideSingle && count($arrItems) < 2)) {
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

        return array(
            'label'    => $label,
            'subitems' => $objTemplate->parse(),
            'isActive' => $isActive,
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
            }

            $arrItems[] = $this->generateOptionItem(
                $attribute,
                $option['label'],
                $value,
                $count,
                $activeOption
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
    protected function generateOptionItem($attribute, $label, $value, $matchCount, $isActive)
    {
        $value = base64_encode($this->id . ';' . ($isActive ? 'del' : 'add') . ';' . $attribute . ';' . $value);
        $href  = Url::addQueryString('cumulativefilter=' . $value);
        $link  = $label;

        if (false !== $matchCount) {
            $link = sprintf('%s <i class="result_count">(%d)</i>', $label, $matchCount);
        }

        return array(
            'href'  => $href,
            'class' => ($isActive ? 'active' : ''),
            'title' => specialchars($label),
            'link'  => $link,
            'label' => $label,
            'count' => $matchCount,
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
        $usedValues = $this->getUsedValuesForAttribute($attribute, $this->findCategories(), $this->iso_list_where);

        if (empty($usedValues)) {
            return array();
        }

        // Use the default routine to initialize options data
        $arrWidget = \Widget::getAttributesFromDca(
            $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$attribute],
            $attribute
        );

        $label     = $arrWidget['label'];
        $options   = $arrWidget['options'];

        if (($objAttribute = $GLOBALS['TL_DCA']['tl_iso_product']['attributes'][$attribute]) !== null
            && $objAttribute instanceof IsotopeAttributeWithOptions
        ) {
            $options = $objAttribute->getOptionsForProductFilter($usedValues);

        } elseif (is_array($options)) {
            $options = array_filter(
                $options,
                function ($option) use ($usedValues) {
                    return in_array($option['value'], $usedValues);
                }
            );
        } else {
            $options = array();
        }

        return $options;
    }

    /**
     * @param string $action
     * @param string $attribute
     * @param string $value
     */
    protected function saveFilter($action, $attribute, $value)
    {
        // Unique filter key is necessary to unset the filter
        $strFilterKey = $this->generateFilterKey($attribute, $value);
        $filterConfig = $this->iso_cumulativeFields[$attribute];

        if ($action == 'add') {
            $filter   = Filter::attribute($attribute)->isEqualTo($value);

            if (!$this->isMultiple($attribute)) {
                $group = 'cumulative_' . $attribute;
                $filter->groupBy($group);

                if ($filterConfig['queryType'] == 'and') {
                    /** @var Filter $oldFilter */
                    foreach (Isotope::getRequestCache()->getFiltersForModules(array($this->id)) as $oldFilter) {
                        if ($oldFilter->getGroup() == $group) {
                            Isotope::getRequestCache()->removeFilterForModule(
                                $this->generateFilterKey($oldFilter['attribute'], $oldFilter['value']),
                                $this->id
                            );
                        }
                    }
                }
            }

            Isotope::getRequestCache()->setFilterForModule(
                $strFilterKey,
                $filter,
                $this->id
            );
        } else {
            Isotope::getRequestCache()->removeFilterForModule($strFilterKey, $this->id);
        }

        $objCache = Isotope::getRequestCache()->saveNewConfiguration();

        // Include \Environment::base or the URL would not work on the index page
        \Controller::redirect(
            \Environment::get('base') .
            Url::addQueryString(
                'isorc='.$objCache->id,
                Url::removeQueryString(array('cumulativefilter'), ($this->jumpTo ?: null))
            )
        );
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

    /**
     * Returns true if the attribute is multiple choice.
     *
     * @param string $attribute
     *
     * @return bool
     */
    private function isMultiple($attribute)
    {
        return (bool) $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$attribute]['eval']['multiple'];
    }
}
