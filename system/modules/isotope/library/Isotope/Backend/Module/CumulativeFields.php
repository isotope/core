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

namespace Isotope\Backend\Module;

class CumulativeFields extends \Backend
{
    /**
     * Gets the multi column wizard configuration for the cumulative filter fields.
     *
     * @return array
     */
    public function getColumns()
    {
        return array(
            'attribute' => array(
                'label'            => &$GLOBALS['TL_LANG']['tl_module']['iso_cumulativeFields']['attribute'],
                'inputType'        => 'select',
                'options_callback' => array(
                    'Isotope\Backend\Module\CumulativeFields',
                    'getAttributes'
                ),
                'eval'             => array(
                    'mandatory'          => true,
                    'includeBlankOption' => true,
                    'style'              => 'width:300px'
                ),
            ),
            'queryType' => array(
                'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_cumulativeFields']['queryType'],
                'default'   => 'and',
                'inputType' => 'select',
                'options'   => array('and', 'or'),
                'reference' => &$GLOBALS['TL_LANG']['tl_module']['iso_cumulativeFields']['queryType'],
                'eval'      => array('style' => 'width:100px'),
            ),
            'matchCount' => array(
                'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_cumulativeFields']['matchCount'],
                'default'   => 'and',
                'inputType' => 'select',
                'options'   => array('none', 'all', 'new'),
                'reference' => &$GLOBALS['TL_LANG']['tl_module']['iso_cumulativeFields']['matchCount'],
                'eval'      => array('style' => 'width:100px'),
            ),
        );
    }

    /**
     * Gets attributes for the cumulative filter including their type (single or multiple choice).
     *
     * @return array
     */
    public function getAttributes()
    {
        $this->loadDataContainer('tl_iso_product');
        \System::loadLanguageFile('tl_iso_product');

        $arrAttributes = array();

        foreach ($GLOBALS['TL_DCA']['tl_iso_product']['fields'] as $field => $arrData) {
            if ($arrData['attributes']['fe_filter']) {
                $arrAttributes[$field] = sprintf(
                    '%s (%s)',
                    (strlen($arrData['label'][0]) ? $arrData['label'][0] : $field),
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
        $value = deserialize($value);

        if (!is_array($value)) {
            return '';
        }

        $attributes = array();

        foreach ($value as $option) {
            if ($option['attribute'] == '') {
                throw new \InvalidArgumentException(
                    sprintf(
                        $GLOBALS['TL_LANG']['ERR']['mandatory'],
                        $GLOBALS['TL_LANG']['tl_module']['iso_cumulativeFields']['attribute'][0]
                    )
                );
            }

            if (in_array($option['attribute'], $attributes)) {
                throw new \InvalidArgumentException($GLOBALS['TL_LANG']['ERR']['cumulativeDuplicateAttribute']);
            }

            $attributes[] = $option['attribute'];
        }

        return serialize($value);
    }
}
