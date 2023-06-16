<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Product;

use Contao\ContentElement;
use Contao\Database;
use Contao\Date;
use Contao\Environment;
use Contao\FrontendUser;
use Contao\Input;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Contao\Widget;
use Haste\Generator\RowClass;
use Haste\Units\Mass\Weight;
use Haste\Units\Mass\WeightAggregate;
use Haste\Util\Url;
use Isotope\Collection\ProductPrice as ProductPriceCollection;
use Isotope\Frontend\ProductAction\ProductActionInterface;
use Isotope\Frontend\ProductAction\Registry;
use Isotope\Interfaces\IsotopeAttribute;
use Isotope\Interfaces\IsotopeAttributeForVariants;
use Isotope\Interfaces\IsotopeAttributeWithOptions;
use Isotope\Interfaces\IsotopePrice;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopeProductWithOptions;
use Isotope\Isotope;
use Isotope\Model\Attribute;
use Isotope\Model\Gallery;
use Isotope\Model\Gallery\Standard as StandardGallery;
use Isotope\Model\ProductCollectionItem;
use Isotope\Model\ProductPrice;
use Isotope\Model\ProductType;
use Isotope\Template;

/**
 * Standard implementation of an Isotope product.
 */
class Standard extends AbstractProduct implements WeightAggregate, IsotopeProductWithOptions
{

    /**
     * Price model for the current product
     * @var \Isotope\Model\ProductPrice
     */
    protected $objPrice = false;

    /**
     * Attributes assigned to this product type
     * @var array
     * @deprecated
     */
    protected $arrAttributes;

    /**
     * Variant attributes assigned to this product type
     * @var array
     * @deprecated
     */
    protected $arrVariantAttributes;

    /**
     * Available variant IDs
     * @var int[]
     */
    protected $arrVariantIds;

    /**
     * Customer defined configuration
     * @var array
     */
    protected $arrCustomerConfig = array();

    /**
     * Default configuration (to predefine variant or customer editable attributes)
     * @var array
     */
    protected $arrDefaults;

    /**
     * Unique form ID
     * @var string
     */
    protected $strFormId = 'iso_product';

    /**
     * For option widgets, helps determine the encoding type for a form
     * @var bool
     */
    protected $hasUpload = false;

    /**
     * For option widgets, don't submit if certain validation(s) fail
     * @var bool
     */
    protected $doNotSubmit = false;

    /**
     * @inheritdoc
     */
    public function isAvailableForCollection(IsotopeProductCollection $objCollection)
    {
        if (false === parent::isAvailableForCollection($objCollection)) {
            return false;
        }

        // Check that the product is in any page of the current site
        if (\count(\Isotope\Frontend::getPagesInCurrentRoot($this->getCategories(), $objCollection->getMember())) == 0) {
            return false;
        }

        if ($this->hasVariants() && !count($this->getVariantIds())) {
            return false;
        }

        // Check if "advanced price" is available
        if ($this->getType()->hasAdvancedPrices()
            && (\in_array('price', $this->getType()->getAttributes(), true) || $this->hasVariantPrices())
            && null === $this->getPrice($objCollection)
        ) {
            return false;
        }

        return true;
    }

    /**
     * Return true if the user should see lowest price tier as lowest price
     *
     * @return bool
     */
    public function canSeePriceTiers()
    {
        return $this->hasAdvancedPrices() && $this->getType()->show_price_tiers;
    }

    /**
     * Return the unique form ID for the product
     *
     * @return string
     */
    public function getFormId()
    {
        return $this->strFormId;
    }

    /**
     * Get product price model
     *
     * @param IsotopeProductCollection $objCollection
     *
     * @return \Isotope\Interfaces\IsotopePrice|ProductPrice
     */
    public function getPrice(IsotopeProductCollection $objCollection = null)
    {
        if (null !== $objCollection && $objCollection !== Isotope::getCart()) {
            return ProductPrice::findByProductAndCollection($this, $objCollection);
        }

        if (false === $this->objPrice) {
            if (null === $objCollection) {
                $objCollection = Isotope::getCart();
            }

            $this->objPrice = ProductPrice::findByProductAndCollection($this, $objCollection);
        }

        return $this->objPrice;
    }

    public function setPrice(IsotopePrice $price)
    {
        $this->objPrice = $price;

        if ($price instanceof ProductPrice || $price instanceof ProductPriceCollection) {
            $price->setProduct($this);
        }
    }

