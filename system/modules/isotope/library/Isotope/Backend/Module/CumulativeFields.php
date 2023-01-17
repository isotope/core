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

class CumulativeFields extends Backend
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
                'label'            => &$GLOBALS['TL_LANG']['tl_module']['iso_cumulativeFields']['attribute'],
                'inputType'        => 'select',
                'options_callback' => ['Isotope\Backend\Module\CumulativeFields', 'getAttributes'],
                'eval'             => [
                    'mandatory'          => true,
                    'includeBlankOption' => true,
                    'style'              => 'width:300px'
                ],
            ],
            'queryType' => [
                'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_cumulativeFields']['queryType'],
                'default'   => 'and',
                'inputType' => 'select',
                'options'   => ['and', 'or'],
                'reference' => &$GLOBALS['TL_LANG']['tl_module']['iso_cumulativeFields']['queryType'],
                'eval'      => ['style' => 'width:100px'],
            ],
            'matchCount' => [
                'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_cumulativeFields']['matchCount'],
                'default'   => 'and',
                'inputType' => 'select',
                'options'   => ['none', 'all', 'new'],
                'reference' => &$GLOBALS['TL_LANG']['tl_module']['iso_cumulativeFields']['matchCount'],
                'eval'      => ['style' => 'width:150px'],
            ],
        ];
    }

    /**
     * Gets attributes for the cumulative filter including their type (single or multiple choice).
     *
     * @return array
     */
    public function getAttributes()
    {
        Controller::loadDataContainer('tl_iso_product');
        System::loadLanguageFile('tl_iso_product');

        $arrAttributes = [];

        foreach ($GLOBALS['TL_DCA']['tl_iso_product']['fields'] as $field => $arrData) {
            if ($arrData['attributes']['fe_filter'] ?? false) {
                $arrAttributes[$field] = sprintf(
                    '%s (%s)',
                    (\strlen($arrData['label'][0]) ? $arrData['label'][0] : $field),
                    ($arrData['eval']['multiple'] ? 'multiple choice' : 'single choice')
                );
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
        $value = StringUtil::deserialize($value);

        if (!\is_array($value)) {
            return '';
        }

        $attributes = [];

        foreach ($value as $option) {
            if ($option['attribute'] == '') {
                throw new \InvalidArgumentException(
                    sprintf(
                        $GLOBALS['TL_LANG']['ERR']['mandatory'],
                        $GLOBALS['TL_LANG']['tl_module']['iso_cumulativeFields']['attribute'][0]
                    )
                );
            }

            if (\in_array($option['attribute'], $attributes, true)) {
                throw new \InvalidArgumentException($GLOBALS['TL_LANG']['ERR']['cumulativeDuplicateAttribute']);
            }

            $attributes[] = $option['attribute'];
        }

        return serialize($value);
    }
}
