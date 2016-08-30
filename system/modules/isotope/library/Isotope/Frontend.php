<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope;

use Haste\Input\Input;
use Haste\Util\Url;
use Isotope\EventListener\ChangeLanguageListener;
use Isotope\Interfaces\IsotopeAttributeWithOptions;
use Isotope\Interfaces\IsotopeOrderableCollection;
use Isotope\Interfaces\IsotopePrice;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\Attribute;
use Isotope\Model\AttributeOption;
use Isotope\Model\Product;
use Isotope\Model\Product\Standard;
use Isotope\Model\ProductCollection\Cart;
use Isotope\Model\ProductCollection\Order;
use Isotope\Model\ProductCollectionItem;
use Isotope\Model\ProductCollectionSurcharge;

/**
 * Class Isotope\Frontend
 *
 * Provide methods to handle Isotope front end components.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 * @author     Yanick Witschi <yanick.witschi@terminal42.ch>
 */
class Frontend extends \Frontend
{

    /**
     * Cached reader page id's
     * @var array
     */
    protected static $arrReaderPageIds = array();


    /**
     * Get shipping and payment surcharges for given collection
     *
     * @param IsotopeProductCollection $objCollection
     *
     * @return ProductCollectionSurcharge[]
     */
    public function findShippingAndPaymentSurcharges(IsotopeProductCollection $objCollection)
    {
        if (!$objCollection instanceof IsotopeOrderableCollection) {
            \System::log('Product collection ID "' . $objCollection->getId() . '" is not orderable', __METHOD__, TL_ERROR);
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
     * Callback for add_to_cart button
     *
     * @param IsotopeProduct $objProduct
     * @param array          $arrConfig
     */
    public function addToCart(IsotopeProduct $objProduct, array $arrConfig = array())
    {
        $objModule   = $arrConfig['module'];
        $intQuantity = ($objModule->iso_use_quantity && ((int) \Input::post('quantity_requested')) > 0) ? ((int) \Input::post('quantity_requested')) : 1;

        // Do not add parent of variant product to the cart
        if ($objProduct->hasVariants() && !$objProduct->isVariant()) {
            return;
        }

        if (Isotope::getCart()->addProduct($objProduct, $intQuantity, $arrConfig) !== false) {
            Message::addConfirmation($GLOBALS['TL_LANG']['MSC']['addedToCart']);

            if (!$objModule->iso_addProductJumpTo) {
                \Controller::reload();
            }

            \Controller::redirect(
                Url::addQueryString(
                    'continue=' . base64_encode(\Environment::get('request')),
                    $objModule->iso_addProductJumpTo
                )
            );
        }
    }

    /**
     * Callback for add_to_cart button if a product is being edited.
     *
     * @param IsotopeProduct $objProduct
     * @param array          $arrConfig
     */
    public function updateCart(IsotopeProduct $objProduct, array $arrConfig = array())
    {
        if (\Input::get('collection_item') < 1
            || ($item = ProductCollectionItem::findByPk(\Input::get('collection_item'))) === null
            || $item->pid != Isotope::getCart()->id
            || !$item->hasProduct()
            || $item->getProduct()->getProductId() != $objProduct->getProductId()
        ) {
            return;
        }

        Isotope::getCart()->updateProduct($objProduct, $item);

        if (!$arrConfig['module']->iso_addProductJumpTo) {
            \Controller::reload();
        }

        \Controller::redirect(
            Url::addQueryString(
                'continue=' . base64_encode(\Environment::get('request')),
                $arrConfig['module']->iso_addProductJumpTo
            )
        );
    }

    /**
     * Callback for toggle_favorites button
     *
     * @param IsotopeProduct $objProduct
     * @param array          $arrConfig
     */
    public function toggleFavorites(IsotopeProduct $objProduct, array $arrConfig = array())
    {
        $favorites = Isotope::getFavorites();

        if ($favorites->hasProduct($objProduct)) {
            $favorites->deleteItem($favorites->getItemForProduct($objProduct));
            Message::addConfirmation($GLOBALS['TL_LANG']['MSC']['removedFromFavorites']);
        } elseif ($favorites->addProduct($objProduct, 1, $arrConfig) !== false) {
            Message::addConfirmation($GLOBALS['TL_LANG']['MSC']['addedToFavorites']);
        }

        \Controller::reload();
    }

    /**
     * Replace the current page with a reader page if applicable
     *
     * @param array $arrFragments
     *
     * @return array
     */
    public function loadReaderPageFromUrl($arrFragments)
    {
        $strKey   = 'product';
        $strAlias = '';

        // Find products alias. Can't use Input because they're not yet initialized
        if ($GLOBALS['TL_CONFIG']['useAutoItem'] && in_array($strKey, $GLOBALS['TL_AUTO_ITEM'], true)) {
            $strKey = 'auto_item';
        }

        for ($i = 1, $c = count($arrFragments); $i < $c; $i += 2) {
            if ($arrFragments[$i] == $strKey) {
                $strAlias = $arrFragments[$i + 1];
            }
        }

        global $objIsotopeListPage;
        $objIsotopeListPage = null;

        if ($strAlias != '' && ($objPage = \PageModel::findPublishedByIdOrAlias($arrFragments[0])) !== null) {

            // Check the URL and language of each page if there are multiple results
            // see Contao's index.php
            if ($objPage !== null && $objPage->count() > 1) {
                $objNewPage = null;
                $arrPages   = array();

                // Order by domain and language
                /** @var \PageModel $objCurrentPage */
                foreach ($objPage as $objCurrentPage) {
                    $objCurrentPage->loadDetails();

                    $domain                                           = $objCurrentPage->domain ? : '*';
                    $arrPages[$domain][$objCurrentPage->rootLanguage] = $objCurrentPage;

                    // Also store the fallback language
                    if ($objCurrentPage->rootIsFallback) {
                        $arrPages[$domain]['*'] = $objCurrentPage;
                    }
                }

                $strHost = \Environment::get('host');

                // Look for a root page whose domain name matches the host name
                if (isset($arrPages[$strHost])) {
                    $arrLangs = $arrPages[$strHost];
                } else {
                    $arrLangs = $arrPages['*']; // Empty domain
                }

                // Use the first result (see #4872)
                if (!$GLOBALS['TL_CONFIG']['addLanguageToUrl']) {
                    $objNewPage = current($arrLangs);
                } // Try to find a page matching the language parameter
                elseif (($lang = \Input::get('language')) != '' && isset($arrLangs[$lang])) {
                    $objNewPage = $arrLangs[$lang];
                }

                // Store the page object
                if (is_object($objNewPage)) {
                    $objPage = $objNewPage;
                }
            }

            if ($objPage->iso_setReaderJumpTo && ($objReader = $objPage->getRelated('iso_readerJumpTo')) !== null) {
                /** @var \PageModel $objIsotopeListPage */
                $objIsotopeListPage = $objPage->current();
                $objIsotopeListPage->loadDetails();

                $arrFragments[0] = ($objReader->alias ?: $objReader->id);
            }
        }

        return $arrFragments;
    }

    /**
     * Overrides the reader page
     *
     * @param \PageModel $objPage
     */
    public function overrideReaderPage($objPage)
    {
        global $objPage;
        global $objIsotopeListPage;

        if (null !== $objIsotopeListPage) {
            $arrTrail   = $objIsotopeListPage->trail;
            $arrTrail[] = $objPage->id;

            $objPage->pid   = $objIsotopeListPage->id;
            $objPage->alias = $objIsotopeListPage->alias;
            $objPage->trail = $arrTrail;
        }
    }

    /**
     * Replaces Isotope specific InsertTags in Frontend
     *
     * @param string $strTag
     *
     * @return mixed
     *
     * @deprecated Deprecated since version 2.3, to be removed in 3.0. Use \Isotope\InsertTag::replace() instead.
     */
    public function replaceIsotopeTags($strTag)
    {
        $callback = new InsertTag();

        return $callback->replace($strTag);
    }


    /**
     * Hook callback for changelanguage extension to support language switching on product reader page
     *
     * @param array $arrGet
     *
     * @return array
     *
     * @deprecated Deprecated since Isotope 2.4. See Isotope\EventListener\ChangeLanguageListener
     */
    public function translateProductUrls($arrGet)
    {
        $listener = new ChangeLanguageListener();
        return $listener->onTranslateUrlParameters($arrGet);
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
     *
     * @return string
     */
    public function injectScripts($buffer)
    {
        $messages = Message::generate();
        $hasProducts = !empty($GLOBALS['AJAX_PRODUCTS']) && is_array($GLOBALS['AJAX_PRODUCTS']);

        if ($messages === '' && !$hasProducts) {

            return $buffer;
        }

        $template = new \FrontendTemplate('iso_scripts');

        if ($hasProducts) {
            $template->hasProducts = true;
            $template->loadMessage = specialchars($GLOBALS['TL_LANG']['MSC']['loadingProductData']);
            $template->products    = json_encode($GLOBALS['AJAX_PRODUCTS']);
        }

        if ($messages !== '') {
            $template->hasMessages = true;
            $template->messages = str_replace(array("\n", "'"), array('', '\''), $messages);
        }

        return str_replace('</body>', $template->parse() . '</body>', $buffer);
    }

    /**
     * Return all error, confirmation and info messages as HTML string
     *
     * @return string
     *
     * @deprecated use Isotope\Message::generate
     */
    public static function getIsotopeMessages()
    {
        return Message::generate();
    }

    /**
     * Format surcharge prices
     *
     * @param ProductCollectionSurcharge[] $arrSurcharges
     * @param string|null                  $currencyCode
     *
     * @return array
     */
    public static function formatSurcharges($arrSurcharges, $currencyCode = null)
    {
        $i         = 0;
        $arrReturn = array();

        foreach ($arrSurcharges as $k => $objSurcharge) {
            $arrReturn[$k]                = $objSurcharge->row();
            $arrReturn[$k]['price']       = Isotope::formatPriceWithCurrency($objSurcharge->price, true, $currencyCode, $objSurcharge->applyRoundingIncrement);
            $arrReturn[$k]['total_price'] = Isotope::formatPriceWithCurrency($objSurcharge->total_price, true, $currencyCode, $objSurcharge->applyRoundingIncrement);
            $arrReturn[$k]['tax_free_total_price'] = Isotope::formatPriceWithCurrency($objSurcharge->tax_free_total_price, true, $currencyCode, $objSurcharge->applyRoundingIncrement);
            $arrReturn[$k]['rowClass']    = trim('foot_' . (++$i) . ' ' . $objSurcharge->rowClass);
            $arrReturn[$k]['tax_id']      = $objSurcharge->getTaxNumbers();
            $arrReturn[$k]['raw']         = $objSurcharge->row();
            $arrReturn[$k]['surcharge']   = $objSurcharge;
        }

        return $arrReturn;
    }


    /**
     * Adds the product urls to the array so they get indexed when search index is rebuilt in the maintenance module
     *
     * @param array  $arrPages     Absolute page urls
     * @param int    $intRoot      Root page id
     * @param bool   $blnIsSitemap True if it's a sitemap module call (= treat differently when page is protected etc.)
     *
     * @return array   Extended array of absolute page urls
     */
    public function addProductsToSearchIndex($arrPages, $intRoot = 0, $blnIsSitemap = false)
    {
        $t         = \PageModel::getTable();
        $time      = \Date::floorToMinute();
        $arrValue  = array();
        $arrColumn = array(
            "$t.type='root'",
            "$t.published='1'",
            "($t.start='' OR $t.start<'$time')",
            "($t.stop='' OR $t.stop>'" . ($time + 60) . "')"
        );

        if ($intRoot > 0) {
            $arrColumn[] = "$t.id=?";
            $arrValue[]  = $intRoot;
        }

        $objRoots = \PageModel::findBy($arrColumn, $arrValue);

        if (null !== $objRoots) {
            foreach ($objRoots as $objRoot) {
                $arrPageIds   = \Database::getInstance()->getChildRecords($objRoot->id, $t, false);
                $arrPageIds[] = $intRoot;

                $objProducts = Product::findPublishedByCategories($arrPageIds);

                if (null !== $objProducts) {
                    foreach ($objProducts as $objProduct) {

                        // Find the categories in the current root
                        $arrCategories = array_intersect($objProduct->getCategories(), $arrPageIds);
                        $intRemaining  = count($arrCategories);

                        foreach ($arrCategories as $intPage) {
                            $objPage = \PageModel::findByPk($intPage);
                            --$intRemaining;

                            // The target page does not exist
                            if ($objPage === null) {
                                continue;
                            }

                            // The target page has not been published
                            if (!$objPage->published
                                || ($objPage->start != '' && $objPage->start > $time)
                                || ($objPage->stop != '' && $objPage->stop < ($time + 60))
                            ) {
                                continue;
                            }

                            // The target page is exempt from the sitemap
                            if ($blnIsSitemap && 'map_never' === $objPage->sitemap) {
                                continue;
                            }

                            // Do not generate a reader for the index page, except if it is the only one
                            if ($intRemaining > 0 && 'index' === $objPage->alias) {
                                continue;
                            }

                            // Generate the domain
                            $strDomain  = ($objRoot->useSSL ? 'https://' : 'http://');
                            $strDomain .= ($objRoot->dns ?: \Environment::get('host')) . TL_PATH . '/';

                            // Pass root language to page object
                            $objPage->language = $objRoot->language;

                            $arrPages[] = $strDomain . $objProduct->generateUrl($objPage);

                            // Only take the first category because this is our primary one
                            // Having multiple reader pages in the sitemap XML would mean duplicate content
                            break;
                        }
                    }
                }
            }
        }

        return array_unique($arrPages);
    }


    /**
     * save_callback for upload widget to store $_FILES data into the product
     *
     * @param mixed          $varValue
     * @param IsotopeProduct $objProduct
     * @param \Widget        $objWidget
     *
     * @return mixed
     *
     * @deprecated Deprecated since Isotope 2.4, to be removed in Isotope 3.0.
     */
    public function saveUpload($varValue, IsotopeProduct $objProduct, \Widget $objWidget)
    {
        if (is_array($_SESSION['FILES'][$objWidget->name])
            && $_SESSION['FILES'][$objWidget->name]['uploaded'] == '1'
            && $_SESSION['FILES'][$objWidget->name]['error'] == 0
        ) {
            return $_SESSION['FILES'][$objWidget->name]['name'];
        }

        return $varValue;
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

        foreach (trimsplit(',', $strPostalCodes) as $strCode) {
            $arrCode = trimsplit('-', $strCode);

            // Ignore codes with more than 1 range
            switch (count($arrCode)) {
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
     * Store the current article ID so we know it for the product list
     *
     * @param \Database\Result $objRow
     */
    public function storeCurrentArticle($objRow)
    {
        $GLOBALS['ISO_CONFIG']['current_article']['id']  = $objRow->id;
        $GLOBALS['ISO_CONFIG']['current_article']['pid'] = $objRow->pid;
    }


    /**
     * Return pages in the current root available to the member
     * Necessary to check if a product is allowed in the current site and cache the value
     *
     * @param array                      $arrPages
     * @param \MemberModel|\FrontendUser $objMember
     *
     * @return array
     */
    public static function getPagesInCurrentRoot(array $arrPages, $objMember = null)
    {
        if (0 === count($arrPages)) {
            return $arrPages;
        }

        /** @var \PageModel $objPage */
        global $objPage;

        // $objPage not available, we don't know if the page is allowed
        if (null === $objPage || $objPage == 0) {
            return $arrPages;
        }

        static $arrAvailable = array();
        static $arrUnavailable = array();

        $intMember = 0;
        $arrGroups = [];

        if (null !== $objMember) {
            $intMember = $objMember->id;
            $arrGroups = deserialize($objMember->groups, true);
        }

        if (!isset($arrAvailable[$intMember])) {
            $arrAvailable[$intMember] = array();
        }

        if (!isset($arrUnavailable[$intMember])) {
            $arrUnavailable[$intMember] = array();
        }

        // Load remaining (not cached) pages.
        foreach (array_diff($arrPages, $arrAvailable[$intMember], $arrUnavailable[$intMember]) as $intPage) {
            $objPageDetails = \PageModel::findWithDetails($intPage);

            // Page is not in the current root
            if ($objPageDetails->rootId != $objPage->rootId) {
                continue;
            }

            // Page is for guests only but we have a member
            if ($objPageDetails->guests && $intMember > 0 && !$objPageDetails->protected) {
                $arrUnavailable[$intMember][] = $intPage;
                continue;

            } elseif ($objPageDetails->protected) {
                // Page is protected but we have no member
                if ($intMember == 0) {
                    $arrUnavailable[$intMember][] = $intPage;
                    continue;
                }

                $arrPGroups = deserialize($objPageDetails->groups);

                // Page is protected but has no groups
                if (!is_array($arrPGroups)) {
                    $arrUnavailable[$intMember][] = $intPage;
                    continue;
                }

                // Page groups do not match with member groups
                if (count(array_intersect($arrGroups, $arrPGroups)) == 0) {
                    $arrUnavailable[$intMember][] = $intPage;
                    continue;
                }
            }

            $arrAvailable[$intMember][] = $intPage;
        }

        return array_intersect($arrPages, $arrAvailable[$intMember]);
    }

    /**
     * Show product name in breadcrumb
     *
     * @param array  $arrItems
     *
     * @return array
     */
    public function addProductToBreadcrumb($arrItems)
    {
        /** @var \PageModel $objPage */
        global $objPage;

        if ($objPage->type === 'error_404'
            || $objPage->type === 'error_403'
            || !($alias = Input::getAutoItem('product', false, true))
            || ($objProduct = Product::findAvailableByIdOrAlias($alias)) === null
        ) {
            return $arrItems;
        }

        global $objIsotopeListPage;
        $last = count($arrItems) - 1;

        // If we have a reader page, rename the last item (the reader) to the product title
        if (null !== $objIsotopeListPage) {
            $arrItems[$last]['title'] = $this->prepareMetaDescription($objProduct->meta_title ? : $objProduct->name);
            $arrItems[$last]['link']  = $objProduct->name;
        } // Otherwise we add a new item for the product at the last position
        else {
            $arrItems[$last]['href'] = \Controller::generateFrontendUrl($arrItems[$last]['data']);
            $arrItems[$last]['isActive'] = false;

            $arrItems[] = array(
                'isRoot'   => false,
                'isActive' => true,
                'href'     => $objProduct->generateUrl($objPage),
                'title'    => $this->prepareMetaDescription($objProduct->meta_title ? : $objProduct->name),
                'link'     => $objProduct->name,
                'data'     => $objPage->row(),
            );
        }

        return $arrItems;
    }

    /**
     * Initialize environment (language, objPage) for a given order
     *
     * @param Order  $objOrder
     * @param string $strLanguage
     */
    public static function loadOrderEnvironment(Order $objOrder, $strLanguage = null)
    {
        global $objPage;

        $strLanguage = $strLanguage ?: $objOrder->language;

        // Load page configuration
        if ($objOrder->pageId > 0 && (null === $objPage || $objPage->id != $objOrder->pageId)) {
            $objPage = \PageModel::findWithDetails($objOrder->pageId);
            $objPage = static::loadPageConfig($objPage);
        }

        // Set the current system to the language when the user placed the order.
        // This will result in correct e-mails and payment description.
        $GLOBALS['TL_LANGUAGE'] = $strLanguage;
        \System::loadLanguageFile('default', $strLanguage, true);

        Isotope::setConfig($objOrder->getRelated('config_id'));

        if (($objCart = $objOrder->getRelated('source_collection_id')) !== null && $objCart instanceof Cart) {
            Isotope::setCart($objCart);
        }
    }

    /**
     * Load system configuration into page object
     *
     * @param \Database\Result|\PageModel $objPage
     *
     * @return \Database\Result
     */
    public static function loadPageConfig($objPage)
    {
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
            list($GLOBALS['TL_ADMIN_NAME'], $GLOBALS['TL_ADMIN_EMAIL']) = \StringUtil::splitFriendlyEmail($objPage->adminEmail);
        } else {
            list($GLOBALS['TL_ADMIN_NAME'], $GLOBALS['TL_ADMIN_EMAIL']) = \StringUtil::splitFriendlyEmail($GLOBALS['TL_CONFIG']['adminEmail']);
        }

        // Define the static URL constants
        define('TL_FILES_URL', ($objPage->staticFiles != '' && !$GLOBALS['TL_CONFIG']['debugMode']) ? $objPage->staticFiles . TL_PATH . '/' : '');
        define('TL_SCRIPT_URL', ($objPage->staticSystem != '' && !$GLOBALS['TL_CONFIG']['debugMode']) ? $objPage->staticSystem . TL_PATH . '/' : '');
        define('TL_PLUGINS_URL', ($objPage->staticPlugins != '' && !$GLOBALS['TL_CONFIG']['debugMode']) ? $objPage->staticPlugins . TL_PATH . '/' : '');

        $objLayout = \Database::getInstance()->prepare("
            SELECT l.*, t.templates
            FROM tl_layout l
            LEFT JOIN tl_theme t ON l.pid=t.id
            WHERE l.id=?
            ORDER BY l.id=? DESC
        ")->limit(1)->execute($objPage->layout, $objPage->layout);

        if ($objLayout->numRows) {
            // Get the page layout
            $objPage->template      = strlen($objLayout->template) ? $objLayout->template : 'fe_page';
            $objPage->templateGroup = $objLayout->templates;

            // Store the output format
            list($strFormat, $strVariant) = explode('_', $objLayout->doctype);
            $objPage->outputFormat  = $strFormat;
            $objPage->outputVariant = $strVariant;
        }

        $GLOBALS['TL_LANGUAGE'] = $objPage->language;

        return $objPage;
    }

    /**
     * Adjust module and module id for certain payment and/or shipping modules
     *
     * @param \Isotope\PostSale $objPostsale
     */
    public function setPostsaleModuleSettings(PostSale $objPostsale)
    {
        // Payment method "Payone"
        $strParam = \Input::post('param');

        if (strpos($strParam, 'paymentMethodPayone') !== false) {
            $intId = (int) str_replace('paymentMethodPayone', '', $strParam);
            $objPostsale->setModule('pay');
            $objPostsale->setModuleId($intId);
        }
    }

    /**
     * Calculate price surcharge for attribute options
     *
     * @param float  $fltPrice
     * @param object $objSource
     * @param string $strField
     * @param int    $intTaxClass
     * @param array  $arrOptions
     *
     * @return float
     * @throws \Exception
     */
    public function addOptionsPrice($fltPrice, $objSource, $strField, $intTaxClass, array $arrOptions)
    {
        $fltAmount = $fltPrice;

        if ($objSource instanceof IsotopePrice && ($objProduct = $objSource->getRelated('pid')) !== null) {
            /** @var IsotopeProduct|Standard $objProduct */

            $arrAttributes = array_intersect(
                Attribute::getPricedFields(),
                array_merge(
                    $objProduct->getType()->getAttributes(),
                    $objProduct->getType()->getVariantAttributes()
                )
            );

            foreach ($arrAttributes as $field) {
                if (($objAttribute = $GLOBALS['TL_DCA']['tl_iso_product']['attributes'][$field]) !== null
                    && $objAttribute instanceof IsotopeAttributeWithOptions
                    && $objAttribute->canHavePrices()
                    && ($objOptions = $objAttribute->getOptionsFromManager($objProduct)) !== null
                ) {
                    $value = $objAttribute->isCustomerDefined() ? $arrOptions[$field] : $objProduct->$field;
                    $value = deserialize($value, true);

                    /** @var AttributeOption $objOption */
                    foreach ($objOptions as $objOption) {
                        if (in_array($objOption->id, $value)) {
                            $fltAmount += $objOption->getAmount($fltPrice, 0);
                        }
                    }
                }
            }
        }

        return $fltAmount;
    }
}