    /**
     * Return minimum quantity for the product (from advanced price tiers)
     *
     * @return int
     */
    public function getMinimumQuantity()
    {
        // Minimum quantity is only available for advanced pricing
        if (!$this->hasAdvancedPrices() || null === $this->getPrice()) {
            return 1;
        }

        $intLowest = (int) $this->getPrice()->getLowestTier();

        if ($intLowest < 1) {
            return 1;
        }

        return $intLowest;
    }


    /**
     * Return the product attributes
     *
     * @return array
     *
     * @deprecated Deprecated since Isotope 2.4, to be removed in Isotope 3. Use ProductType::getAttributes()
     */
    public function getAttributes()
    {
        if (null === $this->arrAttributes) {
            $this->arrAttributes = $this->getType()->getAttributes();
        }

        return $this->arrAttributes;
    }


    /**
     * Return the product variant attributes
     *
     * @return array
     *
     * @deprecated Deprecated since Isotope 2.4, to be removed in Isotope 3. Use ProductType::getVariantAttributes()
     */
    public function getVariantAttributes()
    {
        if (null === $this->arrVariantAttributes) {
            $this->arrVariantAttributes = $this->getType()->getVariantAttributes();
        }

        return $this->arrVariantAttributes;
    }

    /**
     * Return all available variant IDs of this product
     *
     * @return int[]
     */
    public function getVariantIds()
    {
        if (null === $this->arrVariantIds) {
            $this->arrVariantIds = [];

            // Nothing to do if we have no variants
            if (!$this->hasVariants()) {
                return $this->arrVariantIds;
            }

            $time            = Date::floorToMinute();
            $blnHasProtected = false;
            $blnHasGuests    = false;
            $strQuery        = '
                SELECT tl_iso_product.id, tl_iso_product.protected, tl_iso_product.groups
                FROM tl_iso_product
                WHERE
                    pid=' . $this->getProductId() . "
                    AND language=''
                    AND published='1'
                    AND (start='' OR start<'$time')
                    AND (stop='' OR stop>'" . ($time + 60) . "')
            ";

            if (BE_USER_LOGGED_IN !== true) {
                $arrAttributes   = $this->getType()->getVariantAttributes();
                $blnHasProtected = \in_array('protected', $arrAttributes, true);
                $blnHasGuests = \in_array('guests', $arrAttributes, true);

                // Hide guests-only products when logged in
                if (FE_USER_LOGGED_IN === true && $blnHasGuests) {
                    $strQuery .= " AND (guests=''" . ($blnHasProtected ? " OR protected='1'" : '') . ')';
                } // Hide protected if no user is logged in
                elseif (FE_USER_LOGGED_IN !== true && $blnHasProtected) {
                    $strQuery .= " AND (protected=''" . ($blnHasGuests ? " OR guests='1'" : '') . ")";
                }
            }

            /** @var object $objVariants */
            $objVariants = Database::getInstance()->query($strQuery);

            while ($objVariants->next()) {
                if (FE_USER_LOGGED_IN !== true
                    && $blnHasProtected
                    && $objVariants->protected
                    && (!$blnHasGuests || !$objVariants->guests)
                ) {
                    continue;
                }

                if (FE_USER_LOGGED_IN === true
                    && $blnHasGuests
                    && $objVariants->guests
                    && (!$blnHasProtected || $objVariants->protected)
                ) {
                    continue;
                }

                if ($blnHasProtected && $objVariants->protected) {
                    $groups = StringUtil::deserialize($objVariants->groups);

                    if (empty($groups) || !\is_array($groups) || !\count(array_intersect($groups, FrontendUser::getInstance()->groups))) {
                        continue;
                    }
                }

                $this->arrVariantIds[] = $objVariants->id;
            }

            // Only show variants where a price is available
            if (0 !== \count($this->arrVariantIds) && $this->hasVariantPrices()) {
                if ($this->hasAdvancedPrices()) {
                    $objPrices = ProductPrice::findAdvancedByProductIdsAndCollection($this->arrVariantIds, Isotope::getCart());
                } else {
                    $objPrices = ProductPrice::findPrimaryByProductIds($this->arrVariantIds);
                }

                if (null === $objPrices) {
                    $this->arrVariantIds = [];
                } else {
                    $this->arrVariantIds = $objPrices->fetchEach('pid');
                }
            }
        }

        return $this->arrVariantIds;
    }

    /**
     * Get the weight of the product (as object)
     *
     * @return Weight
     */
    public function getWeight()
    {
        if (!isset($this->arrData['shipping_weight'])) {
            return null;
        }

        return Weight::createFromTimePeriod($this->arrData['shipping_weight']);
    }

