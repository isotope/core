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

namespace Isotope\Backend\ProductType;

use Isotope\Model\ProductType;

class Help extends \Backend
{

    public function initializeWizard($strName, $strLanguage)
    {
        if ('explain' === $strName) {
            /** @var ProductType[] $objTypes */
            $objTypes = ProductType::findAll();

            if (null !== $objTypes) {
                foreach ($objTypes as $objType) {
                    $GLOBALS['TL_LANG']['XPL']['tl_iso_product.type'][$objType->id] = array(
                        $objType->name,
                        $objType->description,
                    );
                }
            }
        }
    }
}
