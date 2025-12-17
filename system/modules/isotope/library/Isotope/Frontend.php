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
use Contao\FrontendTemplate;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Isotope\CoreBundle\Product\ProductInterface;
use Isotope\Interfaces\IsotopeOrderableCollection;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\ProductCollection\Cart;
use Isotope\Model\ProductCollection\Order;
use Isotope\Model\ProductCollectionSurcharge;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provide methods to handle Isotope front end components.
 */
class Frontend extends \Contao\Frontend
{
    /**
     * Get shipping and payment surcharges for given collection
     *
     *
     * @return ProductCollectionSurcharge[]
     */
    public function findShippingAndPaymentSurcharges(IsotopeProductCollection $objCollection)
    {
        if (!$objCollection instanceof IsotopeOrderableCollection) {
            \Contao\System::getContainer()->get('monolog.logger.contao')->log(\Psr\Log\LogLevel::ERROR, 'Product collection ID "' . $objCollection->getId() . '" is not orderable', ['contao' => new \Contao\CoreBundle\Monolog\ContaoContext(__METHOD__, \Contao\CoreBundle\Monolog\ContaoContext::ERROR)]);
            return false;
        }

        // Do not add shipping and payment surcharge to cart,
        // they should only appear in the order review
        if ($objCollection instanceof Cart) {
            return array();
        }

        $arrSurcharges = array();

        if (($objSurcharge = $objCollection->getShippingSurcharge()) !== null) {
            $arrSurcharges[] = $objSurcharge;
        }

        if (($objSurcharge = $objCollection->getPaymentSurcharge()) !== null) {
            $arrSurcharges[] = $objSurcharge;
        }

        return $arrSurcharges;
    }

    /**
     * Inject the necessary scripts here upon the "modifyFrontendPage" hook.
     * We don't use the generatePage hook here anymore as modules added via
     * InsertTags will not get those scripts added. We also don't use a combination
     * of both (e.g. use generatePage hook by default and use the modifyFrontendPage
     * hook only if there are still $GLOBALS['AJAX_PRODUCTS'] because a simple
     * str_replace on </body> is really not a performance issue. So we chose
     * simplicity here.
     *
     * @param string $buffer
     * @param string $templateName
     *
     * @return string
     */
    public function injectScripts($buffer, $templateName)
    {
        // Only add messages to the fe_page template (see isotope/core#2255)
        if (!empty($templateName) && 0 !== strncmp($templateName, 'fe_', 3)) {
            return $buffer;
        }

        $messages = Message::generate();
        $hasProducts = !empty($GLOBALS['AJAX_PRODUCTS']) && \is_array($GLOBALS['AJAX_PRODUCTS']);

        if ($messages === '' && !$hasProducts) {

            return $buffer;
        }

        $template = new FrontendTemplate('iso_scripts');

        if ($hasProducts) {
            $template->hasProducts = true;
            $template->loadMessage = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['loadingProductData']);
            $template->products    = json_encode($GLOBALS['AJAX_PRODUCTS']);
        }

        if ($messages !== '') {
            $template->hasMessages = true;
            $template->messages = str_replace(array("\n", "\r", "'"), array('', '', '\''), $messages);
        }

        return str_replace('</body>', $template->parse() . '</body>', $buffer);
    }

    /**
     * Get postal codes from CSV and ranges
     *
     * @param string $strPostalCodes
     *
     * @return array
     */
    public static function parsePostalCodes($strPostalCodes)
    {
        $arrCodes = array();

        foreach (StringUtil::trimsplit(',', $strPostalCodes) as $strCode) {
            $arrCode = StringUtil::trimsplit('-', $strCode);

            // Ignore codes with more than 1 range
            switch (\count($arrCode)) {
                case 1:
                    $arrCodes[] = $arrCode[0];
                    break;

                case 2:
                    $arrCodes = array_merge($arrCodes, range($arrCode[0], $arrCode[1]));
                    break;
            }
        }

        return $arrCodes;
    }

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
            $objPage->templateGroup = $objLayout->templates;

            // Store the output format
            [$strFormat, $strVariant] = explode('_', $objLayout->doctype) + [null, null];
            $objPage->outputFormat  = $strFormat;
            $objPage->outputVariant = $strVariant;
        }

        self::setLanguage($objPage->language);

        return $objPage;
    }

    /**
     * Replaces Isotope specific InsertTags in Frontend
     *
     * @param string $strTag
     *
     * @return mixed
     *
     * @deprecated Deprecated since version 2.3, to be removed in 3.0. Use InsertTag::replace() instead.
     */
    public function replaceIsotopeTags($strTag)
    {
        return (new InsertTag())->replace($strTag);
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