    /**
     * @inheritdoc
     */
    public function getOptions()
    {
        return array_merge($this->getVariantConfig(), $this->getCustomerConfig());
    }

    /**
     * @inheritdoc
     */
    public function setOptions(array $options)
    {
        if (!$this->blnPreventSaving) {
            throw new \RuntimeException('Do not modify a product object that is in the model registry!');
        }

        if (!$this->isVariant()) {
            $this->arrCustomerConfig = $options;
            return;
        }

        $attributes = array_intersect($this->getType()->getVariantAttributes(), Attribute::getVariantOptionFields());
        $this->arrCustomerConfig = [];

        foreach ($options as $k => $v) {
            if (\in_array($k, $attributes, true)) {
                if ($this->arrData[$k] != $v) {
                    throw new \RuntimeException(
                        sprintf('"%s" for attribute "%s" does not match current variant.', $v, $k)
                    );
                }

                // Ignore variant data, that's already stored
                continue;
            }

            $this->arrCustomerConfig[$k] = $v;
        }
    }

    /**
     * Get the product configuration
     * This includes customer defined fields and variant options
     *
     * @return array
     *
     * @deprecated Deprecated since Isotope 2.4, to be removed in Isotope 3.0. Use getOptions() instead.
     */
    public function getConfiguration()
    {
        return Isotope::formatProductConfiguration($this->getOptions(), $this);
    }

    /**
     * Get customer defined field values
     *
     * @return array
     */
    public function getCustomerConfig()
    {
        return $this->arrCustomerConfig;
    }

    /**
     * Get variant option field values
     *
     * @return array
     */
    public function getVariantConfig()
    {
        if (!$this->isVariant()) {
            return array();
        }

        $arrVariantConfig = array();
        $arrAttributes = array_intersect($this->getType()->getVariantAttributes(), Attribute::getVariantOptionFields());

        foreach (array_unique($arrAttributes) as $attribute) {
            $arrVariantConfig[$attribute] = $this->arrData[$attribute];
        }

        return $arrVariantConfig;
    }

    /**
     * Generate a product template
     *
     * @param array $arrConfig
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function generate(array $arrConfig)
    {
        $objProduct = $this;
        $loadFallback = isset($arrConfig['loadFallback']) ? (bool) $arrConfig['loadFallback'] : true;

        $this->strFormId = (($arrConfig['module'] instanceof ContentElement) ? 'cte' : 'fmd') . $arrConfig['module']->id . '_product_' . $this->getProductId();

        if (!($arrConfig['disableOptions'] ?? false)) {
            $objProduct = $this->validateVariant($loadFallback);

            // A variant has been loaded, generate the variant
            if ($objProduct->getId() != $this->getId()) {
                return $objProduct->generate($arrConfig);
            }
        }

        /** @var Template|\stdClass $objTemplate */
        $objTemplate = new Template($arrConfig['template']);
        $objTemplate->setData($this->arrData);
        $objTemplate->product = $this;
        $objTemplate->config  = $arrConfig;

        $objTemplate->highlightKeywords = function($text) {
            $keywords = Input::get('keywords');

            if (empty($keywords)) {
                return $text;
            }

            $keywords = StringUtil::trimsplit(' |-', $keywords);
            $keywords = array_filter(array_unique($keywords));

            foreach ($keywords as $word) {
                $text = StringUtil::highlight($text, $word, '<em>', '</em>');
            }

            return $text;
        };

        $objTemplate->hasAttribute = function ($strAttribute) use ($objProduct) {
            return \in_array($strAttribute, $objProduct->getType()->getAttributes(), true)
                || \in_array($strAttribute, $objProduct->getType()->getVariantAttributes(), true);
        };

        $objTemplate->generateAttribute = function ($strAttribute, array $arrOptions = array()) use ($objProduct) {

            $objAttribute = $GLOBALS['TL_DCA']['tl_iso_product']['attributes'][$strAttribute];

            if (!($objAttribute instanceof IsotopeAttribute)) {
                throw new \InvalidArgumentException($strAttribute . ' is not a valid attribute');
            }

            return $objAttribute->generate($objProduct, $arrOptions);
        };

        $objTemplate->generatePrice = function() use ($objProduct) {
            $objPrice = $objProduct->getPrice();

            /** @var ProductType $objType */
            $objType = $objProduct->getType();

            if (null === $objPrice) {
                return '';
            }

            return $objPrice->generate($objType->showPriceTiers(), 1, $objProduct->getOptions());
        };

