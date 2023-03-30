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

use Contao\Controller;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\Database;
use Contao\Database\Result;
use Contao\Date;
use Contao\Environment;
use Contao\FrontendTemplate;
use Contao\FrontendUser;
use Contao\MemberModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Contao\Widget;
use Haste\Input\Input;
use Isotope\EventListener\ChangeLanguageListener;
use Isotope\Frontend\ProductAction\CartAction;
use Isotope\Frontend\ProductAction\FavoriteAction;
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
use Isotope\Model\ProductCollectionSurcharge;
use Isotope\Model\TaxClass;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provide methods to handle Isotope front end components.
 */
class Frontend extends \Contao\Frontend
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
            System::log('Product collection ID "' . $objCollection->getId() . '" is not orderable', __METHOD__, TL_ERROR);
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
        if ($GLOBALS['TL_CONFIG']['useAutoItem'] && \in_array($strKey, $GLOBALS['TL_AUTO_ITEM'], true)) {
            $strKey = 'auto_item';
        }

        for ($i = 1, $c = \count($arrFragments); $i < $c; $i += 2) {
            if ($arrFragments[$i] == $strKey) {
                $strAlias = $arrFragments[$i + 1];
            }
        }

        global $objIsotopeListPage;
        $objIsotopeListPage = null;

        if ($strAlias != '' && ($objPage = PageModel::findPublishedByIdOrAlias($arrFragments[0])) !== null) {

            // Check the URL and language of each page if there are multiple results
            // see Contao's index.php
            if ($objPage !== null && $objPage->count() > 1) {
                $objNewPage = null;
                $arrPages   = array();

                // Order by domain and language
                /** @var PageModel $objCurrentPage */
                foreach ($objPage as $objCurrentPage) {
                    $objCurrentPage->loadDetails();

                    $domain                                           = $objCurrentPage->domain ? : '*';
                    $arrPages[$domain][$objCurrentPage->rootLanguage] = $objCurrentPage;

                    // Also store the fallback language
                    if ($objCurrentPage->rootIsFallback) {
                        $arrPages[$domain]['*'] = $objCurrentPage;
                    }
                }

                $strHost = Environment::get('host');

                // Look for a root page whose domain name matches the host name
                if (isset($arrPages[$strHost])) {
                    $arrLangs = $arrPages[$strHost];
                } elseif (isset($arrPages['*'])) {
                    $arrLangs = $arrPages['*']; // Empty domain
                } else {
                    // No domain match (see #2347)
                    return $arrFragments;
                }

                // Use the first result (see #4872)
                if (!$GLOBALS['TL_CONFIG']['addLanguageToUrl']) {
                    $objNewPage = current($arrLangs);
                } // Try to find a page matching the language parameter
                elseif (($lang = Input::get('language')) != '' && isset($arrLangs[$lang])) {
                    $objNewPage = $arrLangs[$lang];
                }

                // Store the page object
                if (\is_object($objNewPage)) {
                    $objPage = $objNewPage;
                }
            }

            if ('page' === $objPage->iso_readerMode && ($objReader = $objPage->getRelated('iso_readerJumpTo')) !== null) {
                /** @var PageModel $objIsotopeListPage */
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
     * @param PageModel $objPage
     */
    public function overrideReaderPage($objPage)
    {
        /** @var PageModel $objIsotopeListPage */
        global $objIsotopeListPage;

        if (null !== $objIsotopeListPage) {
            $originalPageId = $objPage->id;
            $arrTrail   = $objIsotopeListPage->trail;
            $arrTrail[] = $originalPageId;

            $objPage->id = $objIsotopeListPage->id;
            $objPage->pid = $objIsotopeListPage->pid;
            $objPage->alias = $objIsotopeListPage->alias;
            $objPage->trail = $arrTrail;
            $objPage->languageMain = $objIsotopeListPage->languageMain;

            $objIsotopeListPage->pid = $originalPageId;
        }
    }

    public function overrideArticles($pageId, $strColumn)
    {
        global $objPage;
        global $objIsotopeListPage;

        if (!$objIsotopeListPage || $pageId === $objIsotopeListPage->pid) {
            return false;
        }

        $objPage->id = $objIsotopeListPage->pid;

        $articles = Controller::getFrontendModule(0, $strColumn);

        $objPage->id = $objIsotopeListPage->id;

        return $articles;
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
        $t         = PageModel::getTable();
        $time      = Date::floorToMinute();
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

        $objRoots = PageModel::findBy($arrColumn, $arrValue);

        if (null !== $objRoots) {
            foreach ($objRoots as $objRoot) {
                $arrPageIds   = Database::getInstance()->getChildRecords($objRoot->id, $t, false);
                $arrPageIds[] = $intRoot;

                $objProducts = Product::findPublishedByCategories($arrPageIds);

                if (null !== $objProducts) {
                    foreach ($objProducts as $objProduct) {

                        if (!$objProduct->isPublished()) {
                            continue;
                        }

                        // Find the categories in the current root
                        $arrCategories = array_intersect($objProduct->getCategories(), $arrPageIds);
                        $intRemaining  = \count($arrCategories);
                        $objPages = PageModel::findMultipleByIds($arrCategories) ?: [];

                        foreach ($objPages as $objPage) {
                            --$intRemaining;

                            // The target page does not exist
                            if ($objPage === null || 'none' === $objPage->iso_readerMode) {
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

                            // Pass root language to page object
                            $objPage->language = $objRoot->language;

                            $arrPages[] = $objProduct->generateUrl($objPage, true);

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
     * @deprecated Deprecated since Isotope 2.4, to be removed in Isotope 3.0.
     */
    public function saveUpload($varValue, IsotopeProduct $objProduct, Widget $objWidget)
    {
        if (\is_array($_SESSION['FILES'][$objWidget->name])
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
     * Store the current article ID so we know it for the product list
     *
     * @param Result $objRow
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
     * @param MemberModel|FrontendUser $objMember
     *
     * @return array
     */
    public static function getPagesInCurrentRoot(array $arrPages, $objMember = null)
    {
        if (0 === \count($arrPages)) {
            return $arrPages;
        }

        /** @var PageModel $objPage */
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
            $arrGroups = StringUtil::deserialize($objMember->groups, true);
        }

        if (!isset($arrAvailable[$intMember])) {
            $arrAvailable[$intMember] = array();
        }

        if (!isset($arrUnavailable[$intMember])) {
            $arrUnavailable[$intMember] = array();
        }

        // Load remaining (not cached) pages.
        foreach (array_diff($arrPages, $arrAvailable[$intMember], $arrUnavailable[$intMember]) as $intPage) {
            $objPageDetails = PageModel::findWithDetails($intPage);

            // Page is not in the current root
            if (null === $objPageDetails || $objPageDetails->rootId != $objPage->rootId) {
                continue;
            }

            // Page is for guests only but we have a member
            if ($objPageDetails->guests && $intMember > 0 && !$objPageDetails->protected) {
                $arrUnavailable[$intMember][] = $intPage;
                continue;
            }

            if ($objPageDetails->protected) {
                $arrPGroups = StringUtil::deserialize($objPageDetails->groups);

                // Page is protected but has no groups
                if (!\is_array($arrPGroups)) {
                    $arrUnavailable[$intMember][] = $intPage;
                    continue;
                }

                // Page is protected but we have no member
                if ($intMember == 0) {
                    if (in_array(-1, $arrPGroups, false)) { // "Guests" group in Contao 4.13+
                        $arrAvailable[$intMember][] = $intPage;
                    } else {
                        $arrUnavailable[$intMember][] = $intPage;
                    }
                    continue;
                }

                // Page groups do not match with member groups
                if (\count(array_intersect($arrGroups, $arrPGroups)) == 0) {
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
        /** @var PageModel $objPage */
        global $objPage;

        if ($objPage->type === 'error_404'
            || $objPage->type === 'error_403'
            || !($alias = Input::getAutoItem('product', false, true))
            || ($objProduct = Product::findAvailableByIdOrAlias($alias)) === null
        ) {
            return $arrItems;
        }

        global $objIsotopeListPage;
        $last = \count($arrItems) - 1;

        // If we have a reader page that is a level below the list, rename the last item (the reader) to the product title
        if (null !== $objIsotopeListPage && $objPage->pid == $objIsotopeListPage->id) {
            $arrItems[$last]['title'] = $this->prepareMetaDescription($objProduct->meta_title ? : $objProduct->name);
            $arrItems[$last]['link']  = $objProduct->name;
        } else {
            $listPage = $objIsotopeListPage ?: $objPage;
            $originalRow = $listPage->originalRow();

            // Replace the current page (if breadcrumb is insert tag, it would already be the product name)
            $arrItems[$last] = array(
                'isRoot'   => (bool) $arrItems[$last]['isRoot'],
                'isActive' => false,
                'href'     => $listPage->getFrontendUrl(),
                'title'    => StringUtil::specialchars($originalRow['pageTitle'] ?: $originalRow['title']),
                'link'     => $originalRow['title'],
                'data'     => $originalRow,
                'class'    => ''
            );

            // Add a new item for the current product
            $arrItems[] = array(
                'isRoot'   => false,
                'isActive' => true,
                'href'     => $objProduct->generateUrl($objPage),
                'title'    => StringUtil::specialchars($this->prepareMetaDescription($objProduct->meta_title ? : $objProduct->name)),
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

        // Define the static URL constants
        $isDebugMode = System::getContainer()->getParameter('kernel.debug');
        \define('TL_FILES_URL', ($objPage->staticFiles != '' && !$isDebugMode) ? $objPage->staticFiles . TL_PATH . '/' : '');
        \define('TL_ASSETS_URL', ($objPage->staticPlugins != '' && !$isDebugMode) ? $objPage->staticPlugins . TL_PATH . '/' : '');
        \define('TL_SCRIPT_URL', TL_ASSETS_URL);
        \define('TL_PLUGINS_URL', TL_ASSETS_URL);

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
            [$strFormat, $strVariant] = explode('_', $objLayout->doctype);
            $objPage->outputFormat  = $strFormat;
            $objPage->outputVariant = $strVariant;
        }

        self::setLanguage($objPage->language);

        return $objPage;
    }

    /**
     * Adjust module and module id for certain payment and/or shipping modules
     *
     * @param PostSale $objPostsale
     */
    public function setPostsaleModuleSettings(PostSale $objPostsale)
    {
        // Payment method "Payone"
        $strParam = Input::post('param');

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
        $fltAmount = (float) $fltPrice;

        if ($objSource instanceof IsotopePrice
            && ($objProduct = $objSource->getRelated('pid')) instanceof IsotopeProduct
            && $objProduct->getType() !== null) {
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
                    $value = $objAttribute->isCustomerDefined() ? ($arrOptions[$field] ?? null) : $objProduct->$field;
                    $value = StringUtil::deserialize($value, true);

                    /** @var AttributeOption $objOption */
                    foreach ($objOptions as $objOption) {
                        if (\in_array($objOption->getLanguageId(), $value)) {
                            // Do not use getAmount() for non-percentage price, it would run Isotope::calculatePrice again (see isotope/core#2342)
                            $amount = $objOption->isPercentage() ? $objOption->getAmount($fltPrice, 0) : (float) $objOption->price;
                            $objTax = $objSource->getRelated('tax_class');

                            if ($objOption->isPercentage() || !$objTax instanceof TaxClass) {
                                $fltAmount += $amount;
                                continue;
                            }

                            if ('net_price' === $strField) {
                                $fltAmount += $objTax->calculateNetPrice($amount);
                            } elseif ('gross_price' === $strField) {
                                $fltAmount += $objTax->calculateGrossPrice($amount);
                            } else {
                                $fltAmount += $amount;
                            }
                        }
                    }
                }
            }
        }

        return $fltAmount;
    }

    /**
     * Callback for add_to_cart button
     *
     * @param IsotopeProduct $objProduct
     * @param array          $arrConfig
     *
     * @deprecated Deprecated since Isotope 2.5
     */
    public function addToCart(IsotopeProduct $objProduct, array $arrConfig = array())
    {
        $action = new CartAction();
        $action->handleSubmit($objProduct, $arrConfig);
    }

    /**
     * Callback for add_to_cart button if a product is being edited.
     *
     * @param IsotopeProduct $objProduct
     * @param array          $arrConfig
     *
     * @deprecated Deprecated since Isotope 2.5
     */
    public function updateCart(IsotopeProduct $objProduct, array $arrConfig = array())
    {
        $action = new CartAction();
        $action->handleSubmit($objProduct, $arrConfig);
    }

    /**
     * Callback for toggle_favorites button
     *
     * @param IsotopeProduct $objProduct
     * @param array          $arrConfig
     *
     * @deprecated Deprecated since Isotope 2.5
     */
    public function toggleFavorites(IsotopeProduct $objProduct, array $arrConfig = array())
    {
        $action = new FavoriteAction();
        $action->handleSubmit($objProduct, $arrConfig);
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
     * Hook callback for changelanguage extension to support language switching on product reader page
     *
     * @param array $arrGet
     *
     * @return array
     *
     * @deprecated Deprecated since Isotope 2.4. See ChangeLanguageListener
     */
    public function translateProductUrls($arrGet)
    {
        return (new ChangeLanguageListener())->onTranslateUrlParameters($arrGet);
    }

    /**
     * Return all error, confirmation and info messages as HTML string
     *
     * @return string
     *
     * @deprecated use Message::generate
     */
    public static function getIsotopeMessages()
    {
        return Message::generate();
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
