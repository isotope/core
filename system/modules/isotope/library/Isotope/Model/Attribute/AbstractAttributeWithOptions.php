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

namespace Isotope\Model\Attribute;

use Isotope\Interfaces\IsotopeAttributeForVariants;
use Isotope\Interfaces\IsotopeAttributeWithOptions;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Model\Attribute;
use Isotope\Model\AttributeOption;
use Isotope\Model\Product;
use Isotope\Translation;

abstract class AbstractAttributeWithOptions extends Attribute implements IsotopeAttributeWithOptions
{
    /**
     * Cache product options for attribute
     * "false" as long as the cache is not built
     * @type \Isotope\Collection\AttributeOption|array
     */
    protected $varOptionsCache = false;

    /**
     * Return true if attribute can have prices
     *
     * @return bool
     */
    public function canHavePrices()
    {
        if ($this instanceof IsotopeAttributeForVariants && $this->isVariantOption()) {
            return false;
        }

        return in_array($this->field_name, Attribute::getPricedFields());
    }

    /**
     * Get options of attribute from database
     *
     * @param IsotopeProduct $objProduct
     *
     * @return array|mixed
     */
    public function getOptionsForWidget(IsotopeProduct $objProduct = null)
    {
        $arrOptions = array();

        switch ($this->optionsSource) {

            // @deprecated remove in Isotope 3.0
            case 'attribute':
                $options = deserialize($this->options);

                if (!empty($options) && is_array($options)) {

                    // Build for a frontend widget
                    if ($this->isCustomerDefined()) {
                        foreach ($options as $option) {
                            $option['label'] = Translation::get($option['label']);

                            $arrOptions[] = $option;
                        }
                    }

                    // Build for a backend widget
                    else {
                        $group = '';

                        foreach ($options as $option) {
                            $option['label'] = Translation::get($option['label']);

                            if ($option['group']) {
                                $group = $option['label'];
                                continue;
                            }

                            if ($group != '') {
                                $arrOptions[$group][] = $option;
                            } else {
                                $arrOptions[] = $option;
                            }
                        }
                    }
                }
                break;

            case 'table':
                $objOptions = $this->getOptionsFromManager();

                if (null === $objOptions) {
                    return array();

                } elseif ($this->isCustomerDefined()) {
                    return $objOptions->getArrayForFrontendWidget($objProduct);

                } else {
                    return $objOptions->getArrayForBackendWidget();
                }
                break;

            case 'product':
                if (TL_MODE == 'FE' && !($objProduct instanceof IsotopeProduct)) {
                    throw new \InvalidArgumentException('Must pass IsotopeProduct to Attribute::getOptions if optionsSource is "product"');
                }

                $objOptions = $this->getOptionsFromManager($objProduct);

                if (null === $objOptions) {
                    return array();

                } else {
                    return $objOptions->getArrayForFrontendWidget($objProduct);
                }

                break;

            default:
                throw new \UnexpectedValueException('Invalid options source "'.$this->optionsSource.'" for '.static::$strTable.'.'.$this->field_name);
        }

        return $arrOptions;
    }

    /**
     * Get AttributeOption models for current attribute
     *
     * @param IsotopeProduct $objProduct
     *
     * @return \Isotope\Collection\AttributeOption
     */
    public function getOptionsFromManager(IsotopeProduct $objProduct = null)
    {
        switch ($this->optionsSource) {

            case 'table':
                if (false === $this->varOptionsCache) {
                    $this->varOptionsCache = AttributeOption::findByAttribute($this);
                }

                return $this->varOptionsCache;

            case 'product':
                /** @type IsotopeProduct|Product $objProduct */
                if (TL_MODE == 'FE' && !($objProduct instanceof IsotopeProduct)) {
                    throw new \InvalidArgumentException('Must pass IsotopeProduct to Attribute::getOptionsFromManager if optionsSource is "product"');

                } elseif (!is_array($this->varOptionsCache) || array_key_exists($objProduct->id, $this->varOptionsCache)) {
                    $this->varOptionsCache[$objProduct->id] = AttributeOption::findByProductAndAttribute($objProduct, $this);
                }

                return $this->varOptionsCache[$objProduct->id];

            default:
                throw new \UnexpectedValueException(static::$strTable.'.'.$this->field_name . ' does not use options manager');
        }
    }

    /**
     * Get options for the frontend product filter widget
     *
     * @param array $arrValues
     *
     * @return array
     */
    public function getOptionsForProductFilter(array $arrValues)
    {
        switch ($this->optionsSource) {

            // @deprecated remove in Isotope 3.0
            case 'attribute':
                $arrOptions = array();
                $options = deserialize($this->options);

                if (!empty($options) && is_array($options)) {
                    foreach ($options as $option) {
                        if (in_array($option['value'], $arrValues)) {
                            $option['label'] = Translation::get($option['label']);
                            $arrOptions[] = $option;
                        }
                    }
                }

                return $arrOptions;
                break;

            case 'table':
            case 'product':
                /** @type \Isotope\Collection\AttributeOption $objOptions */
                $objOptions = AttributeOption::findPublishedByIds($arrValues);

                return (null === $objOptions) ? array() : $objOptions->getArrayForFrontendWidget(null, false);
                break;

            default:
                throw new \UnexpectedValueException('Invalid options source "'.$this->optionsSource.'" for '.static::$strTable.'.'.$this->field_name);
        }
    }

    /**
     * Adjust DCA field for this attribute
     *
     * @param array $arrData
     */
    public function saveToDCA(array &$arrData)
    {
        parent::saveToDCA($arrData);

        if (TL_MODE == 'BE') {

            if ($this->be_filter && \Input::get('act') == '') {
                $arrData['fields'][$this->field_name]['foreignKey'] = 'tl_iso_attribute_option.label';
            }

            if ($this->isCustomerDefined() && $this->optionsSource == 'product') {

                \Controller::loadDataContainer(static::$strTable);
                \System::loadLanguageFile(static::$strTable);

                $arrField = array_merge(
                    $arrData['fields'][$this->field_name],
                    $GLOBALS['TL_DCA'][static::$strTable]['fields']['optionsTable']
                );

                $arrField['label']                 = $arrData['fields'][$this->field_name]['label'];
                $arrField['attributes']['dynamic'] = true;

                $arrData['fields'][$this->field_name] = $arrField;
            }
        }
    }
} 