        /** @var StandardGallery $currentGallery */
        $currentGallery          = null;
        $objTemplate->getGallery = function ($strAttribute, $galleryId=null) use ($objProduct, $arrConfig, &$currentGallery) {
            
            if(!is_null($isoGalleryId))
                $arrConfig['gallery'] = $isoGalleryId;
            
            if (null === $currentGallery
                || $currentGallery->getName() !== $objProduct->getFormId() . '_' . $strAttribute
            ) {
                $currentGallery = Gallery::createForProductAttribute(
                    $objProduct,
                    $strAttribute,
                    $arrConfig
                );
            }

            return $currentGallery;
        };

        $arrVariantOptions = array();
        $arrProductOptions = array();
        $arrAjaxOptions    = array();

        if (!($arrConfig['disableOptions'] ?? false)) {
            foreach (array_unique(array_merge($this->getType()->getAttributes(), $this->getType()->getVariantAttributes())) as $attribute) {
                $arrData = $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$attribute];

                if (($arrData['attributes']['customer_defined'] ?? null) || ($arrData['attributes']['variant_option'] ?? null)) {

                    $strWidget = $this->generateProductOptionWidget($attribute, $arrVariantOptions, $arrAjaxOptions, $objWidget);

                    if ($strWidget != '') {
                        $arrProductOptions[$attribute] = array_merge($arrData, array
                        (
                            'name'    => $attribute,
                            'html'    => $strWidget,
                            'widget'  => $objWidget,
                        ));
                    }

                    unset($objWidget);
                }
            }
        }

        /** @var ProductActionInterface[] $actions */
        $handleButtons = false;
        $actions = array_filter(
            Registry::all(true, $this),
            function (ProductActionInterface $action) use ($arrConfig) {
                return \in_array($action->getName(), $arrConfig['buttons'] ?? []) && $action->isAvailable($this, $arrConfig);
            }
        );

        // Sort actions by order in module configuration
        $buttonOrder = array_values($arrConfig['buttons'] ?? []);
        usort($actions, function (ProductActionInterface $a, ProductActionInterface $b) use ($buttonOrder) {
            return array_search($a->getName(), $buttonOrder) - array_search($b->getName(), $buttonOrder);
        });

        if (Input::post('FORM_SUBMIT') == $this->getFormId() && !$this->doNotSubmit) {
            $handleButtons = true;

            foreach ($actions as $action) {
                if ($action->handleSubmit($this, $arrConfig)) {
                    $handleButtons = false;
                    break;
                }
            }
        }

        /**
         * @deprecated Deprecated since Isotope 2.5
         */
        $objTemplate->buttons = function() use ($arrConfig, $handleButtons) {
            $arrButtons = array();

            // !HOOK: retrieve buttons
            if (isset($arrConfig['buttons'], $GLOBALS['ISO_HOOKS']['buttons'])
                && \is_array($arrConfig['buttons'])
                && \is_array($GLOBALS['ISO_HOOKS']['buttons'])
            ) {
                foreach ($GLOBALS['ISO_HOOKS']['buttons'] as $callback) {
                    $arrButtons = System::importStatic($callback[0])->{$callback[1]}($arrButtons, $this);
                }
            }

            $arrButtons = array_intersect_key($arrButtons, array_flip($arrConfig['buttons'] ?? []));

            if ($handleButtons) {
                foreach ($arrButtons as $button => $data) {
                    if (isset($_POST[$button])) {
                        if (isset($data['callback'])) {
                            System::importStatic($data['callback'][0])->{$data['callback'][1]}($this, $arrConfig);
                        }
                        break;
                    }
                }
            }

            return $arrButtons;
        };

        RowClass::withKey('rowClass')->addCustom('product_option')->addFirstLast()->addEvenOdd()->applyTo($arrProductOptions);

        $objTemplate->actions = $actions;
        $objTemplate->useQuantity = $arrConfig['useQuantity'] && null === $this->getCollectionItem();
        $objTemplate->minimum_quantity = $this->getMinimumQuantity();
        $objTemplate->raw = $this->arrData;
        $objTemplate->raw_options = $this->getConfiguration();
        $objTemplate->configuration = $this->getConfiguration();
        $objTemplate->href = '';
        $objTemplate->label_detail = $GLOBALS['TL_LANG']['MSC']['detailLabel'];
        $objTemplate->options = $arrProductOptions;
        $objTemplate->hasOptions = \count($arrProductOptions) > 0;
        $objTemplate->enctype = $this->hasUpload ? 'multipart/form-data' : 'application/x-www-form-urlencoded';
        $objTemplate->formId = $this->getFormId();
        $objTemplate->formSubmit = $this->getFormId();
        $objTemplate->product_id = $this->getProductId();
        $objTemplate->module_id = $arrConfig['module']->id ?? null;

        if (!($arrConfig['jumpTo'] ?? null) instanceof PageModel || $arrConfig['jumpTo']->iso_readerMode !== 'none') {
            $objTemplate->href = $this->generateUrl($arrConfig['jumpTo']);
        }

        if (!($arrConfig['disableOptions'] ?? false)) {
            $GLOBALS['AJAX_PRODUCTS'][] = array('formId' => $this->getFormId(), 'attributes' => $arrAjaxOptions);
        }

        // !HOOK: alter product data before output
        if (isset($GLOBALS['ISO_HOOKS']['generateProduct']) && \is_array($GLOBALS['ISO_HOOKS']['generateProduct'])) {
            foreach ($GLOBALS['ISO_HOOKS']['generateProduct'] as $callback) {
                System::importStatic($callback[0])->{$callback[1]}($objTemplate, $this);
            }
        }

        return trim($objTemplate->parse());
    }


    /**
     * Return a widget object based on a product attribute's properties
     *
     * @param string $strField
     * @param array  $arrVariantOptions
     * @param array  $arrAjaxOptions
     *
     * @return string
     */
    protected function generateProductOptionWidget($strField, &$arrVariantOptions, &$arrAjaxOptions, &$objWidget = null)
    {
        $arrDefaults = $this->getOptionsDefaults();

        /** @var IsotopeAttribute|IsotopeAttributeWithOptions|IsotopeAttributeForVariants|Attribute $objAttribute */
        $objAttribute = $GLOBALS['TL_DCA']['tl_iso_product']['attributes'][$strField];
        $arrData = $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$strField];

        /** @var Widget $strClass */
        $strClass = $objAttribute->getFrontendWidget();

        $arrData['eval']['required'] = $arrData['eval']['mandatory'];

        // Value can be predefined in the URL, e.g. to preselect a variant
        if (!empty($arrDefaults[$strField])) {
            $arrData['default'] = $arrDefaults[$strField];
        }

        $arrField = $strClass::getAttributesFromDca($arrData, $strField, $arrData['default'] ?? null, $strField, static::$strTable, $this);

        // Prepare variant selection field
        // @todo in 3.0: $objAttribute instanceof IsotopeAttributeForVariants
        if ($objAttribute->isVariantOption()) {
            $arrOptions = $objAttribute->getOptionsForVariants($this->getVariantIds(), $arrVariantOptions);

            // Hide selection if only one option is available (and "force_variant_options" is not set in product type)
            if (\count($arrOptions) == 1 && !$this->getType()->force_variant_options) {
                $arrVariantOptions[$strField] = $arrOptions[0];
                return '';
            }

            if ($arrField['value'] != '' && \in_array($arrField['value'], $arrOptions)) {
                $arrVariantOptions[$strField] = $arrField['value'];
            }

            // Remove options not available in any product variant
            if (\is_array($arrField['options'])) {
                $blankOption = null;

                foreach ($arrField['options'] as $k => $option) {
                    // Keep groups and blankOptionLabels
                    if ($option['value'] == '') {
                        if (null !== $blankOption) {
                            // Last blank option wins
                            $arrField['options'][$blankOption] = $option;
                            unset($arrField['options'][$k]);
                        } else {
                            $blankOption = $k;
                        }
                    } elseif (!\in_array($option['value'], $arrOptions) && !($option['group'] ?? false)) {
                        unset($arrField['options'][$k]);
                    }
                }

                $arrField['options'] = array_values($arrField['options']);
            }

            $arrField['value'] = $this->$strField;

        } elseif ($objAttribute instanceof IsotopeAttributeWithOptions && empty($arrField['options'])) {
            return '';
        }

        if ($objAttribute->isVariantOption()
            || ($objAttribute instanceof IsotopeAttributeWithOptions && $objAttribute->canHavePrices())
            || ($arrData['attributes']['ajax_option'] ?? null)
            || ($arrField['attributes']['ajax_option'] ?? null) // see https://github.com/isotope/core/issues/2096
        ) {
            $arrAjaxOptions[] = $strField;
        }

        // Convert optgroups so they work with FormSelectMenu
        // @deprecated Remove in Isotope 3.0, the options should match for frontend if attribute is customer defined
        if (
            \is_array($arrField['options'] ?? null)
            && array_is_assoc($arrField['options'])
            && \count(
                array_filter(
                    $arrField['options'], function($v) {
                        return !isset($v['label']);
                    }
                )
            ) > 0
        ) {
            $arrOptions = $arrField['options'];
            $arrField['options'] = array();

            foreach ($arrOptions as $k => $v) {
                if (isset($v['label'])) {
                    $arrField['options'][] = $v;
                } else {
                    $arrField['options'][] = array(
                        'label'     => $k,
                        'value'     => $k,
                        'group'     => '1',
                    );

                    foreach ($v as $vv) {
                        $arrField['options'][] = $vv;
                    }
                }
            }
        }

        $arrField['storeValues'] = true;
        $arrField['tableless'] = true;
        $arrField['product'] = $this;
        $arrField['id'] .= '_' . $this->getFormId();

        /** @var Widget|\stdClass $objWidget */
        $objWidget = new $strClass($arrField);

        // Validate input
        if (Input::post('FORM_SUBMIT') == $this->getFormId()) {
            $objWidget->validate();

            if ($objWidget->hasErrors()) {
                $this->doNotSubmit = true;
            } elseif ($objWidget->submitInput() || $objWidget instanceof \uploadable) {
                $varValue = $objWidget->value;

                // Convert date formats into timestamps
                if ($varValue != '' && \in_array($arrData['eval']['rgxp'], ['date', 'time', 'datim'], true)) {
                    try {
                        /** @var Date|object $objDate */
                        $objDate = new Date($varValue, $GLOBALS['TL_CONFIG'][$arrData['eval']['rgxp'] . 'Format']);
                        $varValue = $objDate->tstamp;

                    } catch (\OutOfBoundsException $e) {
                        $objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR'][$arrData['eval']['rgxp']], $GLOBALS['TL_CONFIG'][$arrData['eval']['rgxp'] . 'Format']));
                    }
                }

                // Trigger the save_callback
                if (\is_array($arrData['save_callback'] ?? null)) {
                    foreach ($arrData['save_callback'] as $callback) {
                        try {
                            if (\is_array($callback)) {
                                $varValue = System::importStatic($callback[0])->{$callback[1]}($varValue, $this, $objWidget);
                            } else {
                                $varValue = $objAttribute->{$callback}($varValue, $this, $objWidget);
                            }
                        } catch (\Exception $e) {
                            $objWidget->class = 'error';
                            $objWidget->addError($e->getMessage());
                            $this->doNotSubmit = true;
                        }
                    }
                }

                if (!$objWidget->hasErrors() && $varValue != '') {

                    // @todo in 3.0: $objAttribute instanceof IsotopeAttributeForVariants
                    if ($objAttribute->isVariantOption()) {
                        $arrVariantOptions[$strField] = $varValue;
                    } else {
                        $this->arrCustomerConfig[$strField] = $varValue;
                    }
                }
            }
        } elseif (isset($_GET[$strField]) && empty(Input::post('FORM_SUBMIT')) && !$objAttribute->isVariantOption()) {
            $this->arrCustomerConfig[$strField] = $objWidget->value = Input::get($strField);
        }

        $wizard = '';

        // Datepicker
        if ($arrData['eval']['datepicker']) {

            $GLOBALS['TL_JAVASCRIPT'][] = 'assets/datepicker/js/datepicker.min.js';
            $GLOBALS['TL_CSS'][] = 'assets/datepicker/css/datepicker.min.css';
            $icon = 'assets/datepicker/images/icon.svg';

            $rgxp   = $arrData['eval']['rgxp'];
            $format = Date::formatToJs($GLOBALS['TL_CONFIG'][$rgxp . 'Format']);

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

            $wizard .= ' <img src="'.$icon.'" width="20" height="20" alt="" id="toggle_' . $objWidget->id . '" style="vertical-align:-6px">
  <script>
  window.addEvent("domready", function() {
    new Picker.Date($$("#ctrl_' . $objWidget->id . '"), {
      draggable:false,
      toggle:$$("#toggle_' . $objWidget->id . '"),
      format:"' . $format . '",
      positionOffset:{x:-197,y:-182}' . $time . ',
      pickerClass:"datepicker_bootstrap",
      useFadeInOut:!Browser.ie,
      startDay:' . $GLOBALS['TL_LANG']['MSC']['weekOffset'] . ',
      titleFormat:"' . $GLOBALS['TL_LANG']['MSC']['titleFormat'] . '"
    });
  });
  </script>';
        }

        // Add a custom wizard
        if (\is_array($arrData['wizard'] ?? null)) {
            foreach ($arrData['wizard'] as $callback) {
                $wizard .= System::importStatic($callback[0])->{$callback[1]}($this);
            }
        }

        if ($objWidget instanceof \uploadable) {
            $this->hasUpload = true;
        }

        return $objWidget->parse() . $wizard;
    }

    /**
     * Load data of a product variant if the options match one
     *
     * @param bool $loadDefaultVariant
     *
     * @return IsotopeProduct|$this
     */
    public function validateVariant($loadDefaultVariant = true)
    {
        if (!$this->hasVariants()) {
            return $this;
        }

        $hasOptions = null;
        $arrOptions = array();
        $arrDefaults = $this->getOptionsDefaults();

        // We don't need to validate IsotopeAttributeForVariants interface here, because Attribute::getVariantOptionFields will check it
        foreach (array_intersect($this->getType()->getVariantAttributes(), Attribute::getVariantOptionFields()) as $attribute) {

            /** @var IsotopeAttribute|Attribute $objAttribute */
            $objAttribute = $GLOBALS['TL_DCA']['tl_iso_product']['attributes'][$attribute];
            $arrValues    = $objAttribute->getOptionsForVariants($this->getVariantIds(), $arrOptions);

            if (Input::post('FORM_SUBMIT') == $this->getFormId() && \in_array(Input::post($attribute), $arrValues)) {
                $arrOptions[$attribute] = Input::post($attribute);
            } elseif (Input::post('FORM_SUBMIT') == '' && isset($arrDefaults[$attribute]) && \in_array($arrDefaults[$attribute], $arrValues)) {
                $arrOptions[$attribute] = $arrDefaults[$attribute];
            } elseif (\count($arrValues) == 1) {
                $arrOptions[$attribute] = $arrValues[0];
            } else {

                // Abort if any attribute does not have a value, we can't find a variant
                $hasOptions = false;
                break;
            }

            if (Input::post('FORM_SUBMIT') === $this->getFormId() && Input::post($attribute) === '') {
                Input::setPost($attribute, $arrOptions[$attribute]);
            }
        }

        $hasOptions = false !== $hasOptions && \count($arrOptions) > 0;

        if ($hasOptions && ($objVariant = static::findVariantOfProduct($this, $arrOptions)) !== null) {
            return $objVariant;
        }

        if (!$hasOptions && $loadDefaultVariant && ($objVariant = static::findDefaultVariantOfProduct($this)) !== null) {
            return $objVariant;
        }

        return $this;
    }

    /**
     * Validate data and remove non-available attributes
     *
     * @param array $arrData
     *
     * @return $this
     */
    public function setRow(array $arrData)
    {
        if ($arrData['pid'] > 0) {
            // Do not use the model, it would trigger setRow and generate too much
            // @deprecated use static::buildFindQuery once we drop BC support for buildQueryString
            /** @var object $objParent */
            $objParent = Database::getInstance()->prepare(static::buildQueryString(array('table' => static::$strTable, 'column' => 'id')))->execute($arrData['pid']);

            if (null === $objParent) {
                throw new \UnderflowException('Parent record of product variant ID ' . $arrData['id'] . ' not found');
            }

            $this->setRow($objParent->row());

            // Must be set before call to getInheritedFields()
            $this->arrData['id'] = $arrData['id'];
            $this->arrData['pid'] = $arrData['pid'];
            $this->arrData['inherit'] = $arrData['inherit'];

            // Set all variant attributes, except if they are inherited
            $arrFallbackFields = Attribute::getFetchFallbackFields();
            $arrVariantFields = array_diff($this->getType()->getVariantAttributes(), $this->getInheritedFields());
            foreach ($arrData as $attribute => $value) {
                if (
                    \in_array($attribute, $arrVariantFields, true)
                    || (($GLOBALS['TL_DCA']['tl_iso_product']['fields'][$attribute]['attributes']['legend'] ?? '') == ''
                        && !\in_array(str_replace('_fallback', '', $attribute), $arrFallbackFields, true))
                ) {
                    $this->arrData[$attribute] = $arrData[$attribute];

                    if (\in_array($attribute, $arrFallbackFields, true)) {
                        $this->arrData[$attribute . '_fallback'] = $arrData[$attribute . '_fallback'];
                    }
                }
            }

            // Make sure publishing settings match product and variant (see #1120)
            $this->arrData['published'] = $objParent->published ? $arrData['published'] : '';
            $this->arrData['start'] = ($objParent->start != '' && ($arrData['start'] == '' || $objParent->start > $arrData['start'])) ? $objParent->start : $arrData['start'];
            $this->arrData['stop'] = ($objParent->stop != '' && ($arrData['stop'] == '' || $objParent->stop < $arrData['stop'])) ? $objParent->stop : $arrData['stop'];

            return $this;
        }

        // Empty cache
        $this->objPrice             = false;
        $this->arrAttributes        = null;
        $this->arrVariantAttributes = null;
        $this->arrVariantIds        = null;
        $this->arrRelated           = [];

        // Must initialize product type to have attributes etc.
        if (($this->arrRelated['type'] = ProductType::findByPk($arrData['type'])) === null) {
            throw new \UnderflowException('Product type for product ID ' . $arrData['id'] . ' not found');
        }

        $this->strFormId = 'iso_product_' . $arrData['id'];

        // Remove attributes not in this product type
        foreach ($arrData as $attribute => $value) {
            if ((
                    !\in_array($attribute, $this->getType()->getAttributes(), true)
                    && !\in_array($attribute, $this->getType()->getVariantAttributes(), true)
                    && isset($GLOBALS['TL_DCA']['tl_iso_product']['fields'][$attribute]['attributes']['legend'])
                    && $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$attribute]['attributes']['legend'] != ''
                )
                || \in_array($attribute, Attribute::getVariantOptionFields(), true)
            ) {
                unset($arrData[$attribute]);
            }
        }

        return parent::setRow($arrData);
    }

    /**
     * Prevent reload of the database record
     * We would need to fetch parent data etc. again, pretty useless
     *
     * @param array $arrData
     *
     * @return $this
     */
    public function mergeRow(array $arrData)
    {
        // do not allow to reset the whole record
        if (isset($arrData['id'])) {
            return $this;
        }

        return parent::mergeRow($arrData);
    }

    /**
     * In a variant, only variant and non-inherited fields can be marked as modified
     *
     * @param string $strKey
     */
    public function markModified($strKey)
    {
        if ($this->isVariant()) {
            $arrAttributes = array_diff(
                $this->getType()->getVariantAttributes(),
                $this->getInheritedFields(),
                Attribute::getCustomerDefinedFields()
            );
        } else {
            $arrAttributes = array_diff($this->getType()->getAttributes(), Attribute::getCustomerDefinedFields());
        }

        if (!\in_array($strKey, $arrAttributes, true)
            && '' !== (string) ($GLOBALS['TL_DCA'][static::$strTable]['fields'][$strKey]['attributes']['legend'] ?? '')
        ) {
            return;
        }

        parent::markModified($strKey);
    }

    /**
     * Generate url
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function generateUrl(PageModel $objJumpTo = null/*, bool $absolute = false*/)
    {
        $absolute = false;

        if (func_num_args() >= 2) {
            $absolute = (bool) func_get_arg(1);
        }

        if (null === $objJumpTo) {
            global $objPage;
            global $objIsotopeListPage;

            $objJumpTo = $objIsotopeListPage ?: $objPage;
        }

        if (!$objJumpTo instanceof PageModel) {
            return '';
        }

        $strParams = '';

        if ($objJumpTo->iso_readerMode !== 'none') {
            $strParams = '/'.($this->arrData['alias'] ?: $this->getProductId());

            if (!$GLOBALS['TL_CONFIG']['useAutoItem'] || !\in_array('product', $GLOBALS['TL_AUTO_ITEM'], true)) {
                $strParams = '/product'.$strParams;
            }
        }

        $url = $absolute ? $objJumpTo->getAbsoluteUrl($strParams) : $objJumpTo->getFrontendUrl($strParams);

        return Url::addQueryString(http_build_query($this->getOptions()), $url);
    }

    /**
     * Return array of inherited attributes
     *
     * @return array
     */
    protected function getInheritedFields()
    {
        // Not a variant, no inherited fields
        if (!$this->isVariant()) {
            return array();
        }

        return array_merge(StringUtil::deserialize($this->arrData['inherit'], true), Attribute::getInheritFields());
    }

    private function getCollectionItem()
    {
        if (Input::get('collection_item') > 0) {
            $item = ProductCollectionItem::findByPk(Input::get('collection_item'));

            if (null !== $item
                && $item->hasProduct()
                && $item->getProduct()->getProductId() == $this->getProductId()
            ) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Load default values from URL
     */
    private function getOptionsDefaults()
    {
        if (\is_array($this->arrDefaults)) {
            return $this->arrDefaults;
        }

        $this->arrDefaults = array();

        if (($item = $this->getCollectionItem()) !== null) {
            $this->arrDefaults = $item->getOptions();
        } else {
            foreach ($_GET as $k => $v) {
                $this->arrDefaults[$k] = Input::get($k);
            }
        }

        return $this->arrDefaults;
    }
}
