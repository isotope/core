<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\ProductType;

use Contao\Backend;
use Contao\Controller;
use Contao\DataContainer;
use Contao\Image;
use Contao\StringUtil;
use Contao\System;
use Contao\Widget;
use Isotope\CoreBundle\Attribute\AttributeInterface;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Attribute\VariantAttributeInterface;
use Isotope\CoreBundle\Model\Attribute;
use Isotope\Model\Product;


class AttributeWizard extends Backend
{

    /**
     * Return list of MultiColumnWizard columns
     *
     * @param \MultiColumnWizard|object $objWidget
     *
     * @return array
     */
    public function getColumns($objWidget)
    {
        Controller::loadDataContainer(Product::getTable());

        $arrValues   = $objWidget->value;
        $blnVariants = $this->isVariants($objWidget->name);

        if (!empty($arrValues) && \is_array($arrValues)) {
            if ($blnVariants) {
                $arrFixed = $this->getVariantFixedFields($objWidget->dataContainer->activeRecord->class);
            } else {
                $arrFixed = $this->getFixedFields($objWidget->dataContainer->activeRecord->class);
            }

            foreach ($arrValues as $i => $attribute) {
                if (\in_array($attribute['name'], $arrFixed, true)) {
                    $objWidget->addDataToFieldAtIndex($i, 'enabled', array('eval' => array('disabled' => true)));
                }

                $objAttribute = System::getContainer()->get(AttributeRegistry::class)->get($attribute['name']);

                if ($objAttribute instanceof VariantAttributeInterface && $objAttribute->isVariantOption()) {
                    $objWidget->addDataToFieldAtIndex($i, 'mandatory', array('eval' => array('hideBody' => true)));
                }
            }
        }

        return array
        (
            'enabled'   => array
            (
                'inputType' => 'checkbox',
                'eval'      => array('hideHead' => true),
            ),
            'name'      => array
            (
                'input_field_callback' => array('Isotope\Backend\ProductType\AttributeWizard', 'getNextName'),
                'eval'                 => array('hideHead' => true, 'tl_class' => 'mcwUpdateFields'),
            ),
            'legend'    => array
            (
                'label'            => &$GLOBALS['TL_LANG']['tl_iso_producttype']['attributes']['legend'],
                'inputType'        => 'select',
                'options_callback' => array('Isotope\Backend\ProductType\AttributeWizard', 'getLegends'),
                'eval'             => array('style' => 'width:150px', 'class' => 'extendable'),
            ),
            'tl_class'  => array
            (
                'label'     => &$GLOBALS['TL_LANG']['tl_iso_producttype']['attributes']['tl_class'],
                'inputType' => 'text',
                'eval'      => array('style' => 'width:80px'),
            ),
            'mandatory' => array
            (
                'label'     => &$GLOBALS['TL_LANG']['tl_iso_producttype']['attributes']['mandatory'],
                'inputType' => 'select',
                'options'   => array('yes', 'no'),
                'reference' => &$GLOBALS['TL_LANG']['MSC'],
                'eval'      => array('style' => 'width:80px', 'includeBlankOption' => true, 'blankOptionLabel' => &$GLOBALS['TL_LANG']['tl_iso_producttype']['attributes']['default']),
            ),
            'edit'      => array
            (
                'input_field_callback' => array('Isotope\Backend\ProductType\AttributeWizard', 'getEditButton'),
                'eval'                 => array('hideHead' => true, 'tl_class' => 'mcwUpdateFields'),
            ),
        );
    }

