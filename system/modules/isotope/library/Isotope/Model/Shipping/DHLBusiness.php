<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * @copyright  Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Shipping;

/**
 * @property string $dhl_user
 * @property string $dhl_signature
 * @property string $dhl_epk
 * @property string $dhl_product
 * @property string $dhl_app
 * @property string $dhl_token
 * @property array  $dhl_shipping
 */
class DHLBusiness extends Flat
{
    /**
     * @inheritDoc
     */
    public function isAvailable()
    {
        return parent::isAvailable() && class_exists('Petschko\DHL\BusinessShipment');
    }
}
