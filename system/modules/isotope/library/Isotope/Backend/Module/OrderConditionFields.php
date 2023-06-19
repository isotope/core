<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\Module;

class OrderConditionFields extends \Backend
{
    /**
     * Gets the multi column wizard configuration for the cumulative filter fields.
     *
     * @return array
     */
    public function getColumns()
    {
        return [
            'form' => [
                'label' => &$GLOBALS['TL_LANG']['tl_module']['iso_order_conditions']['form'],
                'exclude' => true,
                'inputType' => 'select',
                'foreignKey' => 'tl_form.title',
                'eval' => [
                    'includeBlankOption' => true,
                    'style' => 'width:300px',
                    'chosen' => true,
                    'columnPos' => 'checkout',
                ],
            ],
            'step' => [
                'label' => &$GLOBALS['TL_LANG']['tl_module']['iso_order_conditions']['step'],
                'inputType' => 'select',
                'options_callback' => ['Isotope\Backend\Module\OrderConditionFields', 'getSteps'],
                'eval' => [
                    'decodeEntities' => true,
                    'includeBlankOption' => true,
                    'style' => 'width:300px',
                    'columnPos' => 'checkout',
                ],
            ],
            'position' => [
                'label' => &$GLOBALS['TL_LANG']['tl_module']['iso_order_conditions']['position'],
                'default' => 'before',
                'inputType' => 'select',
                'options' => ['before', 'after'],
                'reference' => &$GLOBALS['TL_LANG']['tl_module']['iso_order_conditions']['position'],
                'eval' => [
                    'style' => 'width:300px',
                    'columnPos' => 'checkout',
                ],
            ],
            'product_types' => [
                'label' => &$GLOBALS['TL_LANG']['tl_module']['iso_order_conditions']['product_types'],
                'inputType' => 'select',
                'foreignKey' => 'tl_iso_producttype.name',
                'eval' => [
                    'multiple' => true,
                    'style' => 'width:300px',
                    'columnPos' => 'product_type',
                ],
            ],
            'product_types_condition' => [
                'label' => &$GLOBALS['TL_LANG']['tl_module']['iso_order_conditions']['product_types_condition'],
                'inputType' => 'select',
                'options' => ['oneAvailable', 'allAvailable', 'onlyAvailable'],
                'reference' => &$GLOBALS['TL_LANG']['tl_module']['iso_order_conditions']['product_types_condition'],
                'eval' => [
                    'style' => 'width:300px',
                    'columnPos' => 'product_type',
                ],
            ],
        ];
    }

    public function getSteps()
    {
        $options = [];

        foreach ($GLOBALS['ISO_CHECKOUTSTEP'] as $group => $steps) {
            $groupLabel = $GLOBALS['TL_LANG']['MSC']['checkout_'.$group];

            foreach ($steps as $step) {
                $options[$groupLabel][$step] = $GLOBALS['TL_LANG']['CHECKOUT'][$step] ?? substr($step, strrpos($step, '\\') + 1);
            }
        }

        return $options;
    }
}
