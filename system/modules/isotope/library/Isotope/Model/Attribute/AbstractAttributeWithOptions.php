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

use Isotope\Interfaces\IsotopeAttributeWithOptions;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Model\Attribute;
use Isotope\Model\AttributeOption;
use Isotope\Translation;

abstract class AbstractAttributeWithOptions extends Attribute implements IsotopeAttributeWithOptions
{

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
                $objOptions = AttributeOption::findByAttribute($this);

                if (null === $objOptions) {
                    return array();

                } else if ($this->isCustomerDefined()) {
                    return $objOptions->getArrayForFrontendWidget();

                } else {
                    return $objOptions->getArrayForBackendWidget();
                }
                break;

            case 'product':
                if (TL_MODE == 'FE' && !($objProduct instanceof IsotopeProduct)) {
                    throw new \InvalidArgumentException('Must pass IsotopeProduct to Attribute::getOptions if optionsSource is "product"');
                }

                $objOptions = AttributeOption::findByProductAndAttribute($objProduct, $this);

                if (null === $objOptions) {
                    return array();

                } else {
                    return $objOptions->getArrayForFrontendWidget();
                }

                break;

            default:
                throw new \UnexpectedValueException('Invalid options source "'.$this->optionsSource.'" for '.static::$strTable.'.'.$this->field_name);
        }

        return $arrOptions;
    }

    /**
     * Adjust DCA field for this attribute
     *
     * @param array $arrData
     */
    public function saveToDCA(array &$arrData)
    {
        parent::saveToDCA($arrData);

        if (TL_MODE == 'BE' && $this->isCustomerDefined() && $this->optionsSource == 'product') {

            \Haste\Haste::getInstance()->call('loadDataContainer', static::$strTable);
            \System::loadLanguageFile(static::$strTable);

            $arrData['fields'][$this->field_name] = array_merge($arrData['fields'][$this->field_name], $GLOBALS['TL_DCA'][static::$strTable]['fields']['optionsTable']);
            $arrData['fields'][$this->field_name]['attributes']['dynamic'] = true;
        }
    }
} 