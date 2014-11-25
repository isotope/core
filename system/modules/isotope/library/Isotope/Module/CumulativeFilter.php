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
use Isotope\Interfaces\IsotopeFilterModule;
use Isotope\Isotope;
use Isotope\RequestCache\Filter;


/**
 * Class ModuleIsotopeCumulativeFilter
 *
 * Provides a cumulative filter module.
 * @copyright  Isotope eCommerce Workgroup 2009-2011
 * @author     Yanick Witschi <yanick.witschi@terminal42.ch>
 */
class CumulativeFilter extends Module implements IsotopeFilterModule
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

        // Remove setting to prevent override of the module template
        $this->iso_filterTpl = '';
        $this->navigationTpl = $this->navigationTpl ? $this->navigationTpl : 'nav_default';
        $this->iso_filterFields  = deserialize($this->iso_filterFields);

        if (!is_array($this->iso_filterFields) || count($this->iso_filterFields) == 0) { // Can't use empty() because its an object property (using __get)
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
                Isotope::getRequestCache()->setFilterForModule(
                    $strFilterKey,
                    Filter::attribute($arrFilter[2])->isEqualTo($arrFilter[3]),
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
                    Url::removeQueryString(array('cumulativefilter'))
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
        $blnShowClear = false;
        $arrFilters   = array();

        foreach ($this->iso_filterFields as $strField) {
            $blnTrail  = false;
            $arrItems  = array();
            $arrWidget = \Widget::getAttributesFromDca($GLOBALS['TL_DCA']['tl_iso_product']['fields'][$strField], $strField); // Use the default routine to initialize options data

            foreach ($arrWidget['options'] as $option) {
                $varValue = $option['value'];

                // skip zero values (includeBlankOption)
                // @deprecated drop "-" when we only have the database table as options source
                if ($varValue === '' || $varValue === '-') {
                    continue;
                }

                $strFilterKey = $strField . '=' . $varValue;
                $blnActive    = (Isotope::getRequestCache()->getFilterForModule($strFilterKey, $this->id) !== null);
                $blnTrail     = $blnActive ? true : $blnTrail;

                $arrItems[] = array
                (
                    'href'  => \Haste\Util\Url::addQueryString('cumulativefilter=' . base64_encode($this->id . ';' . ($blnActive ? 'del' : 'add') . ';' . $strField . ';' . $varValue)),
                    'class' => ($blnActive ? 'active' : ''),
                    'title' => specialchars($option['label']),
                    'link'  => $option['label'],
                );
            }

            if (!empty($arrItems) || ($this->iso_iso_filterHideSingle && count($arrItems) < 2)) {
                $objClass = RowClass::withKey('class')->addFirstLast();

                if ($blnTrail) {
                    $objClass->addCustom('sibling');
                }

                $objClass->applyTo($arrItems);

                $objTemplate = new \Isotope\Template($this->navigationTpl);

                $objTemplate->level = 'level_2';
                $objTemplate->items = $arrItems;

                $arrFilters[$strField] = array
                (
                    'label'    => $arrWidget['label'],
                    'subitems' => $objTemplate->parse(),
                    'isActive' => $blnTrail,
                );

                $blnShowClear = $blnTrail ? true : $blnShowClear;
            }
        }

        $this->Template->filters   = $arrFilters;
        $this->Template->showClear = $blnShowClear;
    }
}
