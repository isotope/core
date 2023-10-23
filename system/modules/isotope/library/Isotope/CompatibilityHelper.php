<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope;

use Contao\System;

class CompatibilityHelper
{
    /**
     * Check if the current scope is backend.
     */
    public static function isBackend(): bool
    {
        $container = System::getContainer();
        $request = $container->get('request_stack')->getCurrentRequest();

        if (!$request) {
            return false;
        }

        return $container->get('contao.routing.scope_matcher')->isBackendRequest($request);
    }


    /**
     * Check if the current scope is frontend.
     */
    public static function isFrontend(): bool
    {
        $container = System::getContainer();
        $request = $container->get('request_stack')->getCurrentRequest();

        if (!$request) {
            return false;
        }

        return $container->get('contao.routing.scope_matcher')->isFrontendRequest($request);
    }
}
