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
            $item = $this->generateAttribute($strField, $config, $blnShowClear);

            if (null !== $item) {
                $arrFilters[$strField] = $item;
            }
        }

        $this->Template->filters   = $arrFilters;
        $this->Template->showClear = $blnShowClear;
    }

    /**
     * @param string $attribute
     * @param array  $config
     * @param bool   $showClear
     *
     * @return array|null
     */
    protected function generateAttribute($attribute, array $config, &$showClear)
    {
        $arrCategories = $this->findCategories();
        $arrValues     = $this->getUsedValuesForAttribute($attribute, $arrCategories, $this->iso_list_where);

        if (empty($arrValues)) {
            return null;
        }

        $blnTrail  = false;
        $arrData   = $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$attribute];

        // Use the default routine to initialize options data
        $arrWidget = \Widget::getAttributesFromDca($arrData, $attribute);

        if (($objAttribute = $GLOBALS['TL_DCA']['tl_iso_product']['attributes'][$attribute]) !== null
            && $objAttribute instanceof IsotopeAttributeWithOptions
        ) {
            $arrWidget['options'] = $objAttribute->getOptionsForProductFilter($arrValues);
        }

        // Must have options to apply the filter
        if (!is_array($arrWidget['options'])) {
            return null;
        }

        $arrItems = $this->generateOptions($attribute, $arrWidget['options'], $arrValues, $blnTrail);

        // Hide fields with just one option (if enabled)
        if (empty($arrItems) || ($this->iso_filterHideSingle && count($arrItems) < 2)) {
            return null;
        }

        $objClass = RowClass::withKey('class')->addFirstLast();

        if ($blnTrail) {
            $objClass->addCustom('sibling');
            $showClear = true;
        }

        $objClass->applyTo($arrItems);

        $objTemplate = new Template($this->navigationTpl);

        $objTemplate->level = 'level_2';
        $objTemplate->items = $arrItems;

        return array(
            'label'    => $arrWidget['label'],
            'subitems' => $objTemplate->parse(),
            'isActive' => $blnTrail,
        );
    }

    /**
     * @param string $attribute
     * @param array  $options
     * @param array  $available
     * @param bool   $isTrail
     *
     * @return array
     */
    protected function generateOptions($attribute, array $options, array $available, &$isTrail)
    {
        $arrItems  = array();

        foreach ($options as $option) {
            $varValue = $option['value'];

            // skip zero values (includeBlankOption)
            // @deprecated drop "-" when we only have the database table as options source
            if (!in_array($option['value'], $available) || $varValue === '' || $varValue === '-') {
                continue;
            }

            $isActive     = false;
            $strFilterKey = $this->generateFilterKey($attribute, $varValue);

            if (null !== Isotope::getRequestCache()->getFilterForModule($strFilterKey, $this->id)) {
                $isActive = true;
                $isTrail  = true;
            }

            $arrItems[] = $this->generateOptionItem($attribute, $option['label'], $varValue, $isActive);
        }

        return $arrItems;
    }

    /**
     * @param string $attribute
     * @param string $label
     * @param string $value
     * @param bool   $isActive
     *
     * @return array
     */
    protected function generateOptionItem($attribute, $label, $value, $isActive)
    {
        $count = 0;
        $value = base64_encode($this->id . ';' . ($isActive ? 'del' : 'add') . ';' . $attribute . ';' . $value);
        $href  = Url::addQueryString('cumulativefilter=' . $value);

        return array(
            'href'  => $href,
            'class' => ($isActive ? 'active' : ''),
            'title' => specialchars($label),
            'link'  => sprintf('%s (%s)', $label, $count),
            'label' => $label,
            'count' => $count,
        );
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
            $multiple = (bool) $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$attribute]['eval']['multiple'];

            if (!$multiple) {
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
}
