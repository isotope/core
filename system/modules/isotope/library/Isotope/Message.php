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
use Haste\Data\Collection;
use Haste\Data\Plain;
use Isotope\Module\Messages;

class Message
{

    /**
     * Add an error message
     *
     * @param string $strMessage The error message
     */
    public static function addError($strMessage)
    {
        static::add($strMessage, 'ISO_ERROR');
    }


    /**
     * Add a confirmation message
     *
     * @param string $strMessage The confirmation message
     */
    public static function addConfirmation($strMessage)
    {
        static::add($strMessage, 'ISO_CONFIRM');
    }


    /**
     * Add an info message
     *
     * @param string $strMessage The info message
     */
    public static function addInfo($strMessage)
    {
        static::add($strMessage, 'ISO_INFO');
    }


    /**
     * Add a preformatted message
     *
     * @param string $strMessage The preformatted message
     */
    public static function addRaw($strMessage)
    {
        static::add($strMessage, 'ISO_RAW');
    }


    /**
     * Add a message
     *
     * @param string $strMessage The message text
     * @param string $strType    The message type
     *
     * @throws \LogicException If $strType is not a valid message type
     */
    public static function add($strMessage, $strType)
    {
        if ($strMessage == '') {
            return;
        }

        if (!\in_array($strType, static::getTypes())) {
            throw new \LogicException("Invalid message type $strType");
        }

        if (!\is_array($_SESSION[$strType] ?? null)) {
            $_SESSION[$strType] = array();
        }

        $_SESSION[$strType][] = $strMessage;
    }


    /**
     * Return all messages as HTML
     *
     * @return string The messages HTML markup
     */
    public static function generate()
    {
        if (static::isEmpty()) {
            return '';
        }

        $objModule = new Messages(new \ModuleModel());
        $objModule->type = 'iso_messages';

        return $objModule->generate();
    }


    /**
     * Get all messages as array
     *
     * @return array
     */
    public static function getAll()
    {
        $arrMessages = array();

        foreach (static::getTypes() as $strType) {

            if (empty($_SESSION[$strType])) {
                continue;
            }

            $strClass = strtolower($strType);
            $_SESSION[$strType] = array_unique($_SESSION[$strType]);

            foreach ($_SESSION[$strType] as $strMessage) {
                $strFormatted = '';

                if ($strType != 'ISO_RAW') {
                    $strFormatted = sprintf('<p class="%s">%s</p>%s', $strClass, $strMessage, "\n");
                }

                $arrMessages[] = new Plain(
                    $strMessage,
                    '',
                    array(
                        'type'      => $strType,
                        'class'     => $strClass,
                        'formatted' => $strFormatted
                    )
                );
            }
        }

        $request = System::getContainer()->get('request_stack')->getMasterRequest();
        if ($request && $request->isMethod('GET')) {
            static::reset();
        }

        return new Collection($arrMessages);
    }


    /**
     * Reset the message system
     */
    public static function reset()
    {
        foreach (static::getTypes() as $strType) {
            $_SESSION[$strType] = array();
        }
    }

    /**
     * Returns true if there are no messages defined
     *
     * @return bool
     */
    public static function isEmpty()
    {
        foreach (static::getTypes() as $strType) {
            if (!empty($_SESSION[$strType])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return all available message types
     *
     * @return array An array of message types
     */
    public static function getTypes()
    {
        return array('ISO_ERROR', 'ISO_CONFIRM', 'ISO_INFO', 'ISO_RAW');
    }
}
