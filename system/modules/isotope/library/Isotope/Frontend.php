<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope;

use Haste\Haste;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\Product;
use Isotope\Model\ProductCollection\Order;
use Isotope\Module\Messages;


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
     * @param IsotopeProductCollection
     * @return array
     */
    public function findShippingAndPaymentSurcharges(IsotopeProductCollection $objCollection)
    {
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
     * @param object
     * @param array
     */
    public function addToCart($objProduct, array $arrConfig = array())
    {
        $objModule   = $arrConfig['module'];
        $intQuantity = ($objModule->iso_use_quantity && intval(\Input::post('quantity_requested')) > 0) ? intval(\Input::post('quantity_requested')) : 1;

        if (Isotope::getCart()->addProduct($objProduct, $intQuantity, $arrConfig) !== false) {
            $_SESSION['ISO_CONFIRM'][] = $GLOBALS['TL_LANG']['MSC']['addedToCart'];

            if (!$objModule->iso_addProductJumpTo) {
                $this->reload();
            }

            \Controller::redirect(\Haste\Util\Url::addQueryString('continue=' . base64_encode(\Environment::get('request')), $objModule->iso_addProductJumpTo));
        }
    }

    /**
     * Replace the current page with a reader page if applicable
     * @param   array
     * @return  array
     */
    public function loadReaderPageFromUrl($arrFragments)
    {
        $strKey   = 'product';
        $strAlias = '';

        // Find products alias. Can't use Input because they're not yet initialized
        if ($GLOBALS['TL_CONFIG']['useAutoItem'] && in_array($strKey, $GLOBALS['TL_AUTO_ITEM'])) {
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
                while ($objPage->next()) {
                    $objCurrentPage = $objPage->current()->loadDetails();

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

                $objIsotopeListPage = $objPage->current();
                $objIsotopeListPage->loadDetails();

                $arrFragments[0] = $objReader->id;
            }
        }

        return $arrFragments;
    }

    /**
     * Overrides the reader page
     * @param   \PageModel
     * @param   \LayoutModel
     * @param   \PageRegular
     */
    public function overrideReaderPage($objPage, $objLayout, $objRegularPage)
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
     * Replaces Isotope-specific InsertTags in Frontend
     * @param string
     * @return mixed
     */
    public function replaceIsotopeTags($strTag)
    {
        $arrTag = trimsplit('::', $strTag);

        if ($arrTag[0] == 'isotope' || $arrTag[0] == 'cache_isotope') {
            switch ($arrTag[1]) {
                case 'cart_items';

                    return Isotope::getCart()->countItems();
                    break;

                case 'cart_quantity';

                    return Isotope::getCart()->sumItemsQuantity();
                    break;

                case 'cart_items_label';
                    $intCount = Isotope::getCart()->countItems();

                    if (!$intCount) {
                        return '';
                    }

                    return $intCount == 1 ? ('(' . $GLOBALS['TL_LANG']['MSC']['productSingle'] . ')') : sprintf(('(' . $GLOBALS['TL_LANG']['MSC']['productMultiple'] . ')'), $intCount);
                    break;

                case 'cart_quantity_label';
                    $intCount = Isotope::getCart()->sumItemsQuantity();

                    if (!$intCount) {
                        return '';
                    }

                    return $intCount == 1 ? ('(' . $GLOBALS['TL_LANG']['MSC']['productSingle'] . ')') : sprintf(('(' . $GLOBALS['TL_LANG']['MSC']['productMultiple'] . ')'), $intCount);
                    break;

                case 'cart_subtotal':
                    return Isotope::formatPriceWithCurrency(Isotope::getCart()->getSubtotal());
                    break;

                case 'cart_taxfree_subtotal':
                    return Isotope::formatPriceWithCurrency(Isotope::getCart()->getTaxFreeSubtotal());
                    break;

                case 'cart_total':
                    return Isotope::formatPriceWithCurrency(Isotope::getCart()->getTotal());
                    break;

                case 'cart_taxfree_total':
                    return Isotope::formatPriceWithCurrency(Isotope::getCart()->getTaxFreeTotal());
                    break;
            }

            return '';
        } elseif ($arrTag[0] == 'isolabel') {
            return Translation::get($arrTag[1], $arrTag[2]);
        } elseif ($arrTag[0] == 'order') {
            if (($objOrder = Order::findOneByUniqid(\Input::get('uid'))) !== null) {
                return $objOrder->{$arrTag[1]};
            }

            return '';
        } elseif ($arrTag[0] == 'product') {
            // 2 possible use cases:
            // {{product::attribute}}                - gets the data of the current product (Product::getActive() or GET parameter "product")
            // {{product::attribute::product_id}}    - gets the data of the specified product ID

            if (count($arrTag) == 3) {
                $objProduct = Product::findAvailableByPk($arrTag[2]);
            } else {
                if (($objProduct = Product::getActive()) === null) {
                    $objProduct = Product::findAvailableByIdOrAlias(\Haste\Input\Input::getAutoItem('product', false, true));
                }
            }

            return ($objProduct !== null) ? $objProduct->{$arrTag[1]} : '';
        }

        return false;
    }


    /**
     * Hook callback for changelanguage extension to support language switching on product reader page
     * @param array
     * @param string
     * @param array
     * @return array
     */
    public function translateProductUrls($arrGet, $strLanguage, $arrRootPage)
    {
        if (\Haste\Input\Input::getAutoItem('product', false, true) != '') {
            $arrGet['url']['product'] = \Haste\Input\Input::getAutoItem('product', false, true);
        } elseif (\Haste\Input\Input::getAutoItem('step', false, true) != '') {
            $arrGet['url']['step'] = \Haste\Input\Input::getAutoItem('step', false, true);
        } elseif (\Input::get('uid', false, true) != '') {
            $arrGet['get']['uid'] = \Input::get('uid', false, true);
        }

        return $arrGet;
    }


    /**
     * Use generatePage Hook to inject necessary javascript
     */
    public function injectScripts()
    {
        if (!empty($GLOBALS['AJAX_PRODUCTS']) && is_array($GLOBALS['AJAX_PRODUCTS'])) {

            $GLOBALS['TL_MOOTOOLS'][] = "
<script>
window.addEvent('domready', function() {
    IsotopeProducts.setLoadMessage('" . specialchars($GLOBALS['TL_LANG']['MSC']['loadingProductData']) . "');
    IsotopeProducts.attach(JSON.decode('" . json_encode($GLOBALS['AJAX_PRODUCTS']) . "'));
});
</script>";
        }

        $strMessages = Message::generate();

        if ($strMessages != '') {
            $GLOBALS['TL_MOOTOOLS'][] = "
<script>
window.addEvent('domready', function()
{
    Isotope.displayBox('" . str_replace(array("\n", "'"), array('', "\'"), $strMessages) . "', true);
});
</script>";
        }
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
     * @param array
     * @return array
     */
    public static function formatSurcharges($arrSurcharges)
    {
        $i         = 0;
        $arrReturn = array();

        foreach ($arrSurcharges as $k => $objSurcharge) {
            $arrReturn[$k]                = $objSurcharge->row();
            $arrReturn[$k]['price']       = Isotope::formatPriceWithCurrency($objSurcharge->price);
            $arrReturn[$k]['total_price'] = Isotope::formatPriceWithCurrency($objSurcharge->total_price);
            $arrReturn[$k]['tax_free_total_price'] = Isotope::formatPriceWithCurrency($objSurcharge->tax_free_total_price);
            $arrReturn[$k]['rowClass']    = trim('foot_' . (++$i) . ' ' . $objSurcharge->rowClass);
            $arrReturn[$k]['tax_id']      = $objSurcharge->getTaxNumbers();
        }

        return $arrReturn;
    }


    /**
     * Adds the product urls to the array so they get indexed when the search index is being rebuilt in the maintenance module
     * @param   array   Absolute page urls
     * @param   int     Root page id
     * @param   boolean True if it's a sitemap module call (= treat differently when page is protected etc.)
     * @param   string  Language of the root page
     * @return  array   Extended array of absolute page urls
     */
    public function addProductsToSearchIndex($arrPages, $intRoot = 0, $blnIsSitemap = false, $strLanguage = null)
    {
        $t         = \PageModel::getTable();
        $time      = time();
        $arrColumn = array("$t.type='root'", "$t.published='1'", "($t.start='' OR $t.start<$time)", "($t.stop='' OR $t.stop>$time)");
        $arrValue  = array();

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
                            if (!$objPage->published || ($objPage->start != '' && $objPage->start > $time) || ($objPage->stop != '' && $objPage->stop < $time)) {
                                continue;
                            }

                            // The target page is exempt from the sitemap
                            if ($blnIsSitemap && $objPage->sitemap == 'map_never') {
                                continue;
                            }

                            // Do not generate a reader for the index page, except if it is the only one
                            if ($intRemaining > 0 && $objPage->alias == 'index') {
                                continue;
                            }

                            // Generate the domain
                            $strDomain = ($objRoot->useSSL ? 'https://' : 'http://') . ($objRoot->dns ?: \Environment::get('host')) . TL_PATH . '/';

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
     * @param mixed
     * @param IsotopeProduct
     */
    public function saveUpload($varValue, IsotopeProduct $objProduct, \Widget $objWidget)
    {
        if (is_array($_SESSION['FILES'][$objWidget->name]) && $_SESSION['FILES'][$objWidget->name]['uploaded'] == '1' && $_SESSION['FILES'][$objWidget->name]['error'] == 0) {
            return $_SESSION['FILES'][$objWidget->name]['name'];
        }

        return $varValue;
    }


    /**
     * Get postal codes from CSV and ranges
     * @param string
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
     * @param \Database\Result
     */
    public function storeCurrentArticle($objRow)
    {
        $GLOBALS['ISO_CONFIG']['current_article']['id']  = $objRow->id;
        $GLOBALS['ISO_CONFIG']['current_article']['pid'] = $objRow->pid;
    }


    /**
     * Return pages in the current root available to the member
     * Necessary to check if a product is allowed in the current site and cache the value
     * @param   array
     * @param   \MemberModel|\FrontendUser
     * @return  array
     */
    public static function getPagesInCurrentRoot(array $arrPages, $objMember = null)
    {
        global $objPage;

        // $objPage not available, we dont know if the page is allowed
        if (null === $objPage || $objPage == 0) {
            return $arrPages;
        }

        static $arrAvailable = array();
        static $arrUnavailable = array();

        $intMember = 0;
        if (null !== $objMember) {
            $intMember = $objMember->id;
            $arrGroups = deserialize($objMember->groups);

            if (!is_array($arrGroups)) {
                $arrGroups = array();
            }
        }

        foreach (array_diff($arrPages, $arrAvailable, $arrUnavailable) as $intPage) {
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
     * @param  array
     * @param  object
     * @return array
     */
    public function addProductToBreadcrumb($arrItems, $objModule)
    {
        if (\Haste\Input\Input::getAutoItem('product', false, true) != '') {
            $objProduct = Product::findAvailableByIdOrAlias(\Haste\Input\Input::getAutoItem('product', false, true));

            if (null !== $objProduct) {

                global $objPage;
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

                    $arrItems[] = array
                    (
                        'isRoot'   => false,
                        'isActive' => true,
                        'href'     => $objProduct->generateUrl($objPage),
                        'title'    => $this->prepareMetaDescription($objProduct->meta_title ? : $objProduct->name),
                        'link'     => $objProduct->name,
                        'data'     => $objPage->row(),
                    );
                }
            }
        }

        return $arrItems;
    }

    /**
     * Load system configuration into page object
     * @param \Database\Result
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
            list($GLOBALS['TL_ADMIN_NAME'], $GLOBALS['TL_ADMIN_EMAIL']) = \System::splitFriendlyName($objPage->adminEmail);
        } else {
            list($GLOBALS['TL_ADMIN_NAME'], $GLOBALS['TL_ADMIN_EMAIL']) = \System::splitFriendlyName($GLOBALS['TL_CONFIG']['adminEmail']);
        }

        // Define the static URL constants
        define('TL_FILES_URL', ($objPage->staticFiles != '' && !$GLOBALS['TL_CONFIG']['debugMode']) ? $objPage->staticFiles . TL_PATH . '/' : '');
        define('TL_SCRIPT_URL', ($objPage->staticSystem != '' && !$GLOBALS['TL_CONFIG']['debugMode']) ? $objPage->staticSystem . TL_PATH . '/' : '');
        define('TL_PLUGINS_URL', ($objPage->staticPlugins != '' && !$GLOBALS['TL_CONFIG']['debugMode']) ? $objPage->staticPlugins . TL_PATH . '/' : '');

        $objLayout = \Database::getInstance()->prepare("SELECT l.*, t.templates FROM tl_layout l LEFT JOIN tl_theme t ON l.pid=t.id WHERE l.id=? ORDER BY l.id=? DESC")
            ->limit(1)
            ->execute($objPage->layout, $objPage->layout);

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
     * @param \Isotope\PostSale
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
}
