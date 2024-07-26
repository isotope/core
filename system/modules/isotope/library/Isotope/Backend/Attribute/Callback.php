<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\Attribute;

use Ausi\SlugGenerator\SlugOptions;
use Contao\Backend;
use Contao\Controller;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\Database;
use Contao\DataContainer;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Contao\TemplateLoader;
use Contao\Widget;
use Isotope\Model\Attribute;
use Isotope\Model\AttributeOption;

class Callback extends Backend
{
    public function onLoad($dc)
    {
        $act = Input::get('act');

        // Hide the field in editAll & overrideAll mode (Thanks to Yanick Witschi)
        if ('editAll' === $act || 'overrideAll' === $act) {
            $GLOBALS['TL_DCA']['tl_iso_attribute']['fields']['field_name']['eval']['doNotShow'] = true;
        } elseif ($dc->id) {
            $objAttribute = Database::getInstance()->execute("SELECT * FROM tl_iso_attribute WHERE id={$dc->id}");

            if ($objAttribute->field_name != '') {
                $GLOBALS['TL_DCA']['tl_iso_attribute']['fields']['field_name']['eval']['disabled']  = true;
                $GLOBALS['TL_DCA']['tl_iso_attribute']['fields']['field_name']['eval']['mandatory'] = false;
            }

            if ($objAttribute->customer_defined || $objAttribute->variant_option) {
                $pm = PaletteManipulator::create()
                    ->addLegend('template_legend', '', PaletteManipulator::POSITION_APPEND)
                    ->addField('customTpl', 'template_legend', PaletteManipulator::POSITION_APPEND)
                ;

                foreach ($GLOBALS['TL_DCA']['tl_iso_attribute']['palettes'] as $k => $v) {
                    if (!\is_array($v)) {
                        $pm->applyToPalette($k, 'tl_iso_attribute');
                    }
                }
            }
        }
    }

    /**
     * Show price column in dcaWizard if attribute is not a variant option
     *
     * @return string
     */
    public function initializeTableOptions(Widget $objWidget)
    {
        /** @var Attribute $objAttribute */

        if ('iso_products' === Input::get('do')) {
            $objAttribute = Attribute::findByFieldName($objWidget->name);
        } else {
            $objAttribute = Attribute::findByPk(Input::get('id'));
        }

        if (null !== $objAttribute && !$objAttribute->isVariantOption()) {
            $objWidget->fields = array_merge($objWidget->fields, array('price'));
        }

        return AttributeOption::getTable();
    }

    /**
     * Make sure the system columns are not added as attribute
     *
     * @param mixed $varValue
     *
     * @return mixed
     * @throws \Exception
     */
    public function validateFieldName($varValue)
    {
        Controller::loadDataContainer('tl_iso_product');

        $varValue = System::getContainer()->get('contao.slug')->generate(
            StringUtil::prepareSlug($varValue),
            (new SlugOptions())->setValidChars('A-Za-z0-9_')->setLocale($GLOBALS['TL_LANGUAGE']),
        );

        if (isset($GLOBALS['TL_DCA']['tl_iso_product']['fields'][$varValue])
            && $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$varValue]['attributes']['systemColumn']
        ) {
            throw new \InvalidArgumentException(sprintf($GLOBALS['TL_LANG']['ERR']['systemColumn'], $varValue));
        }

        $platform = System::getContainer()->get('database_connection')->getDatabasePlatform();
        $keywords = $platform->getReservedKeywordsList();

        if ($keywords->isKeyword($varValue)) {
            throw new \InvalidArgumentException(sprintf($GLOBALS['TL_LANG']['ERR']['systemColumn'], $varValue));
        }

        return $varValue;
    }

    /**
     * Alter attribute columns in tl_iso_product table
     *
     * @param DataContainer $dc
     *
     * @deprecated Deprecated since Isotope 2.4.4, use DatabaseUpdate::updateDatabase().
     */
    public function updateDatabase($dc)
    {
        $callback = new DatabaseUpdate();
        $callback->updateDatabase($dc);
    }

    /**
     * Return an array of select-attributes
     * @param object
     * @return array
     */
    public function getConditionFields($dc)
    {
        Controller::loadDataContainer('tl_iso_product');
        $arrFields = array();

        foreach ($GLOBALS['TL_DCA']['tl_iso_product']['fields'] as $field => $arrData) {
            if ('select' === $arrData['inputType']
                || ('conditionalselect' === $arrData['inputType'] && $field != $dc->activeRecord->field_name)
            ) {
                $arrFields[$field] = \strlen($arrData['label'][0]) ? $arrData['label'][0] : $field;
            }
        }

        return $arrFields;
    }

    /**
     * Return a list of available rte config files
     *
     * @return array
     */
    public function getRTE()
    {
        $options = array();

        foreach (preg_grep('/^be_tiny/', array_keys(TemplateLoader::getFiles())) as $template) {
            $options[] = substr($template, 3);
        }

        return $options;
    }

    /**
     * Validate table and field of foreignKey
     *
     * @param mixed $varValue
     *
     * @return mixed
     */
    public function validateForeignKey($varValue)
    {
        if ($varValue != '') {
            $arrLines = StringUtil::trimsplit('@\r\n|\n|\r@', $varValue);

            foreach ($arrLines as $foreignKey) {
                if (empty($foreignKey) || strpos($foreignKey, '#') === 0) {
                    continue;
                }

                if (preg_match('/^([a-z]{2}(-[A-Z]{2})?)=(.+)$/', $foreignKey, $matches)) {
                    $foreignKey = $matches[3];
                }

                [$strTable, $strField] = explode('.', $foreignKey, 2);
                Database::getInstance()->execute("SELECT $strField FROM $strTable");
            }
        }

        return $varValue;
    }

    /**
     * To enable date picker, the rgxp must be date, time or datim
     *
     * @param mixed  $varValue
     * @param object $dc
     *
     * @return mixed
     *
     * @throws \UnexpectedValueException if rgxp is not valid for a datepicker
     */
    public function validateDatepicker($varValue, $dc)
    {
        if ($varValue && !\in_array($dc->activeRecord->rgxp, ['date', 'time', 'datim'], true)) {
            throw new \UnexpectedValueException($GLOBALS['TL_LANG']['ERR']['datepickerRgxp']);
        }

        return $varValue;
    }

    public function getAttributeTemplates(DataContainer $dc): array
    {
        if ('overrideAll' === Input::get('act')) {
            return Controller::getTemplateGroup('form_');
        }

        $default = 'form_' . $dc->activeRecord->type;

        // Backwards compatibility
        if ('text' === $dc->activeRecord->type) {
            $default = 'form_textfield';
        }

        $arrTemplates = Controller::getTemplateGroup('form_' . $dc->activeRecord->type . '_', array(), $default);

        // Backwards compatibility
        if ('text' === $dc->activeRecord->type) {
            $arrTemplates += Controller::getTemplateGroup('form_textfield_');
        }

        return $arrTemplates;
    }
}
