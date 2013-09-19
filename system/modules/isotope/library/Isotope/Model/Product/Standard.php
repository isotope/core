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

namespace Isotope\Model\Product;

use Isotope\Isotope;
use Isotope\Interfaces\IsotopeAttribute;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\Gallery;
use Isotope\Model\Product;
use Isotope\Model\ProductPrice;
use Isotope\Model\ProductType;
use Isotope\Model\TaxClass;


/**
 * Class Product
 *
 * Provide methods to handle Isotope products.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 */
class Standard extends Product implements IsotopeProduct
{

    /**
     * Price model for the current product
     * @var Isotope\Model\ProductPrice
     */
    protected $objPrice = false;

    /**
     * Attributes assigned to this product type
     * @var array
     */
    protected $arrAttributes;

    /**
     * Variant attributes assigned to this product type
     * @var array
     */
    protected $arrVariantAttributes;

    /**
     * Available variant IDs
     * @var array
     */
    protected $arrVariantIds;

    /**
     * Product Options
     * @var array
     */
    protected $arrOptions = array();

    /**
     * Assigned categories (pages)
     * @var array
     */
    protected $arrCategories;

    /**
     * Unique form ID
     * @var string
     */
    protected $formSubmit = 'iso_product';

    /**
     * For option widgets, helps determine the encoding type for a form
     * @var boolean
     */
    protected $hasUpload = false;

    /**
     * For option widgets, don't submit if certain validation(s) fail
     * @var boolean
     */
    protected $doNotSubmit = false;


    /**
     * Get a property
     * @param   string
     * @return  mixed
     */
    public function __get($strKey)
    {
        switch ($strKey)
        {
            case 'formSubmit':
                return $this->formSubmit;

            case 'price':
                return Isotope::calculatePrice($this->arrData['price'], $this, 'price', $this->arrData['tax_class']);

            case 'description_meta':
                return $this->arrData['description_meta'] != '' ? $this->arrData['description_meta'] : ($this->arrData['teaser'] != '' ? $this->arrData['teaser'] : $this->arrData['description']);

            default:
                if ($this->pid > 0 && $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$strKey]['attributes']['customer_defined'] || $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$strKey]['attributes']['variant_option']) {

				    return isset($this->arrOptions[$strKey]) ? deserialize($this->arrOptions[$strKey]) : null;
			    }

