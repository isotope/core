<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 *
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */

namespace Isotope;


/**
 * Class tl_module_isotope
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_module extends \Backend
{

    /**
     * Load tl_iso_products data container and language file
     */
    public function __construct()
    {
        parent::__construct();
        $this->loadDataContainer('tl_iso_products');
        $this->loadLanguageFile('tl_iso_products');
    }


    /**
     * Get the attribute filter fields and return them as array
     * @return array
     */
    public function getFilterFields()
    {
        $arrAttributes = array();

        foreach ($GLOBALS['TL_DCA']['tl_iso_products']['fields'] as $field => $arrData)
        {
            if ($arrData['attributes']['fe_filter'])
            {
                $arrAttributes[$field] = strlen($arrData['label'][0]) ? $arrData['label'][0] : $field;
            }
        }

        return $arrAttributes;
    }


    /**
     * Get the attribute sorting fields and return them as array
     * @return array
     */
    public function getSortingFields()
    {
        $arrAttributes = array();

        foreach ($GLOBALS['TL_DCA']['tl_iso_products']['fields'] as $field => $arrData)
        {
            if ($arrData['attributes']['fe_sorting'])
            {
                $arrAttributes[$field] = strlen($arrData['label'][0]) ? $arrData['label'][0] : $field;
            }
        }

        return $arrAttributes;
    }


    /**
     * Get the attribute search fields and return them as array
     * @return array
     */
    public function getSearchFields()
    {
        $arrAttributes = array();

        foreach ($GLOBALS['TL_DCA']['tl_iso_products']['fields'] as $field => $arrData)
        {
            if ($arrData['attributes']['fe_search'])
            {
                $arrAttributes[$field] = strlen($arrData['label'][0]) ? $arrData['label'][0] : $field;
            }
        }

        return $arrAttributes;
    }


    /**
     * Get the attribute autocomplete fields and return them as array
     * @return array
     */
    public function getAutocompleteFields()
    {
        $arrAttributes = array();

        foreach ($GLOBALS['TL_DCA']['tl_iso_products']['fields'] as $field => $arrData)
        {
            if ($arrData['attributes']['fe_search'] && !$arrData['attributes']['dynamic'])
            {
                $arrAttributes[$field] = strlen($arrData['label'][0]) ? $arrData['label'][0] : $field;
            }
        }

        return $arrAttributes;
    }


    /**
     * Returns a list of all payment modules
     * @return array
     */
    public function getPaymentModules()
    {
        $arrPaymentModules = array();
        $objPaymentModules = $this->Database->execute("SELECT * FROM tl_iso_payment_modules");

        while ($objPaymentModules->next())
        {
            $arrPaymentModules[$objPaymentModules->id] = $objPaymentModules->name;
        }

        return $arrPaymentModules;
    }


    /**
     * Get all enabled shipping modules and return them as array
     * @return array
     */
    public function getShippingModules()
    {
        $arrModules = array();
        $objModules = $this->Database->execute("SELECT * FROM tl_iso_shipping_modules WHERE enabled=1");

        while ($objModules->next())
        {
            $arrModules[$objModules->id] = $objModules->name;
        }

        return $arrModules;
    }


    /**
     * Get all login modules and return them as array
     * @return array
     */
    public function getLoginModuleList()
    {
        $arrModules = array();
        $objModules = $this->Database->execute("SELECT id, name FROM tl_module WHERE type='login'");

        while ($objModules->next())
        {
            $arrModules[$objModules->id] = $objModules->name;
        }

        return $arrModules;
    }


    /**
     * Get all buttons and return them as array
     * @return array
     */
    public function getButtons()
    {
        $arrOptions = array();
        $arrButtons = array();

        // !HOOK: add product buttons
        if (isset($GLOBALS['ISO_HOOKS']['buttons']) && is_array($GLOBALS['ISO_HOOKS']['buttons']))
        {
            foreach ($GLOBALS['ISO_HOOKS']['buttons'] as $callback)
            {
                $this->import($callback[0]);
                $arrButtons = $this->$callback[0]->$callback[1]($arrButtons);
            }
        }

        foreach ($arrButtons as $button => $data)
        {
            $arrOptions[$button] = $data['label'];
        }

        return $arrOptions;
    }


    /**
     * Return list templates as array
     * @param DataContainer
     * @return array
     */
    public function getListTemplates(\DataContainer $dc)
    {
        $intPid = $dc->activeRecord->pid;

        if (\Input::get('act') == 'overrideAll')
        {
            $intPid = \Input::get('id');
        }

        return \Isotope\Backend::getTemplates('iso_list_', $intPid);
    }


    /**
     * Return reader templates as array
     * @param DataContainer
     * @return array
     */
    public function getReaderTemplates(\DataContainer $dc)
    {
        $intPid = $dc->activeRecord->pid;

        if (\Input::get('act') == 'overrideAll')
        {
            $intPid = \Input::get('id');
        }

        return \Isotope\Backend::getTemplates('iso_reader_', $intPid);
    }


    /**
     * Return cart templates as array
     * @param DataContainer
     * @return array
     */
    public function getCartTemplates(\DataContainer $dc)
    {
        $intPid = $dc->activeRecord->pid;

        if (\Input::get('act') == 'overrideAll')
        {
            $intPid = \Input::get('id');
        }

        return \Isotope\Backend::getTemplates('iso_cart_', $intPid);
    }


    /**
     * Return filter templates as array
     * @param DataContainer
     * @return array
     */
    public function getFilterTemplates(\DataContainer $dc)
    {
        $intPid = $dc->activeRecord->pid;

        if (\Input::get('act') == 'overrideAll')
        {
            $intPid = \Input::get('id');
        }

        return \Isotope\Backend::getTemplates('iso_filter_', $intPid);
    }


    /**
     * Get all filter modules and return them as array
     * @param DataContainer
     * @return array
     */
    public function getFilterModules(\DataContainer $dc)
    {
        $arrClasses = array();

        foreach ($GLOBALS['FE_MOD'] as $strGroup => $arrModules)
        {
            foreach ($arrModules as $strName => $strClass)
            {
                if ($strClass != '' && !$this->classFileExists($strClass))
                {
                    continue;
                }

                if ($strClass == 'Isotope\Module\ProductFilter' || is_subclass_of($strClass, 'Isotope\Module\ProductFilter'))
                {
                    $arrClasses[] = $strName;
                }
            }
        }

        $arrModules = array();
        $objModules = $this->Database->execute("SELECT * FROM tl_module WHERE type IN ('" . implode("','", $arrClasses) . "')");

        while ($objModules->next())
        {
            $arrModules[$objModules->id] = $objModules->name;
        }

        return $arrModules;
    }
}
