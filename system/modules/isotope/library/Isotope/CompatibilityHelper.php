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
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CompatibilityHelper
 *
 * Provides compatibility-related methods for Contao Isotope
 *
 */
class CompatibilityHelper
{
    /**
     * Get the current request.
     *
     * @return Request|null
     */
    public static function getCurrentRequest()
    {
        $container = System::getContainer();

        return $container->get('request_stack')->getCurrentRequest();
    }


    /**
     * Check if the current scope is backend.
     *
     * @return bool True if a request exists and is backend.
     */
    public static function isBackend(): bool
    {
        $container = System::getContainer(); // Define $container here

        $request = self::getCurrentRequest();

        if ($request) {
            $scopeMatcher = $container->get('contao.routing.scope_matcher');

            return $scopeMatcher->isBackendRequest($request);
        }

        return false;
    }


    /**
     * Check if the current scope is frontend.
     *
     * @return bool True if a request exists and is frontend.
     */
    public static function isFrontend(): bool
    {
        $container = System::getContainer(); // Define $container here

        $request = self::getCurrentRequest();

        if ($request) {
            $scopeMatcher = $container->get('contao.routing.scope_matcher');

            return $scopeMatcher->isFrontendRequest($request);
        }

        return false;
    }
}
