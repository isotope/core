<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\Module;

use Isotope\Interfaces\IsotopeAttributeWithRange;

class RangeFields extends \Backend
{
    /**
     * Gets the multi column wizard configuration for the cumulative filter fields.
     *
     * @return array
     */
    public function getColumns()
    {
        return [
            'attribute' => [
                'label'            => &$GLOBALS['TL_LANG']['tl_module']['iso_rangeFields']['attribute'],
                'inputType'        => 'select',
                'options_callback' => ['Isotope\Backend\Module\RangeFields', 'getAttributes'],
                'eval'             => [
                    'mandatory'          => true,
                    'includeBlankOption' => true,
                    'style'              => 'width:300px'
                ],
            ],
            'min' => [
                'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_rangeFields']['min'],
                'inputType' => 'text',
                'eval'      => ['mandatory' => true, 'style' => 'width:100px'],
            ],
            'max' => [
                'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_rangeFields']['max'],
                'inputType' => 'text',
                'eval'      => ['mandatory' => true, 'style' => 'width:100px'],
            ],
            'step' => [
                'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_rangeFields']['step'],
                'inputType' => 'text',
                'eval'      => ['rgxp' => 'natural', 'style' => 'width:50px'],
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
        \Controller::loadDataContainer('tl_iso_product');
        \System::loadLanguageFile('tl_iso_product');

        $arrAttributes = array();

        foreach ($GLOBALS['TL_DCA']['tl_iso_product']['fields'] as $field => $arrData) {
            $attribute = $GLOBALS['TL_DCA']['tl_iso_product']['attributes'][$field];
            if ($attribute instanceof IsotopeAttributeWithRange && $attribute->allowRangeFilter()) {
                $arrAttributes[$field] = (strlen($arrData['label'][0]) ? $arrData['label'][0] : $field);
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
     *
     * @throws \InvalidArgumentException If an attribute choice is empty
     * @throws \InvalidArgumentException If the same attribute is added multiple times
     */
    public function validateConfiguration($value)
    {
        return $value;
    }
}
