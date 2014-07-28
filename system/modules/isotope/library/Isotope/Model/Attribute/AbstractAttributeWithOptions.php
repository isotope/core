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

                /** @type \Isotope\Collection\AttributeOption $objOptions */
                $objOptions = AttributeOption::findByAttribute($this);

                if (null === $objOptions) {
                    return array();

                } else if ($this->isCustomerDefined()) {
                    return $objOptions->getArrayForFrontendWidget();

                } else {
                    return $objOptions->getArrayForBackendWidget();
                }
                break;

            default:
                throw new \UnexpectedValueException('Invalid options source "'.$this->optionsSource.'" for '.static::$strTable.'.'.$this->field_name);
        }

        return $arrOptions;
    }
} 