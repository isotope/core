<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Helper;

/**
 * Class Scope
 *
 * Provide methods to check the current scope.
 *
 */
class Scope
{
    /**
     * Check if the current scope is backend.
     *
     * @return bool
     */
    public static function isBackend(): bool
    {
        $container = System::getContainer();
        $scopeMatcher = $container->get('contao.routing.scope_matcher');
        $requestStack = $container->get('request_stack');


        return $scopeMatcher->isBackendRequest($requestStack->getCurrentRequest() ?? Request::create(''));
    }


    /**
     * Check if the current scope is frontend.
     *
     * @return bool
     */
    public static function isFrontend(): bool
    {
        $container = System::getContainer();
        $scopeMatcher = $container->get('contao.routing.scope_matcher');
        $requestStack = $container->get('request_stack');


        return $scopeMatcher->isFrontRequest($requestStack->getCurrentRequest() ?? Request::create(''));
    }
}
