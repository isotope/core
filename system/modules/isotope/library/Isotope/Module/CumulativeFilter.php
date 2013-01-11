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


/**
 * Class ModuleIsotopeCumulativeFilter
 *
 * Provides a cumulative filter module.
 * @copyright  Isotope eCommerce Workgroup 2009-2011
 * @author     Yanick Witschi <yanick.witschi@terminal42.ch>
 */
class CumulativeFilter extends Module
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'iso_filter_cumulative';


    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE')
        {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ISOTOPE ECOMMERCE: CUMULATIVE FILTER ###';

            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        return parent::generate();
    }


    /**
     * Override initializeFilters() to prevent module from not being shown in front end if there are no filter fields
     * @see \Isotope\Module\ProductFilter::initializeFilters()
     * @return boolean
     */
    protected function initializeFilters()
    {
        $this->iso_filterFields = deserialize($this->iso_filterFields, true);

        if (!empty($this->iso_filterFields))
        {
            return true;
        }

        if ($this->iso_filterTpl)
        {
            $this->strTemplate = $this->iso_filterTpl;
        }

        return false;
    }


    /**
     * Compile the module
     */
    protected function compile()
    {
        $this->blnCacheRequest =	(\Input::get('cfilter') &&
                                    \Input::get('attr') &&
                                    \Input::get('v') &&
                                    \Input::get('mod') == $this->id) ? true : false;

        $this->generateFilter();

        $this->Template->linkClearAll	= ampersand(preg_replace('/\?.*/', '', Environment::get('request')));
        $this->Template->labelClearAll	= $GLOBALS['TL_LANG']['MSC']['clearFiltersLabel'];
    }


    /**
     * Generates the filter
     */
    protected function generateFilter()
    {
        $arrFilters = array();

        // get values
        $strMode		= \Input::get('cfilter');
        $strField		= \Input::get('attr');
        $intValue		= \Input::get('v');
        $strFilterKey	= $strField . '::' . $intValue;

        // set filter values
        if ($this->blnCacheRequest)
        {
            if ($strMode == 'add')
            {
                $GLOBALS['ISO_FILTERS'][$this->id][$strFilterKey] = array
                (
                    'operator'		=> '==',
                    'attribute'		=> $strField,
                    'value'			=> $intValue
                );
            }
            else
            {
                unset($GLOBALS['ISO_FILTERS'][$this->id][$strFilterKey]);
            }

            // unset GET params because the rest is done by Isotope\Module\ProductFilter::generate()
            \Input::setGet('mod', null);
            \Input::setGet('cfilter', null);
            \Input::setGet('attr', null);
            \Input::setGet('v', null);
        }

        // build filter
        foreach ($this->iso_filterFields as $strField)
        {
            $arrData = $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$strField];

            // Use the default routine to initialize options data
            $arrWidget = $this->prepareForWidget($arrData, $strField);

            $arrOptions = array();

            foreach($arrWidget['options'] as $k => $option)
            {
                $intValue = (int) $option['value'];
                $strFilterKey = $strField . '::' . $intValue;

                // skip zero values (includeBlankOption)
                if($intValue == 0)
                    continue;

                $blnIsActive = ($GLOBALS['ISO_FILTERS'][$this->id][$strFilterKey]['value'] == $intValue);

                $arrParams		= array();
                $arrParams[]	= array('mod', $this->id);
                $arrParams[]	= array('attr', $strField);
                $arrParams[]	= array('v', $intValue);

                // add or remove mode
                if($blnIsActive)
                {
                    $arrParams[]	= array('cfilter', 'remove');
                }
                else
                {
                    $arrParams[]	= array('cfilter', 'add');
                }

                $arrOptions[$k]['label']	= $arrWidget['options'][$k]['label'];
                $arrOptions[$k]['default']	= $GLOBALS['ISO_FILTERS'][$this->id][$strField]['value'] ? '1' : '';
                $arrOptions[$k]['url']		= $this->addToCurrentUrl($arrParams);
                $arrOptions[$k]['isActive']	= $blnIsActive;
            }

            $arrFilters[$strField] = array
            (
                'label'		=> $arrWidget['label'],
                'options'	=> $arrOptions
            );
        }

        $this->Template->filterData = $arrFilters;
    }


    /**
     * Checks whether there is a ? in the url already and and adds a param to the current url
     * @param array
     * @return string
     */
    protected function addToCurrentUrl($arrParams)
    {
        $strUrl = Environment::get('request');

        foreach($arrParams as $arrParam)
        {
            $strUrl .= (strpos($strUrl, '?') !== false) ? '&' : '?';
            $strUrl .= $arrParam[0] . '=' . $arrParam[1];
        }

        return $strUrl;
    }
}
