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

namespace Isotope\Backend\Attribute;

use Isotope\Model\Attribute;


class OptionsWizard extends \Backend
{

    /**
     * Return list of MultiColumnWizard columns
     * @param   MultiColumnWizard
     * @return  array
     */
    public function getColumns($objWidget)
    {
        $objAttribute = Attribute::findByPk($objWidget->activeRecord->id);

        $arrColumns = array
        (
            'value' => array
            (
                'label'     => &$GLOBALS['TL_LANG']['tl_iso_attribute']['options']['value'],
                'inputType' => 'text',
                'eval'      => array('class'=>'tl_text_2'),
            ),
            'label' => array
            (
                'label'     => &$GLOBALS['TL_LANG']['tl_iso_attribute']['options']['label'],
                'inputType' => 'text',
                'eval'      => array('class'=>'tl_text_2'),
            )
        );

        if (null === $objAttribute || $objAttribute->hasOptionsDefault()) {
            $arrColumns['default'] = array
            (
                'label'     => &$GLOBALS['TL_LANG']['tl_iso_attribute']['options']['default'],
                'inputType' => 'checkbox',
                'eval'      => array('columnPos'=>2),
            );
        }

        if (null === $objAttribute || $objAttribute->hasOptionsGroup()) {
            $arrColumns['group'] = array
            (
                'label'     => &$GLOBALS['TL_LANG']['tl_iso_attribute']['options']['group'],
                'inputType' => 'checkbox',
                'eval'      => array('columnPos'=>3),
            );
        }

        return $arrColumns;
    }
}
