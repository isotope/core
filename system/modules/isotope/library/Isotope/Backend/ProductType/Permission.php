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
use Contao\Database;
use Contao\System;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Attribute\VariantAttributeInterface;

class Permission extends Backend
{

    /**
     * Check permissions for that entry
     * @return void
     */
    public static function check()
    {
        // Disable variants if no such attributes are available
        Controller::loadDataContainer('tl_iso_product');
        $blnVariants = false;
        foreach ($GLOBALS['TL_DCA']['tl_iso_product']['fields'] as $strName => $arrConfig) {
            $objAttribute = System::getContainer()->get(AttributeRegistry::class)->get($strName);

            if (null !== $objAttribute && $objAttribute instanceof VariantAttributeInterface && $objAttribute->isVariantOption()) {
                $blnVariants = true;
                break;
            }
        }

        if (!$blnVariants) {
            System::loadLanguageFile('explain');

            unset($GLOBALS['TL_DCA']['tl_iso_producttype']['subpalettes']['variants']);
            $GLOBALS['TL_DCA']['tl_iso_producttype']['fields']['variants']['input_field_callback'] = function($dc) {

                // Make sure variants are disabled in this product type (see #1114)
                Database::getInstance()->prepare("UPDATE tl_iso_producttype SET variants='' WHERE id=?")->execute($dc->id);

                return '<br><p class="tl_info">'.$GLOBALS['TL_LANG']['XPL']['noVariantAttributes'].'</p>';
            };
        }
    }
}
