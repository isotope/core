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
use Haste\Util\Url;
use Isotope\Interfaces\IsotopeAttributeWithOptions;
use Isotope\Interfaces\IsotopeFilterModule;
use Isotope\Isotope;
use Isotope\RequestCache\Filter;


class CumulativeFilter extends AbstractProductFilter implements IsotopeFilterModule
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_iso_cumulativefilter';


    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ISOTOPE ECOMMERCE: CUMULATIVE FILTER ###';

            $objTemplate->title = $this->headline;
            $objTemplate->id    = $this->id;
            $objTemplate->link  = $this->name;
            $objTemplate->href  = 'contao/main.php?do=themes&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        // Hide product list in reader mode if the respective setting is enabled
        if ($this->iso_hide_list && \Haste\Input\Input::getAutoItem('product', false, true) != '') {
            return '';
        }

        // Remove setting to prevent override of the module template
        $this->iso_filterTpl = '';
        $this->navigationTpl = $this->navigationTpl ?: 'nav_default';

        if (empty($this->iso_filterFields)) {
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

        if ($arrFilter[0] == $this->id && in_array($arrFilter[2], $this->iso_filterFields)) {

            // Unique filter key is necessary to unset the filter
            $strFilterKey = $arrFilter[2] . '=' . $arrFilter[3];

            if ($arrFilter[1] == 'add') {
                $filter   = Filter::attribute($arrFilter[2])->isEqualTo($arrFilter[3]);
                $multiple = (bool) $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$arrFilter[2]]['eval']['multiple'];

                if (!$multiple) {
                    $filter->groupBy('cumulative_' . $arrFilter[2]);
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

        } else {
            $this->generateFilter();

            $this->Template->linkClearAll  = ampersand(preg_replace('/\?.*/', '', \Environment::get('request')));
            $this->Template->labelClearAll = $GLOBALS['TL_LANG']['MSC']['clearFiltersLabel'];
        }
    }


    /**
     * Generates the filter
     */
    protected function generateFilter()
    {
        $blnShowClear  = false;
        $arrFilters    = array();
        $arrCategories = $this->findCategories();

        foreach ($this->iso_filterFields as $strField) {
            $arrValues = $this->getUsedValuesForAttribute($strField, $arrCategories, $this->iso_list_where);

            if (empty($arrValues)) {
                continue;
            }

            $blnTrail  = false;
            $arrItems  = array();
            $arrData   = $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$strField];

            // Use the default routine to initialize options data
            $arrWidget = \Widget::getAttributesFromDca($arrData, $strField);

            if (($objAttribute = $GLOBALS['TL_DCA']['tl_iso_product']['attributes'][$strField]) !== null
                && $objAttribute instanceof IsotopeAttributeWithOptions
            ) {
                $arrWidget['options'] = $objAttribute->getOptionsForProductFilter(array_keys($arrValues));
            }

            // Must have options to apply the filter
            if (!is_array($arrWidget['options'])) {
                continue;
            }

            foreach ($arrWidget['options'] as $option) {
                $varValue = $option['value'];

                // skip zero values (includeBlankOption)
                // @deprecated drop "-" when we only have the database table as options source
                if (!isset($arrValues[$option['value']]) || $varValue === '' || $varValue === '-') {
                    continue;
                }

                $strFilterKey = $strField . '=' . $varValue;
                $blnActive    = (Isotope::getRequestCache()->getFilterForModule($strFilterKey, $this->id) !== null);
                $blnTrail     = $blnActive ? true : $blnTrail;

                $arrItems[] = array(
                    'href'  => \Haste\Util\Url::addQueryString('cumulativefilter=' . base64_encode($this->id . ';' . ($blnActive ? 'del' : 'add') . ';' . $strField . ';' . $varValue)),
                    'class' => ($blnActive ? 'active' : ''),
                    'title' => specialchars($option['label']),
                    'link'  => sprintf('%s (%s)', $option['label'], $arrValues[$option['value']]),
                    'label' => $option['label'],
                    'count' => $arrValues[$option['value']],
                );
            }

            // Hide fields with just one option (if enabled)
            if (empty($arrItems) || ($this->iso_iso_filterHideSingle && count($arrItems) < 2)) {
                continue;
            }

            $objClass = RowClass::withKey('class')->addFirstLast();

            if ($blnTrail) {
                $objClass->addCustom('sibling');
            }

            $objClass->applyTo($arrItems);

            $objTemplate = new \Isotope\Template($this->navigationTpl);

            $objTemplate->level = 'level_2';
            $objTemplate->items = $arrItems;

            $arrFilters[$strField] = array(
                'label'    => $arrWidget['label'],
                'subitems' => $objTemplate->parse(),
                'isActive' => $blnTrail,
            );

            $blnShowClear = $blnTrail ? true : $blnShowClear;
        }

        $this->Template->filters   = $arrFilters;
        $this->Template->showClear = $blnShowClear;
    }
}
