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

use Contao\Backend;
use Contao\Controller;
use Contao\StringUtil;
use Contao\System;
use Isotope\Interfaces\IsotopeAttributeWithRange;
use MenAtWork\MultiColumnWizardBundle\Contao\Widgets\MultiColumnWizard;

class RangeFields extends Backend
{
    /**
     * Gets the multi column wizard configuration for the range filter fields.
     *
     * @return array
     */
    public function getColumns()
    {
        return [
            'mode' => [
                'label' => &$GLOBALS['TL_LANG']['tl_module']['iso_rangeFields']['mode'],
                'inputType' => 'select',
                'options' => ['range', 'min', 'max', 'fields'],
                'reference' => &$GLOBALS['TL_LANG']['tl_module']['iso_rangeFieldsMode'],
                'eval' => [
                    'mandatory' => true,
                    'style' => 'width:100%',
                ],
            ],
            'attribute' => [
                'label' => &$GLOBALS['TL_LANG']['tl_module']['iso_rangeFields']['attribute'],
                'inputType' => 'select',
                'options_callback' => ['Isotope\Backend\Module\RangeFields', 'getAttributes'],
                'eval' => [
                    'mandatory' => true,
                    'includeBlankOption' => true,
                    'style' => 'width:100%',
                ],
            ],
            'attribute_max' => [
                'label' => &$GLOBALS['TL_LANG']['tl_module']['iso_rangeFields']['attribute_max'],
                'inputType' => 'select',
                'options_callback' => ['Isotope\Backend\Module\RangeFields', 'getAttributes'],
                'eval' => [
                    'mandatory' => false,
                    'includeBlankOption' => true,
                    'blankOptionLabel' => &$GLOBALS['TL_LANG']['tl_module']['iso_rangeFields']['attribute_max_blank'],
                    'style' => 'width:100%',
                ],
            ],
            'min' => [
                'label' => &$GLOBALS['TL_LANG']['tl_module']['iso_rangeFields']['min'],
                'inputType' => 'text',
                'eval' => ['mandatory' => true, 'style' => 'width:100%'],
            ],
            'max' => [
                'label' => &$GLOBALS['TL_LANG']['tl_module']['iso_rangeFields']['max'],
                'inputType' => 'text',
                'eval' => ['mandatory' => true, 'style' => 'width:100%'],
            ],
            'step' => [
                'label' => &$GLOBALS['TL_LANG']['tl_module']['iso_rangeFields']['step'],
                'inputType' => 'text',
                'eval' => ['rgxp' => 'natural', 'style' => 'width:100%'],
            ],
        ];
    }

    /**
     * Gets attributes for the range filter including their type (single or multiple choice).
     *
     * @return array
     */
    public function getAttributes()
    {
        Controller::loadDataContainer('tl_iso_product');
        System::loadLanguageFile('tl_iso_product');

        $arrAttributes = [];

        foreach ($GLOBALS['TL_DCA']['tl_iso_product']['fields'] as $field => $arrData) {
            $attribute = $GLOBALS['TL_DCA']['tl_iso_product']['attributes'][$field];
            if ($attribute instanceof IsotopeAttributeWithRange && $attribute->allowRangeFilter()) {
                $arrAttributes[$field] = (\strlen($arrData['label'][0]) ? $arrData['label'][0] : $field);
            }
        }

        return $arrAttributes;
    }

    /**
     * Validates that the cumulative filter configuration is correct.
     *
     * @param mixed $value
     *
     * @return string
     */
    public function validateConfiguration($value)
    {
        $configs = StringUtil::deserialize($value);

        foreach ($configs as $i => &$config) {
            if ('fields' !== $config['mode']) {
                $config['attribute_max'] = '';
                continue;
            }

            if (empty($config['attribute_max'])) {
                throw new \InvalidArgumentException(sprintf($GLOBALS['TL_LANG']['ERR']['rangeAttributeMax'], $i+1));
            }
        }

        return serialize($configs);
    }
}
