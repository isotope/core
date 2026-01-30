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

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\Database;
use Contao\Database\Result;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Isotope\Model\ProductCollection\Cart;
use Isotope\Model\ProductCollection\Order;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provide methods to handle Isotope front end components.
 */
class Frontend extends \Contao\Frontend
{
    /**
     * Initialize environment (language, objPage) for a given order
     *
     * @param string $strLanguage
     */
    public static function loadOrderEnvironment(Order $objOrder, $strLanguage = null)
    {
        global $objPage;

        $strLanguage = $strLanguage ?: $objOrder->language;

        // Load page configuration
        if (
            $objOrder->pageId > 0
            && (null === $objPage || $objPage->id != $objOrder->pageId)
            && ($objPage = PageModel::findWithDetails($objOrder->pageId))
        ) {
            $objPage = static::loadPageConfig($objPage);
        }

        // Set the current system to the language when the user placed the order.
        // This will result in correct e-mails and payment description.
        self::setLanguage($strLanguage);

        Isotope::setConfig($objOrder->getRelated('config_id'));

        if (($objCart = $objOrder->getRelated('source_collection_id')) !== null && $objCart instanceof Cart) {
            Isotope::setCart($objCart);
        }
    }

    /**
     * Load system configuration into page object
     *
     * @param Result|PageModel $objPage
     *
     * @return Result
     */
    public static function loadPageConfig($objPage)
    {
        if (!\is_object($objPage)) {
            return $objPage;
        }

        // Use the global date format if none is set
        if ($objPage->dateFormat == '') {
            $objPage->dateFormat = $GLOBALS['TL_CONFIG']['dateFormat'];
        }

        if ($objPage->timeFormat == '') {
            $objPage->timeFormat = $GLOBALS['TL_CONFIG']['timeFormat'];
        }

        if ($objPage->datimFormat == '') {
            $objPage->datimFormat = $GLOBALS['TL_CONFIG']['datimFormat'];
        }

        // Set the admin e-mail address
        if ($objPage->adminEmail != '') {
            [$GLOBALS['TL_ADMIN_NAME'], $GLOBALS['TL_ADMIN_EMAIL']] = StringUtil::splitFriendlyEmail($objPage->adminEmail);
        } else {
            [$GLOBALS['TL_ADMIN_NAME'], $GLOBALS['TL_ADMIN_EMAIL']] = StringUtil::splitFriendlyEmail($GLOBALS['TL_CONFIG']['adminEmail']);
        }

        $objLayout = Database::getInstance()->prepare("
            SELECT l.*, t.templates
            FROM tl_layout l
            LEFT JOIN tl_theme t ON l.pid=t.id
            WHERE l.id=?
            ORDER BY l.id=? DESC
        ")->limit(1)->execute($objPage->layout, $objPage->layout);

        if ($objLayout->numRows) {
            // Get the page layout
            $objPage->template      = \strlen($objLayout->template) ? $objLayout->template : 'fe_page';

            // Store the output format
            [$strFormat, $strVariant] = explode('_', $objLayout->doctype) + [null, null];
            $objPage->outputFormat  = $strFormat;
            $objPage->outputVariant = $strVariant;
        }

        self::setLanguage($objPage->language);

        return $objPage;
    }

    /**
     * Switches the environment to the given language.
     *
     * @param string $language
     */
    private static function setLanguage($language)
    {
        $GLOBALS['TL_LANGUAGE'] = $language;

        if (class_exists(ContaoCoreBundle::class)) {
            /** @var ContainerInterface $container */
            $container = System::getContainer();

            if ($container->has('request_stack') && null !== ($request = $container->get('request_stack')->getCurrentRequest())) {
                $request->setLocale($language);
            }

            if ($container->has('translator')) {
                $container->get('translator')->setLocale($language);
            }
        }

        System::loadLanguageFile('default', $language, true);
    }
}