    /**
     * For each call, return the name of the next attribute in the wizard (for input_field_callback)
     *
     * @param Widget|object $objWidget
     *
     * @return string
     */
    public function getNextName($objWidget)
    {
        static $arrValues;
        static $strWidget;
        static $i = 0;

        if ($objWidget->name !== $strWidget || empty($arrValues)) {
            $strWidget = $objWidget->name;
            $arrValues = $objWidget->value;
            $i         = 0;
        }

        $arrField = array_shift($arrValues);
        if (null === $arrField) {
            return '';
        }

        $strName  = $arrField['name'];
        $style = '';

        $objAttribute = System::getContainer()->get(AttributeRegistry::class)->get($strName);

        if (null !== $objAttribute && $objAttribute instanceof VariantAttributeInterface && $objAttribute->isVariantOption()) {
            $style = ';font-style:italic';
        }

        return sprintf(
            '<input type="hidden" name="%s[%s][name]" id="ctrl_%s_row%s_name" value="%s"><div style="width:300px%s">%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span></div>',
            $objWidget->name,
            $i,
            $objWidget->name,
            $i++,
            $strName,
            $style,
            $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$strName]['label'][0] ?? $strName,
            $strName
        );
    }

    public function getEditButton($objWidget): string
    {
        static $arrValues;
        static $strWidget;

        if ($objWidget->name !== $strWidget || empty($arrValues)) {
            $strWidget = $objWidget->name;
            $arrValues = $objWidget->value;

            System::loadLanguageFile('tl_iso_attribute');
        }

        $arrField = array_shift($arrValues);
        if (null === $arrField) {
            return '';
        }

        /** @var AttributeInterface|VariantAttributeInterface $objAttribute */
        $objAttribute = $GLOBALS['TL_DCA']['tl_iso_product']['attributes'][$arrField['name']] ?? null;

        if (!$objAttribute || !$objAttribute->id) {
            return Image::getHtml('edit_.svg');
        }

        $href = Backend::addToUrl('mod=attributes&table=tl_iso_attribute&id='.$objAttribute->id).'&popup=1';
        $title = sprintf($GLOBALS['TL_LANG']['tl_iso_attribute']['edit'][1], $objAttribute->id);

        return '<a href="' . StringUtil::specialcharsUrl($href) . '" title="' . StringUtil::specialchars($title) . '" onclick="Backend.openModalIframe({\'title\':\'' . StringUtil::specialchars(str_replace("'", "\\'", $title)) . '\',\'url\':this.href});return false">' . Image::getHtml('edit.svg', $title) . '</a>';
    }

    /**
     * Return list of default and widget legends
     *
     * @param object $objWidget The widget object
     *
     * @return array
     */
    public function getLegends($objWidget)
    {
        Controller::loadDataContainer(Attribute::getTable());
        System::loadLanguageFile(Product::getTable());

        $arrLegends = $GLOBALS['TL_DCA'][Attribute::getTable()]['fields']['legend']['options'];
        $arrLegends = array_intersect_key($GLOBALS['TL_LANG'][Product::getTable()], array_flip($arrLegends));

        $varValue = $objWidget->value;

        if (!empty($varValue) && \is_array($varValue)) {
            foreach ($varValue as $arrField) {
                if ($arrField['legend'] != '' && !isset($arrLegends[$arrField['legend']])) {
                    $arrLegends[$arrField['legend']] = $arrField['legend'];
                }
            }
        }

        return $arrLegends;
    }

    /**
     * Generate list of fields and add missing ones from DCA
     *
     * @param mixed          $varValue
     * @param DataContainer $dc
     *
     * @return array
     */
    public function load($varValue, $dc)
    {
        Controller::loadDataContainer('tl_iso_product');

        $arrDCA      = &$GLOBALS['TL_DCA']['tl_iso_product']['fields'];
        $arrFields   = array();
        $arrValues   = StringUtil::deserialize($varValue);
        $blnVariants = $this->isVariants($dc->field);

        if ($blnVariants) {
            $arrFixed = $this->getVariantFixedFields($dc->activeRecord->class);
        } else {
            $arrFixed = $this->getFixedFields($dc->activeRecord->class);
        }

        if (!\is_array($arrValues)) {
            $arrValues = array();
        }

        foreach ($arrValues as $arrField) {

            $strName = $arrField['name'];

            if ($strName == ''
                || !isset($arrDCA[$strName])
                || empty($arrDCA[$strName]['attributes']['legend'])
                || $this->isExcluded($strName, $blnVariants)
                || ($blnVariants && ($arrDCA[$strName]['attributes']['inherit'] ?? false))
                || (!$blnVariants && ($arrDCA[$strName]['attributes']['variant_option'] ?? false))
            ) {
                continue;
            }

            if (empty($arrField['legend'])) {
                $arrField['legend'] = $arrDCA[$arrField['name']]['attributes']['legend'] ?? '';
            }

            $arrFields[$arrField['name']] = $arrField;
        }

        foreach (array_diff_key($arrDCA, $arrFields) as $strName => $arrField) {

            if (!\is_array($arrField['attributes'] ?? null)
                || empty($arrField['attributes']['legend'])
                || $this->isExcluded($strName, $blnVariants)
                || ($blnVariants && ($arrField['attributes']['inherit'] ?? false))
                || (!$blnVariants && ($arrField['attributes']['variant_option'] ?? false))
            ) {
                continue;
            }

            $arrFields[$strName] = array(
                'enabled' => \in_array($strName, $arrFixed, true) ? '1' : '',
                'name'    => $strName,
                'legend'  => $arrField['attributes']['legend'],
            );
        }

        return array_values($arrFields);
    }

    /**
     * save_callback to sort attribute wizard fields by legend
     *
     * @param mixed  $varValue The widget value
     * @param object $dc       The DataContainer object
     *
     * @return string
     */
    public function save($varValue, $dc)
    {
        $arrLegends  = array();
        $arrFields   = StringUtil::deserialize($varValue);
        $blnVariants = $this->isVariants($dc->field);

        if (empty($arrFields) || !\is_array($arrFields)) {
            return $varValue;
        }

        if ($blnVariants) {
            $arrFixed = $this->getVariantFixedFields($dc->activeRecord->class);
        } else {
            $arrFixed = $this->getFixedFields($dc->activeRecord->class);
        }

        foreach ($arrFields as $k => $arrField) {
            if (\in_array($arrField['name'] ?? null, $arrFixed, true)) {
                $arrFields[$k]['enabled'] = '1';
            }

            if (!\in_array($arrField['legend'] ?? null, $arrLegends, true)) {
                $arrLegends[] = $arrField['legend'];
            }
        }

        uksort($arrFields, function ($a, $b) use ($arrFields, $arrLegends) {
            if ($arrFields[$a]['enabled'] && !$arrFields[$b]['enabled']) {
                return -1;
            }

            if ($arrFields[$b]['enabled'] && !$arrFields[$a]['enabled']) {
                return 1;
            }

            if ($arrFields[$a]['legend'] === $arrFields[$b]['legend']) {
                return ($a > $b) ? +1 : -1;
            }

            $posA = array_search($arrFields[$a]['legend'], $arrLegends, true);
            $posB = array_search($arrFields[$b]['legend'], $arrLegends, true);

            return ($posA > $posB) ? +1 : -1;
        });

        $arrValues = array();
        foreach (array_values($arrFields) as $pos => $arrConfig) {
            $arrConfig['position']         = $pos;
            $arrValues[$arrConfig['name']] = $arrConfig;
        }

        return serialize($arrValues);
    }

    /**
     * Returns whether we're currently handling variant attributes.
     *
     * @param string $widgetName
     *
     * @return bool
     */
    private function isVariants($widgetName)
    {
        return 'attributes' !== $widgetName;
    }

    /**
     * Returns whether an attribute is excluded.
     *
     * @param string $attributeName
     * @param bool   $forVariants
     *
     * @return bool
     */
    private function isExcluded($attributeName, $forVariants)
    {
        $excludedFields = $forVariants ? $this->getVariantExcludedFields() : $this->getExcludedFields();

        return \in_array($attributeName, $excludedFields, true);
    }

    /**
     * Return list of fixed fields
     * Fixed fields cannot be disabled in product type config
     *
     * @param string|null $class A product type class to make a field fixed for certain product types only.
     *
     * @return list<string>
     */
    private function getFixedFields(string|null $class = null): array
    {
        Controller::loadDataContainer('tl_iso_product');

        $arrFields = array();
        $arrDCA    = &$GLOBALS['TL_DCA']['tl_iso_product']['fields'];

        foreach ($arrDCA as $field => $config) {
            $fixed = ($config['attributes']['fixed'] ?? null);
            $isArray = \is_array($fixed);

            if ((!$isArray && $fixed) || (null !== $class && $isArray && \in_array($class, $fixed, true))) {
                $arrFields[] = $field;
            }
        }

        return $arrFields;
    }

    /**
     * Return list of variant fixed fields
     * Fixed fields cannot be disabled in product type config
     *
     * @param string|null $class A product type class to make a field fixed for certain product types only.
     *
     * @return list<string>
     */
    private function getVariantFixedFields(string|null $class = null): array
    {
        Controller::loadDataContainer('tl_iso_product');

        $arrFields = array();
        $arrDCA = &$GLOBALS['TL_DCA']['tl_iso_product']['fields'];

        foreach ($arrDCA as $field => $config) {
            $fixed   = $config['attributes']['variant_fixed'] ?? null;
            $isArray = \is_array($fixed);

            if ((!$isArray && $fixed) || (null !== $class && $isArray && \in_array($class, $fixed, true))) {
                $arrFields[] = $field;
            }
        }

        return $arrFields;
    }

    /**
     * Return list of excluded fields.
     * Excluded fields cannot be enabled in product type config.
     *
     * @return list<string>
     */
    private function getExcludedFields(): array
    {
        static $arrFields;

        if (null === $arrFields) {
            Controller::loadDataContainer('tl_iso_product');

            $arrFields = array();
            $arrDCA    = &$GLOBALS['TL_DCA']['tl_iso_product']['fields'];

            foreach ($arrDCA as $field => $config) {
                if (($config['attributes']['excluded'] ?? false)) {
                    $arrFields[] = $field;
                }
            }
        }

        return $arrFields;
    }

    /**
     * Return list of variant excluded fields.
     * Excluded fields cannot be disabled in product type config.
     *
     * @return list<string>
     */
    private function getVariantExcludedFields(): array
    {
        static $arrFields;

        if (null === $arrFields) {
            Controller::loadDataContainer('tl_iso_product');

            $arrFields = array();
            $arrDCA    = &$GLOBALS['TL_DCA']['tl_iso_product']['fields'];

            foreach ($arrDCA as $field => $config) {
                if ($config['attributes']['variant_excluded'] ?? false) {
                    $arrFields[] = $field;
                }
            }
        }

        return $arrFields;
    }
}
