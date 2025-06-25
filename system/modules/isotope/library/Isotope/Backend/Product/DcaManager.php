<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\Product;

use Contao\Backend;
use Isotope\CoreBundle\Attribute\OptionsAttributeInterface;
use Isotope\CoreBundle\Model\Attribute;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Model\Product;


class DcaManager extends Backend
{
    /**
     * Add options from attribute to DCA
     *
     * @param array  $arrData
     * @param object $objDca
     *
     * @return array
     */
    public function addOptionsFromAttribute($arrData, $objDca)
    {
        if ($arrData['strTable'] == Product::getTable()
            && ($arrData['optionsSource'] ?? '') != ''
            && 'foreignKey' !== $arrData['optionsSource']
        ) {

            /** @var OptionsAttributeInterface|Attribute $objAttribute */
            $objAttribute = Attribute::findByFieldName($arrData['strField']);

            if (null !== $objAttribute && $objAttribute instanceof OptionsAttributeInterface) {
                $arrData['options'] = ($objDca instanceof IsotopeProduct) ? $objAttribute->getOptionsForWidget($objDca) : $objAttribute->getOptionsForWidget();

                if (!empty($arrData['options'])) {
                    if ($arrData['includeBlankOption']) {
                        array_unshift($arrData['options'], array('value'=>'', 'label'=>($arrData['blankOptionLabel'] ?: '-')));
                    }

                    if (null !== ($arrData['default'] ?? null)) {
                        $arrDefault = array_filter(
                            $arrData['options'],
                            function (&$option) {
                                return (bool) $option['default'];
                            }
                        );

                        if (!empty($arrDefault)) {
                            array_walk(
                                $arrDefault,
                                function (&$value) {
                                    $value = $value['value'];
                                }
                            );

                            $arrData['value'] = ($objAttribute->multiple ? $arrDefault : $arrDefault[0]);
                        }
                    }
                }
            }
        }

        return $arrData;
    }
}
