<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\TaxRate;

use Contao\Backend;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Isotope\Backend\Permission;
use Isotope\Isotope;
use Isotope\Model\Config;
use Isotope\Model\TaxRate;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;


class Callback extends Backend
{

    /**
     * Check permissions to edit table tl_iso_tax_rate
     * @return void
     */
    public function checkPermission()
    {
        // Do not run the permission check on other Isotope modules
        if ('tax_rate' !== Input::get('mod')) {
            return;
        }

        if (\Contao\BackendUser::getInstance()->isAdmin) {
            return;
        }

        // Set root IDs
        if (!\is_array(\Contao\BackendUser::getInstance()->iso_tax_rates) || \count(\Contao\BackendUser::getInstance()->iso_tax_rates) < 1) // Can't use empty() because its an object property (using __get)
        {
            $root = array(0);
        } else {
            $root = \Contao\BackendUser::getInstance()->iso_tax_rates;
        }

        $GLOBALS['TL_DCA']['tl_iso_tax_rate']['list']['sorting']['root'] = $root;
    }


    /**
     * List all records with formatted currency
     * @param array
     * @return string
     */
    public function listRow($row)
    {
        $arrRate = StringUtil::deserialize($row['rate']);

        if ($row['config'] && !$arrRate['unit']) {
            Isotope::setConfig(Config::findByPk($row['config']));

            $strRate = Isotope::formatPriceWithCurrency($arrRate['value'], false);
        } else {
            $strRate = $arrRate['value'] . '%';
        }

        return sprintf('%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>', $row['name'], $strRate);
    }


    /**
     * Set the currency rate from selected store config
     * @param object
     */
    public function addCurrencyRate($dc)
    {
        $objTaxRate = TaxRate::findByPk($dc->id);

        if (null !== $objTaxRate && $objTaxRate->config > 0 && null !== $objTaxRate->getRelated('config')) {
            $GLOBALS['TL_DCA']['tl_iso_tax_rate']['fields']['rate']['options'][''] = $objTaxRate->getRelated('config')->currency;
        }
    }
}