                return isset($this->arrData[$strKey]) ? deserialize($this->arrData[$strKey]) : null;
        }
    }


    /**
     * Set a property
     * @param   string
     * @param   mixed
     */
    public function __set($strKey, $varValue)
    {
        switch ($strKey)
        {
            case 'sku':
            case 'name':
            case 'price':
                $this->arrData[$strKey] = $varValue;
                break;
        }
    }

    /**
     * Returns true if the product is published, otherwise returns false
     * @return  bool
     */
    public function isPublished()
    {
        if (!$this->arrData['published'])
        {
            return false;
        }
        elseif ($this->arrData['start'] > 0 && $this->arrData['start'] > time())
        {
            return false;
        }
        elseif ($this->arrData['stop'] > 0 && $this->arrData['stop'] < time())
        {
            return false;
        }

        return true;
    }

    /**
     * Returns true if the product is available to show on the website
     * @return  bool
     */
    public function isAvailableInFrontend()
    {
        if (BE_USER_LOGGED_IN !== true && !$this->isPublished()) {
            return false;
        }

        // Show to guests only
        if ($this->arrData['guests'] && FE_USER_LOGGED_IN === true && BE_USER_LOGGED_IN !== true && !$this->arrData['protected']) {
            return false;
        }

        // Protected product
        if (BE_USER_LOGGED_IN !== true && $this->arrData['protected']) {
            if (FE_USER_LOGGED_IN !== true) {
                return false;
            }

            $groups = deserialize($this->arrData['groups']);

            if (!is_array($groups) || empty($groups) || !count(array_intersect($groups, \FrontendUser::getInstance()->groups))) {
                return false;
            }
        }

        // Check that the product is in any page of the current site
        if (count(\Isotope\Frontend::getPagesInCurrentRoot($this->getCategories(), \FrontendUser::getInstance())) == 0) {
            return false;
        }

        // Check if "advanced price" is available
        if (null === $this->getPrice() && (in_array('price', $this->getAttributes()) || $this->hasVariantPrices())) {
            return false;
        }

        return true;
    }

    /**
     * Returns true if the product is available
     * @return  bool
     */
    public function isAvailableForCollection(IsotopeProductCollection $objCollection)
    {
        if ($objCollection->isLocked()) {
            return true;
        }

        if (BE_USER_LOGGED_IN !== true && !$this->isPublished()) {
            return false;
        }

        // Show to guests only
        if ($this->arrData['guests'] && $objCollection->member > 0 && BE_USER_LOGGED_IN !== true && !$this->arrData['protected']) {
            return false;
        }

        // Protected product
        if (BE_USER_LOGGED_IN !== true && $this->arrData['protected']) {
            if ($objCollection->member == 0) {
                return false;
            }

            $groups = deserialize($this->arrData['groups']);
            $memberGroups = deserialize($objCollection->getRelated('member')->groups);

            if (!is_array($groups) || empty($groups) || !is_array($memberGroups) || empty($memberGroups) || !count(array_intersect($groups, $memberGroups))) {
                return false;
            }
        }

        // Check that the product is in any page of the current site
        if (count(\Isotope\Frontend::getPagesInCurrentRoot($this->getCategories(), $objCollection->getRelated('member'))) == 0) {
            return false;
        }

        // Check if "advanced price" is available
        if (null === $this->getPrice($objCollection) && (in_array('price', $this->getAttributes()) || $this->hasVariantPrices())) {
            return false;
        }

        return true;
    }

    /**
     * Checks whether a product is new according to the current store config
     * @return  boolean
     */
    public function isNew()
    {
        return $this->dateAdded >= Isotope::getConfig()->getNewProductLimit();
    }

    /**
     * Return true if the product or product type has shipping exempt activated
     * @return  bool
     */
    public function isExemptFromShipping()
    {
        return ($this->arrData['shipping_exempt'] || $this->getRelated('type')->shipping_exempt) ? true : false;
    }

    /**
     * Returns true if variants are enabled in the product type, otherwise returns false
     * @return  bool
     */
    public function hasVariants()
    {
        return (bool) $this->getRelated('type')->hasVariants();
    }

    /**
     * Returns true if product has variants, and the price is a variant attribute
     * @return  bool
     */
    public function hasVariantPrices()
    {
        if ($this->hasVariants() && in_array('price', $this->getVariantAttributes()))
        {
            return true;
        }

        return false;
    }

    /**
     * Returns true if advanced prices are enabled in the product type, otherwise returns false
     * @return  bool
     */
    public function hasAdvancedPrices()
    {
        return (bool) $this->getRelated('type')->hasAdvancedPrices();
    }

    /**
     * Return true if the user should see lowest price tier as lowest price
     * @return  bool
     */
    public function canSeePriceTiers()
    {
        return $this->hasAdvancedPrices() && $this->getRelated('type')->show_price_tiers;
    }

    /**
     * Get product price model
     * @param   IsotopeProductCollection
     * @return  IsotopePrice
     */
    public function getPrice(IsotopeProductCollection $objCollection=null)
    {
        if (false === $this->objPrice) {

            if (null === $objCollection) {
                $objCollection = Isotope::getCart();
            }

            if ($this->hasVariantPrices() && $this->pid == 0) {
                $this->objPrice = ProductPrice::findLowestActiveByVariantsAndCollection($this, $objCollection);
            } else {
                $this->objPrice = ProductPrice::findActiveByProductAndCollection($this, $objCollection);
            }
        }

        return $this->objPrice;
    }

    /**
     * Return minimum quantity for the product (from advanced price tiers)
     * @return  int
     */
    public function getMinimumQuantity()
    {
        // Minimum quantity is only available for advanced pricing
        if (!$this->hasAdvancedPrices()) {
            return 1;
        }

        $this->getPrice()->getLowestTier();
    }


    /**
     * Return the product attributes
     * @return  array
     */
    public function getAttributes()
    {
        if (null === $this->arrAttributes) {
            $this->arrAttributes = $this->getRelated('type')->getAttributes();
        }

        return $this->arrAttributes;
    }


    /**
     * Return the product variant attributes
     * @return  array
     */
    public function getVariantAttributes()
    {
        if (null === $this->arrVariantAttributes) {
            $this->arrVariantAttributes = $this->getRelated('type')->getVariantAttributes();
        }

        return $this->arrVariantAttributes;
    }

    /**
     * Return all available variant IDs of this product
     * @return  array|false
     */
    public function getVariantIds()
    {
        if (null === $this->arrVariantIds) {

            $this->arrVariantIds = array();

            $time = time();
            $blnHasProtected = false;
            $strQuery = "SELECT id, protected, groups FROM tl_iso_products WHERE pid=" . ($this->pid ?: $this->id) . " AND language='' AND published='1' AND (start='' OR start<$time) AND (stop='' OR stop>$time)";

            if (BE_USER_LOGGED_IN !== true) {
                $arrAttributes = $this->getVariantAttributes();
                $blnHasProtected = in_array('protected', $arrAttributes);
                $blnHasGroups = in_array('groups', $arrAttributes);

                // Hide guests-only products when logged in
                if (FE_USER_LOGGED_IN === true && in_array('guests', $arrAttributes)) {
                    $strQuery .= " AND (guests=''" . ($blnHasProtected ? " OR protected='1'" : '') . ")";
                }

                // Hide protected if no user is logged in
                elseif (FE_USER_LOGGED_IN !== true && $blnHasProtected) {
                    $strQuery .= " AND protected=''";
                }
            }

            $objVariants = \Database::getInstance()->query($strQuery);

            while ($objVariants->next()) {
                if ($blnHasProtected && $objVariants->protected) {
                    $groups = $blnHasGroups ? deserialize($objVariants->groups) : '';

                    if (empty($groups) || !is_array($groups) || !count(array_intersect($groups, \FrontendUser::getInstance()->groups))) {
                        continue;
                    }
                }

                $this->arrVariantIds[] = $objVariants->id;
            }

            // @todo check if each variant has a price
        }

        return $this->arrVariantIds;
    }

    /**
     * Get categories (pages) assigned to this product
     * @return  array
     */
    public function getCategories()
    {
        if (null === $this->arrCategories) {
            $this->arrCategories = \Database::getInstance()->execute("SELECT page_id FROM tl_iso_product_categories WHERE pid=" . ($this->pid ?: $this->id) . " ORDER BY sorting")->fetchEach('page_id');
        }

        return $this->arrCategories;
    }


    /**
     * Return all product options
     * @return  array
     */
    public function getOptions()
    {
        return $this->arrOptions;
    }


    /**
     * Set options data
     * @param   array
     */
    public function setOptions(array $arrOptions)
    {
        $this->arrOptions = $arrOptions;
    }


    /**
     * Generate a product template
     * @param   array
     * @return  string
     */
    public function generate(array $arrConfig)
    {
        $this->formSubmit = (($arrConfig['module'] instanceof \ContentElement) ? 'cte' : 'fmd') . $arrConfig['module']->id . '_product_' . ($this->pid ? $this->pid : $this->id);
        $this->validateVariant();

        $objProduct = $this;
        $arrGalleries = array();

        $objTemplate = new \Isotope\Template($arrConfig['template']);
        $objTemplate->setData($this->arrData);
        $objTemplate->product = $this;
        $objTemplate->config = $arrConfig;

        $objTemplate->generateAttribute = function($strAttribute, array $arrOptions=array()) use ($objProduct) {

            $objAttribute = $GLOBALS['TL_DCA']['tl_iso_products']['attributes'][$strAttribute];

            if (!($objAttribute instanceof IsotopeAttribute)) {
                throw new \InvalidArgumentException($strAttribute . ' is not a valid attribute');
            }

            return $objAttribute->generate($objProduct, $arrOptions);
        };

        $objTemplate->generatePrice = function() use ($objProduct) {
            $objPrice = $this->getPrice();

            if (null === $objPrice) {
                return '';
            }

            return $objPrice->generate(($this->pid == 0));
        };

        $objTemplate->getGallery = function($strAttribute) use ($objProduct, $arrConfig, &$arrGalleries) {

            if (!isset($arrGalleries[$strAttribute])) {
                $arrGalleries[$strAttribute] = Gallery::createForProductAttribute(
                    $objProduct,
                    $strAttribute,
                    $arrConfig
                );
            }

            return $arrGalleries[$strAttribute];
        };



        $arrVariantOptions = array();
        $arrProductOptions = array();
        $arrAjaxOptions = array();

        foreach (array_unique(array_merge($this->getAttributes(), $this->getVariantAttributes())) as $attribute)
        {
            $arrData = $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute];

            if ($arrData['attributes']['customer_defined'] || $arrData['attributes']['variant_option']) {

                $strWidget = $this->generateProductOptionWidget($attribute, $arrVariantOptions);

                if ($strWidget != '')
                {
                    $objTemplate->hasOptions = true;
                    $arrProductOptions[$attribute] = array_merge($arrData, array
                    (
                        'name'    => $attribute,
                        'html'    => $strWidget,
                    ));

                    if ($arrData['attributes']['variant_option'] || $arrData['attributes']['ajax_option']) {
                        $arrAjaxOptions[] = $attribute;
                    }
                }

            }
        }

        $arrButtons = array();

        // !HOOK: retrieve buttons
        if (isset($GLOBALS['ISO_HOOKS']['buttons']) && is_array($GLOBALS['ISO_HOOKS']['buttons']))
        {
            foreach ($GLOBALS['ISO_HOOKS']['buttons'] as $callback)
            {
                $objCallback = \System::importStatic($callback[0]);
                $arrButtons = $objCallback->$callback[1]($arrButtons);
            }

            $arrButtons = array_intersect_key($arrButtons, array_flip($arrConfig['buttons']));
        }

        if (\Input::post('FORM_SUBMIT') == $this->formSubmit && !$this->doNotSubmit)
        {
            foreach ($arrButtons as $button => $data)
            {
                if (\Input::post($button) != '')
                {
                    if (isset($data['callback']))
                    {
                        $objCallback = \System::importStatic($data['callback'][0]);
                        $objCallback->{$data['callback'][1]}($this, $arrConfig);
                    }
                    break;
                }
            }
        }

        $objTemplate->buttons = $arrButtons;
        $objTemplate->useQuantity = $arrConfig['useQuantity'];
        $objTemplate->minimum_quantity = $this->getMinimumQuantity();
        $objTemplate->raw = $this->arrData;
        $objTemplate->raw_options = $this->arrOptions;
        $objTemplate->href = $this->generateUrl($arrConfig['jumpTo']);
        $objTemplate->label_detail = $GLOBALS['TL_LANG']['MSC']['detailLabel'];
        $objTemplate->options = \Isotope\Frontend::generateRowClass($arrProductOptions, 'product_option');
        $objTemplate->hasOptions = !empty($arrProductOptions);
        $objTemplate->enctype = $this->hasUpload ? 'multipart/form-data' : 'application/x-www-form-urlencoded';
        $objTemplate->formId = $this->formSubmit;
        $objTemplate->action = ampersand(\Environment::get('request'), true);
        $objTemplate->formSubmit = $this->formSubmit;
        $objTemplate->product_id = ($this->pid ? $this->pid : $this->id);
        $objTemplate->module_id = $arrConfig['module']->id;

        $GLOBALS['AJAX_PRODUCTS'][] = array('formId'=>$this->formSubmit, 'attributes'=>$arrAjaxOptions);

        // !HOOK: alter product data before output
        if (isset($GLOBALS['ISO_HOOKS']['generateProduct']) && is_array($GLOBALS['ISO_HOOKS']['generateProduct']))
        {
            foreach ($GLOBALS['ISO_HOOKS']['generateProduct'] as $callback)
            {
                $objCallback = \System::importStatic($callback[0]);
                $objCallback->$callback[1]($objTemplate, $this);
            }
        }

        return $objTemplate->parse();
    }


    /**
     * Return a widget object based on a product attribute's properties
     * @param   string
     * @param   boolean
     * @return  string
     */
    protected function generateProductOptionWidget($strField, &$arrVariantOptions)
    {
        $objAttribute = $GLOBALS['TL_DCA']['tl_iso_products']['attributes'][$strField];
        $arrData = $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$strField];

        $strClass = $objAttribute->getFrontendWidget();

        $arrData['eval']['mandatory'] = ($arrData['eval']['mandatory'] && !\Environment::get('isAjaxRequest')) ? true : false;
        $arrData['eval']['required'] = $arrData['eval']['mandatory'];

        // Value can be predefined in the URL, e.g. to preselect a variant
        if (\Input::get($strField) != '') {
            $arrData['default'] = \Input::get($strField);
        }

        // Prepare variant selection field
        if ($objAttribute->isVariantOption()) {

            $arrOptions = $objAttribute->getOptionsForVariants($this->getVariantIds(), $arrVariantOptions);

            // Hide selection if only one option is available (and "force_variant_options" is not set in product type)
            if (\Input::post('FORM_SUBMIT') != $this->formSubmit && count($arrOptions) == 1 && !$this->getRelated('type')->force_variant_options) {
                $arrVariantOptions[$strField] = $arrOptions[0];

                return '';
            }

            $arrField = $strClass::getAttributesFromDca($arrData, $strField, $arrData['default']);

            // Remove options not available in any product variant
            if (is_array($arrData['options'])) {
                foreach ($arrField['options'] as $k => $option) {

                    // Keep groups and blankOptionLabels
                    if (!in_array($option['value'], $arrOptions) && !$option['group'] && $option['value'] != '') {
                        unset($arrField['options'][$k]);
                    }
                }
            }

            $arrField['options'] = array_values($arrField['options']);

            // Set field value if a variant is selected
            if ($this->pid > 0) {
                $arrField['value'] = $this->arrOptions[$strField];
            }
        }

        // Not a variant widget, but customer editable
        else {
            $arrField = $strClass::getAttributesFromDca($arrData, $strField, $arrData['default']);
        }

        $objWidget = new $strClass($arrField);

        $objWidget->storeValues = true;
        $objWidget->tableless = true;
        $objWidget->id .= "_" . $this->formSubmit;

        // Validate input
        if (\Input::post('FORM_SUBMIT') == $this->formSubmit)
        {
            $objWidget->validate();

            if ($objWidget->hasErrors()) {
                $this->doNotSubmit = true;
            }

            // Store current value
            elseif ($objWidget->submitInput() || $objWidget instanceof \uploadable) {
                $varValue = $objWidget->value;

                // Convert date formats into timestamps
                if ($varValue != '' && in_array($arrData['eval']['rgxp'], array('date', 'time', 'datim'))) {
                    $objDate = new \Date($varValue, $GLOBALS['TL_CONFIG'][$arrData['eval']['rgxp'] . 'Format']);
                    $varValue = $objDate->tstamp;
                }

                // Trigger the save_callback
                if (is_array($arrData['save_callback'])) {
                    foreach ($arrData['save_callback'] as $callback) {

                        $objCallback = \System::importStatic($callback[0]);

                        try {
                            $varValue = $objCallback->$callback[1]($varValue, $this, $objWidget);
                        } catch (\Exception $e) {
                            $objWidget->class = 'error';
                            $objWidget->addError($e->getMessage());
                            $this->doNotSubmit = true;
                        }
                    }
                }

                if (!$objWidget->hasErrors()) {
                    $arrVariantOptions[$strField] = $varValue;
                }
            }
        }

        $wizard = '';

        // Datepicker
        if ($arrData['eval']['datepicker']) {

            $GLOBALS['TL_JAVASCRIPT'][] = 'plugins/datepicker/datepicker.js';
            $GLOBALS['TL_CSS'][] = 'plugins/datepicker/dashboard.css';

            $rgxp = $arrData['eval']['rgxp'];
            $format = Date::formatToJs($GLOBALS['TL_CONFIG'][$rgxp.'Format']);

            switch ($rgxp) {
                case 'datim':
                    $time = ",\n      timePicker:true";
                    break;

                case 'time':
                    $time = ",\n      pickOnly:\"time\"";
                    break;

                default:
                    $time = '';
                    break;
            }

            $wizard .= ' <img src="plugins/datepicker/icon.gif" width="20" height="20" alt="" id="toggle_' . $objWidget->id . '" style="vertical-align:-6px">
  <script>
  window.addEvent("domready", function() {
    new Picker.Date($$("#ctrl_' . $objWidget->id . '"), {
      draggable:false,
      toggle:$$("#toggle_' . $objWidget->id . '"),
      format:"' . $format . '",
      positionOffset:{x:-197,y:-182}' . $time . ',
      pickerClass:"datepicker_dashboard",
      useFadeInOut:!Browser.ie,
      startDay:' . $GLOBALS['TL_LANG']['MSC']['weekOffset'] . ',
      titleFormat:"' . $GLOBALS['TL_LANG']['MSC']['titleFormat'] . '"
    });
  });
  </script>';
        }

        // Add a custom wizard
        if (is_array($arrData['wizard'])) {
            foreach ($arrData['wizard'] as $callback) {
                $objCallback = \System::importStatic($callback[0]);
                $wizard .= $objCallback->$callback[1]($this);
            }
        }

        if ($objWidget instanceof \uploadable) {
            $this->hasUpload = true;
        }

        return $objWidget->parse() . $wizard;
    }

    /**
     * Load data of a product variant if the options match one
     */
    protected function validateVariant()
    {
        if (!$this->hasVariants()) {
            return;
        }

        $arrOptions = array();

        foreach (array_intersect($this->getVariantAttributes(), $GLOBALS['ISO_CONFIG']['variant_options']) as $attribute) {

            $objAttribute = $GLOBALS['TL_DCA']['tl_iso_products']['attributes'][$attribute];
            $arrValues = $objAttribute->getOptionsForVariants($this->getVariantIds(), $arrOptions);

            if (\Input::post('FORM_SUBMIT') == $this->formSubmit && in_array(\Input::post($attribute), $arrValues)) {
                $arrOptions[$attribute] = \Input::post($attribute);
            } elseif (\Input::post('FORM_SUBMIT') == '' && in_array(\Input::get($attribute), $arrValues)) {
                $arrOptions[$attribute] = \Input::get($attribute);
            } elseif (count($arrValues) == 1) {
                $arrOptions[$attribute] = $arrValues[0];
            } else {

                // Abort if any attribute does not have a value, we can't find a variant
                return;
            }
        }

        if (!empty($arrOptions)) {

            // Do not use the model, it would trigger setRow and generate too much
            $objVariant = \Database::getInstance()->prepare(
                static::buildQueryString(array(
                    'table'     => static::$strTable,
                    'column'    => array("tl_iso_products.id IN (" . implode(',', $this->getVariantIds()) . ") AND tl_iso_products." . implode('=? AND tl_iso_products.', array_keys($arrOptions)) . "=?")
                ))
            )->limit(1)->execute($arrOptions);

            if ($objVariant->numRows) {
                $this->loadVariantData($objVariant->row());
            }
        }
    }

    /**
     * Validate data and remove non-available attributes
     * @param   array
     * @return  Standard
     */
    public function setRow(array $arrData)
    {
        $this->resetCache();

        if ($arrData['pid'] > 0)
        {
            // Do not use the model, it would trigger setRow and generate too much
            $objParent = \Database::getInstance()->prepare(static::buildQueryString(array('table'=>static::$strTable, 'column'=>'id')))->execute($arrData['pid']);

            if (null === $objParent) {
                throw new \UnderflowException('Parent record of product ID ' . $arrData['id'] . ' not found');
            }

            $this->setRow($objParent->row());
            $this->loadVariantData($arrData);

            return $this;
        }

        // Must initialize product type to have attributes etc.
        if (!isset($this->arrRelated['type']))
        {
            $this->arrRelated['type'] = ProductType::findByPk($arrData['type']);

            if (null === $this->arrRelated['type']) {
                throw new \UnderflowException('Product type for product ID ' . $arrData['id'] . ' not found');
            }
        }

        $this->formSubmit = 'iso_product_' . $arrData['id'];

        // Remove attributes not in this product type
        foreach ($arrData as $attribute => $value) {
            if (!in_array($attribute, $this->getAttributes()) && !in_array($attribute, $this->getVariantAttributes()) && $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute]['attributes']['legend'] != '') {
                unset($arrData[$attribute]);
            }
        }

        return parent::setRow($arrData);
    }

    /**
     * Load variant data basing on provided data
     * @param   array
     */
    public function loadVariantData($arrData)
    {
        $this->resetCache();

        $arrInherit = deserialize($arrData['inherit'], true);

        $this->arrData['id'] = $arrData['id'];
        $this->arrData['pid'] = $arrData['pid'];

        // Set all variant attributes, except if they are inherited
        foreach (array_diff($this->getVariantAttributes(), $arrInherit) as $attribute) {

            $this->arrData[$attribute] = $arrData[$attribute];

            if (in_array($attribute, $GLOBALS['ISO_CONFIG']['fetch_fallback'])) {
                $this->arrData[$attribute.'_fallback'] = $arrData[$attribute.'_fallback'];
            }
        }

        // Load variant options
        $this->arrOptions = array_merge($this->arrOptions, array_intersect_key($arrData, array_flip(array_intersect($this->getVariantAttributes(), $GLOBALS['ISO_CONFIG']['variant_options']))));
    }

    /**
     * Generate url
     * @param   PageModel|int   A PageModel instance or a page id
     * @param   string          Optional parameters
     * @return  array
     */
    public function generateUrl($objPage, $arrParams=array())
    {
        if (!$objPage) {
            return '';
        }

        if (is_numeric($objPage)) {
            $objPage = \PageModel::findByPk($objPage);
        }

        if (null === $objPage) {
            return '';
        }

        $strUrlParam = Isotope::getConfig()->getUrlParam('product');
        $strUrl = '/' . ($strUrlParam ? $strUrlParam.'/' : '');
        $strUrl .= $this->arrData['alias'] ?: ($this->arrData['pid'] ?: $this->arrData['id']);

        $arrOptions = $this->getOptions();
        if (!empty($arrOptions)) {
            $arrParams = array_merge($arrOptions, $arrParams);
        }

        return \Isotope\Frontend::addQueryStringToUrl(
            http_build_query($arrParams),
            \Controller::generateFrontendUrl($objPage->row(), $strUrl, $objPage->language)
        );
    }

    /**
     * Unset cached data
     */
    protected function resetCache()
    {
        $this->objPrice = false;
        $this->arrAttributes = null;
        $this->arrVariantAttributes = null;
        $this->arrVariantIds = null;
        $this->arrOptions = array();
        $this->arrCategories = null;
    }
}
