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
        $this->rgxp = 'BE' === TL_MODE ? 'price' : 'natural';

        parent::saveToDCA($arrData);

        $arrData['fields'][$this->field_name]['sql'] = "decimal(12,2) NOT NULL default '0.00'";

        if ('BE' === TL_MODE) {
            unset(
                $arrData['fields'][$this->field_name]['eval']['minval'],
                $arrData['fields'][$this->field_name]['eval']['maxval'],
                $arrData['fields'][$this->field_name]['eval']['step']
            );
        }
    }
}
