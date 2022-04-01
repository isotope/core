<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

declare(strict_types=1);

namespace Isotope\Model\Attribute;

use Isotope\Model\Attribute;

class QuantitySurcharge extends Attribute
{
    public function getBackendWidget(): string
    {
        return $GLOBALS['BE_FFL']['text'];
    }

    public function getFrontendWidget(): string
    {
        return $GLOBALS['TL_FFL']['text'];
    }

    public function isCustomerDefined(): bool
    {
        // Enable both frontend and backend widget
        return 'BE' !== TL_MODE;
    }

    public function saveToDCA(array &$arrData): void
    {
        parent::saveToDCA($arrData);

        $arrData['eval']['rgxp'] = 'natural';
        $arrData['fields'][$this->field_name]['sql'] = "decimal(12,2) NOT NULL default '0.00'";

        if ('BE' === TL_MODE) {
            $arrData['eval']['rgxp'] = 'price';
            unset($arrData['eval']['minval'], $arrData['eval']['maxval'], $arrData['eval']['step']);
        }
    }
}
