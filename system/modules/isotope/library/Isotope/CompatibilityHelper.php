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

use Contao\Database;
use Contao\FrontendUser;
use Contao\MemberModel;
use Contao\StringUtil;
use Contao\System;

/**
 * @internal
 */
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

    public static function getCountries(): array
    {
        $arrCountries = System::getContainer()->get('contao.intl.countries')->getCountries();

        return array_combine(array_map('strtolower', array_keys($arrCountries)), $arrCountries);
    }

    public static function asset(string $path): string
    {
        return System::getContainer()->get('assets.packages')->getUrl($path, 'isotope_core');
    }

    public static function isGranted(int|MemberModel|FrontendUser|null $member, array|string $groups): bool
    {
        $groups = StringUtil::deserialize($groups, true);

        if (is_int($member) && $member > 0) {
            $member = MemberModel::findByPk($member);
        }

        if (!$member) {
            return \in_array(-1, array_map(\intval(...), $groups), true);
        }

        return [] !== array_intersect($groups, StringUtil::deserialize($member->groups, true));
    }
}
