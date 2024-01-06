<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Frontend\ProductAction;

use Contao\System;

class Registry
{
    /**
     * @var array
     */
    private static $actions = [];

    /**
     * Adds an action to the registry.
     *
     * @param ProductActionInterface $action
     */
    public static function add(ProductActionInterface $action)
    {
        static::$actions[$action->getName()] = $action;
    }

    /**
     * Removes an action from the registry.
     *
     * @param ProductActionInterface $action
     */
    public static function remove(ProductActionInterface $action)
    {
        unset(static::$actions[$action->getName()]);
    }

    /**
     * @param bool   $includeButtons
     * @param object $module
     *
     * @return ProductActionInterface[]
     */
    public static function all($includeButtons = true, $module = null)
    {
        $actions = static::$actions;

        if ($includeButtons
            && isset($GLOBALS['ISO_HOOKS']['buttons'])
            && \is_array($GLOBALS['ISO_HOOKS']['buttons'])
        ) {
            $buttons = [];

            foreach ($GLOBALS['ISO_HOOKS']['buttons'] as $callback) {
                if ($callback === ['Isotope\Isotope', 'defaultButtons']) {
                    continue;
                }

                $buttons = System::importStatic($callback[0])->{$callback[1]}($buttons, $module);
            }

            foreach ($buttons as $name => $config) {
                $action = new LegacyButtonAction($name, $config['label'], $config['callback']);

                if (isset($config['class'])) {
                    $action->setClasses($config['class']);
                }

                $actions[$name] = $action;
            }
        }

        return $actions;
    }
}
