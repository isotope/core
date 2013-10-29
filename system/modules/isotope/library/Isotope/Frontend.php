<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope;

use Isotope\Interfaces\IsotopeProduct;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\Product;
use Isotope\Model\ProductCollection\Order;


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
    public function addToCart($objProduct, array $arrConfig=array())
    {
        $objModule = $arrConfig['module'];
        $intQuantity = ($objModule->iso_use_quantity && intval(\Input::post('quantity_requested')) > 0) ? intval(\Input::post('quantity_requested')) : 1;

        if (Isotope::getCart()->addProduct($objProduct, $intQuantity, $arrConfig) !== false)
        {
            $_SESSION['ISO_CONFIRM'][] = $GLOBALS['TL_LANG']['MSC']['addedToCart'];

            if (!$objModule->iso_addProductJumpTo) {
                $this->reload();
            }

            \Controller::redirect(static::addQueryStringToUrl('continue='.base64_encode($this->Environment->request), $objModule->iso_addProductJumpTo));
        }
    }

    /**
     * Replace the current page with a reader page if applicable
     * @param   array
     * @return  array
     */
    public function loadReaderPageFromUrl($arrFragments)
    {
        $strKey = 'product';
        $strAlias = '';

        // Find products alias. Can't use Input because they're not yet initialized
        if ($GLOBALS['TL_CONFIG']['useAutoItem'] && in_array($strKey, $GLOBALS['TL_AUTO_ITEM'])) {
            $strKey = 'auto_item';
        }

        for ($i=1, $c=count($arrFragments); $i<$c; $i+=2) {
            if ($arrFragments[$i] == $strKey) {
                $strAlias = $arrFragments[$i+1];
            }
        }

        global $objIsotopeListPage;
        $objIsotopeListPage = null;

        if ($strAlias != '' && ($objPage = \PageModel::findPublishedByIdOrAlias($arrFragments[0])) !== null) {

            // Check the URL and language of each page if there are multiple results
            // see Contao's index.php
    		if ($objPage !== null && $objPage->count() > 1)
    		{
    			$objNewPage = null;
    			$arrPages = array();

    			// Order by domain and language
    			while ($objPage->next())
    			{
    				$objCurrentPage = $objPage->current()->loadDetails();

    				$domain = $objCurrentPage->domain ?: '*';
    				$arrPages[$domain][$objCurrentPage->rootLanguage] = $objCurrentPage;

    				// Also store the fallback language
    				if ($objCurrentPage->rootIsFallback)
    				{
    					$arrPages[$domain]['*'] = $objCurrentPage;
    				}
    			}

    			$strHost = Environment::get('host');

    			// Look for a root page whose domain name matches the host name
    			if (isset($arrPages[$strHost]))
    			{
    				$arrLangs = $arrPages[$strHost];
    			}
    			else
    			{
    				$arrLangs = $arrPages['*']; // Empty domain
    			}

    			// Use the first result (see #4872)
    			if (!$GLOBALS['TL_CONFIG']['addLanguageToUrl'])
    			{
    				$objNewPage = current($arrLangs);
    			}
    			// Try to find a page matching the language parameter
    			elseif (($lang = Input::get('language')) != '' && isset($arrLangs[$lang]))
    			{
    				$objNewPage = $arrLangs[$lang];
    			}

    			// Store the page object
    			if (is_object($objNewPage))
    			{
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
     *
     */
    public function overrideReaderPage($objPage, $objLayout, $objRegularPage)
    {
        global $objPage;
        global $objIsotopeListPage;

        if (null !== $objIsotopeListPage) {
            $arrTrail = $objIsotopeListPage->trail;
            $arrTrail[] = $objPage->id;

            $objPage->pid = $objIsotopeListPage->id;
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

        if ($arrTag[0] == 'isotope' || $arrTag[0] == 'cache_isotope')
        {
            switch ($arrTag[1])
            {
                case 'cart_items';

                    return Isotope::getCart()->countItems();
                    break;

                case 'cart_quantity';

                    return Isotope::getCart()->sumItemsQuantity();
                    break;

                case 'cart_items_label';
                    $intCount = Isotope::getCart()->countItems();

                    if (!$intCount)
                    {
                        return '';
                    }

                    return $intCount == 1 ? ('('.$GLOBALS['TL_LANG']['MSC']['productSingle'].')') : sprintf(('('.$GLOBALS['TL_LANG']['MSC']['productMultiple'].')'), $intCount);
                    break;

                case 'cart_quantity_label';
                    $intCount = Isotope::getCart()->sumItemsQuantity();

                    if (!$intCount)
                    {
                        return '';
                    }

                    return $intCount == 1 ? ('('.$GLOBALS['TL_LANG']['MSC']['productSingle'].')') : sprintf(('('.$GLOBALS['TL_LANG']['MSC']['productMultiple'].')'), $intCount);
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
        }
        elseif ($arrTag[0] == 'isolabel')
        {
            return Translation::get($arrTag[1], $arrTag[2]);
        }
        elseif ($arrTag[0] == 'order')
        {
            if (($objOrder = Order::findOneByUniqid(\Input::get('uid'))) !== null)
            {
                return $objOrder->{$arrTag[1]};
            }

            return '';
        }
        elseif ($arrTag[0] == 'product')
        {
            // 2 possible use cases:
            // {{product::attribute}}                - gets the data of the current product ($GLOBALS['ACTIVE_PRODUCT'] or GET parameter "product")
            // {{product::attribute::product_id}}    - gets the data of the specified product ID

            $objProduct = (count($arrTag) == 3) ? static::getProduct($arrTag[2]) : ($GLOBALS['ACTIVE_PRODUCT'] ? $GLOBALS['ACTIVE_PRODUCT'] : static::getProductByAlias(static::getAutoItem('product')));

            return ($objProduct !== null) ? $objProduct->{$arrTag[1]} : '';
        }

        return false;
    }


    /**
     * Apply a watermark to an image
     * @param string
     * @param string
     * @param string
     * @param string
     */
    public static function watermarkImage($image, $watermark, $position='br', $target=null)
    {
        $image = urldecode($image);

        if (!is_file(TL_ROOT . '/' . $image) || !is_file(TL_ROOT . '/' . $watermark))
        {
            return $image;
        }

        $objFile = new \File($image);
        $strCacheName = 'system/html/' . $objFile->filename . '-' . substr(md5($watermark . '-' . $position . '-' . $objFile->mtime), 0, 8) . '.' . $objFile->extension;

        // Return the path of the new image if it exists already
        if (is_file(TL_ROOT . '/' . $strCacheName))
        {
            return $strCacheName;
        }

        // !HOOK: override image watermark routine
        if (isset($GLOBALS['ISO_HOOKS']['watermarkImage']) && is_array($GLOBALS['ISO_HOOKS']['watermarkImage']))
        {
            foreach ($GLOBALS['ISO_HOOKS']['watermarkImage'] as $callback)
            {
                $objCallback = \System::importStatic($callback[0]);
                $return = $objCallback->$callback[1]($image, $watermark, $position, $target);

                if (is_string($return))
                {
                    return $return;
                }
            }
        }

        $arrGdinfo = gd_info();

        // Load image
        switch ($objFile->extension)
        {
            case 'gif':
                if ($arrGdinfo['GIF Read Support'])
                {
                    $strImage = imagecreatefromgif(TL_ROOT . '/' . $image);
                }
                break;

            case 'jpg':
            case 'jpeg':
                if ($arrGdinfo['JPG Support'] || $arrGdinfo['JPEG Support'])
                {
                    $strImage = imagecreatefromjpeg(TL_ROOT . '/' . $image);
                }
                break;

            case 'png':
                if ($arrGdinfo['PNG Support'])
                {
                    $strImage = imagecreatefrompng(TL_ROOT . '/' . $image);
                }
                break;
        }

        // Image could not be read
        if (!$strImage)
        {
            return $image;
        }

        $objWatermark = new \File($watermark);

        // Load watermark
        switch ($objWatermark->extension)
        {
            case 'gif':
                if ($arrGdinfo['GIF Read Support'])
                {
                    $strWatermark = imagecreatefromgif(TL_ROOT . '/' . $watermark);
                }
                break;

            case 'jpg':
            case 'jpeg':
                if ($arrGdinfo['JPG Support'] || $arrGdinfo['JPEG Support'])
                {
                    $strWatermark = imagecreatefromjpeg(TL_ROOT . '/' . $watermark);
                }
                break;

            case 'png':
                if ($arrGdinfo['PNG Support'])
                {
                    $strWatermark = imagecreatefrompng(TL_ROOT . '/' . $watermark);
                }
                break;
        }

        // Image could not be read
        if (!$strWatermark)
        {
            return $image;
        }

        switch ($position)
        {
            case 'tl':
                $x = 0;
                $y = 0;
                break;

            case 'tc':
                $x = ($objFile->width/2) - ($objWatermark->width/2);
                $y = 0;
                break;

            case 'tr':
                $x = $objFile->width - $objWatermark->width;
                $y = 0;
                break;

            case 'cc':
                $x = ($objFile->width/2) - ($objWatermark->width/2);
                $y = ($objFile->height/2) - ($objWatermark->height/2);
                break;

            case 'bl':
                $x = 0;
                $y = $objFile->height - $objWatermark->height;
                break;

            case 'bc':
                $x = ($objFile->width/2) - ($objWatermark->width/2);
                $y = $objFile->height - $objWatermark->height;
                break;

            case 'br':
            default:
                $x = $objFile->width - $objWatermark->width;
                $y = $objFile->height - $objWatermark->height;
                break;
        }

        imagecopy($strImage, $strWatermark, $x, $y, 0, 0, $objWatermark->width, $objWatermark->height);

        // Fallback to PNG if GIF ist not supported
        if ($objFile->extension == 'gif' && !$arrGdinfo['GIF Create Support'])
        {
            $objFile->extension = 'png';
        }

        // Create the new image
        switch ($objFile->extension)
        {
            case 'gif':
                imagegif($strImage, TL_ROOT . '/' . $strCacheName);
                break;

            case 'jpg':
            case 'jpeg':
                imagejpeg($strImage, TL_ROOT . '/' . $strCacheName, (!$GLOBALS['TL_CONFIG']['jpgQuality'] ? 80 : $GLOBALS['TL_CONFIG']['jpgQuality']));
                break;

            case 'png':
                imagepng($strImage, TL_ROOT . '/' . $strCacheName);
                break;
        }

        // Destroy the temporary images
        imagedestroy($strImage);
        imagedestroy($strWatermark);

        // Resize the original image
        if ($target)
        {
            $objFiles = \Files::getInstance();
            $objFiles->copy($strCacheName, $target);

            return $target;
        }

        // Set the file permissions when the Safe Mode Hack is used
        if ($GLOBALS['TL_CONFIG']['useFTP'])
        {
            $objFiles = \Files::getInstance();
            $objFiles->chmod($strCacheName, 0644);
        }

        // Return the path to new image
        return $strCacheName;
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
        if (static::getAutoItem('product') != '')
        {
            $arrGet['url']['product'] = static::getAutoItem('product');
        }
        elseif (static::getAutoItem('step') != '')
        {
            $arrGet['url']['step'] = static::getAutoItem('step');
        }
        elseif (\Input::get('uid') != '')
        {
            $arrGet['get']['uid'] = \Input::get('uid');
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

        $strMessages = \Isotope\Frontend::getIsotopeMessages();

        if ($strMessages != '') {
            $GLOBALS['TL_MOOTOOLS'][] = "
<script>
window.addEvent('domready', function()
{
    Isotope.displayBox('" . $strMessages . "', true);
});
</script>";
        }
    }


    /**
     * Return all error, confirmation and info messages as HTML string
     * @return string
     */
    public static function getIsotopeMessages()
    {
        $strMessages = '';
        $arrGroups = array('ISO_ERROR', 'ISO_CONFIRM', 'ISO_INFO');

        foreach ($arrGroups as $strGroup)
        {
            if (!is_array($_SESSION[$strGroup]))
            {
                continue;
            }

            $strClass = strtolower($strGroup);

            foreach ($_SESSION[$strGroup] as $strMessage)
            {
                $strMessages .= sprintf('<p class="%s">%s</p>', $strClass, $strMessage);
            }

            $_SESSION[$strGroup] = array();
        }

        $strMessages = trim($strMessages);

        if (strlen($strMessages))
        {
            // Automatically disable caching if a message is available
            global $objPage;
            $objPage->cache = 0;

            $strMessages = '<div class="iso_message">' . $strMessages . '</div>';
        }

        return $strMessages;
    }


    /**
     * Shortcut for a single product by ID or from database result
     * @param IsotopeProduct|int
     * @param boolean
     * @return IsotopeProduct|null
     * @todo    should use the model instead of this method
     */
    public static function getProduct($objProduct, $blnCheckAvailability=true)
    {
        if (is_numeric($objProduct))
        {
            $objProduct = Product::findPublishedByPk($objProduct);
        }

        if (null === $objProduct || !($objProduct instanceof IsotopeProduct))
        {
            return null;
        }

        if ($blnCheckAvailability && !$objProduct->isAvailableInFrontend())
        {
            return null;
        }

        return $objProduct;
    }


    /**
     * Shortcut for a single product by alias (from url?)
     * @param string
     * @param boolean
     * @return IsotopeProduct|null
     */
    public static function getProductByAlias($strAlias, $blnCheckAvailability=true)
    {
        return static::getProduct(Product::findPublishedByIdOrAlias($strAlias), $blnCheckAvailability);
    }


    /**
     * Generate products from database result or array of IDs
     * @param \Database\Result|array
     * @param boolean
     * @param array
     * @param array
     * @return array
     */
    public static function getProducts($objProducts, $blnCheckAvailability=true, array $arrFilters=array(), array $arrSorting=array())
    {
        // Could be an empty array
        if (empty($objProducts)) {
            return array();
        }

        // $objProducts can also be an array of product ids
        if (is_array($objProducts)) {
            $objProducts = Product::findPublishedById($objProducts, array(
                'group' => Product::getTable().'.id',
                'order' => \Database::getInstance()->findInSet(Product::getTable().'.id', $objProducts)
            ));
        }

        if (null === $objProducts) {
            return array();
        }

        $arrProducts = array();

        // Reset DB iterator (see #22)
        $objProducts->reset();

        while ($objProducts->next()) {
            $objProduct = \Isotope\Frontend::getProduct($objProducts->current(), $blnCheckAvailability);

            if ($objProduct !== null) {
                $arrProducts[$objProducts->id] = $objProduct;
            }
        }

        if (!empty($arrFilters)) {
            $arrProducts = array_filter($arrProducts, function ($objProduct) use ($arrFilters) {
                $arrGroups = array();

                foreach ($arrFilters as $objFilter) {
                    $blnMatch = $objFilter->matches($objProduct);

                    if ($objFilter->hasGroup()) {
                        $arrGroups[$objFilter->getGroup()] = $arrGroups[$objFilter->getGroup()] ?: $blnMatch;
                    } elseif (!$blnMatch) {
                        return false;
                    }
                }

                if (!empty($arrGroups) && in_array(false, $arrGroups)) {
                    return false;
                }

                return true;
            });
        }

        // $arrProducts can be empty if the filter removed all records
        if (!empty($arrSorting) && !empty($arrProducts)) {
            $arrParam = array();
            $arrData = array();

            foreach ($arrSorting as $strField => $arrConfig) {
                foreach ($arrProducts as $id => $objProduct) {

                    // Both SORT_STRING and SORT_REGULAR are case sensitive, strings starting with a capital letter will come before strings starting with a lowercase letter.
                    // To perform a case insensitive search, force the sorting order to be determined by a lowercase copy of the original value.
                    $arrData[$strField][$id] = strtolower(str_replace('"', '', $objProduct->$strField));
                }

                $arrParam[] = &$arrData[$strField];
                $arrParam[] = $arrConfig[0];
                $arrParam[] = $arrConfig[1];
            }

            // Add product array as the last item. This will sort the products array based on the sorting of the passed in arguments.
            eval('array_multisort($arrParam[' . implode('], $arrParam[', array_keys($arrParam)) . '], $arrProducts);');
        }

        return $arrProducts;
    }


    /**
     * Generate row class for an array
     * @param array data rows
     * @param string class prefix (e.g. "product")
     * @param int number of columns
     * @return array
     */
    public static function generateRowClass($arrData, $strClass='', $strKey='rowClass', $intColumns=0, $options=125)
    {
        $strClassPrefix = $strClass == '' ? '' : $strClass.'_';
        $hasColumns = ($intColumns > 1);
        $total = count($arrData) - 1;
        $current = 0;

        if ($hasColumns)
        {
            $row = 0;
            $col = 0;
            $rows = ceil(count($arrData) / $intColumns) - 1;
            $cols = $intColumns - 1;
        }

        foreach ($arrData as $k => $varValue)
        {
            if ($hasColumns && $current > 0 && $current % $intColumns == 0)
            {
                ++$row;
                $col = 0;
            }

            $class = '';

            if ($options & ISO_CLASS_NAME)
            {
                $class .= ' ' . $strClass;
            }

            if ($options & ISO_CLASS_KEY)
            {
                $class .= ' ' . $strClassPrefix . $k;
            }

            if ($options & ISO_CLASS_COUNT)
            {
                $class .= ' ' . $strClassPrefix . $current;
            }

            if ($options & ISO_CLASS_EVENODD)
            {
                $class .= ' ' . (($options & ISO_CLASS_NAME || $options & ISO_CLASS_ROW) ? $strClassPrefix : '') . ($current%2 ? 'even' : 'odd');
            }

            if ($options & ISO_CLASS_FIRSTLAST)
            {
                $class .= ($current == 0 ? ' ' . $strClassPrefix . 'first' : '') . ($current == $total ? ' ' . $strClassPrefix . 'last' : '');
            }

            if ($hasColumns && $options & ISO_CLASS_ROW)
            {
                $class .= ' row_'.$row . ($row%2 ? ' row_even' : ' row_odd') . ($row == 0 ? ' row_first' : '') . ($row == $rows ? ' row_last' : '');
            }

            if ($hasColumns && $options & ISO_CLASS_COL)
            {
                $class .= ' col_'.$col . ($col%2 ? ' col_even' : ' col_odd') . ($col == 0 ? ' col_first' : '') . ($col == $cols ? ' col_last' : '');
            }

            if (is_array($varValue))
            {
                $arrData[$k][$strKey] = trim($arrData[$k][$strKey] . $class);
            }
            elseif (is_object($varValue))
            {
                $varValue->$strKey = trim($varValue->$strKey . $class);
                $arrData[$k] = $varValue;
            }
            else
            {
                $arrData[$k] = '<span class="' . trim($arrData[$k][$strKey] . $class) . '">' . $varValue . '</span>';
            }

            ++$col;
            ++$current;
        }

        return $arrData;
    }


    /**
     * Format surcharge prices
     * @param array
     * @return array
     */
    public static function formatSurcharges($arrSurcharges)
    {
        $i = 0;
        $arrReturn = array();

        foreach ($arrSurcharges as $k => $objSurcharge)
        {
            $arrReturn[$k] = $objSurcharge->row();
            $arrReturn[$k]['price']          = Isotope::formatPriceWithCurrency($objSurcharge->price);
            $arrReturn[$k]['total_price']    = Isotope::formatPriceWithCurrency($objSurcharge->total_price);
            $arrReturn[$k]['rowClass']       = trim('foot_'.(++$i) . ' ' . $objSurcharge->rowClass);
            $arrReturn[$k]['tax_id']         = $objSurcharge->getTaxNumbers();

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
    public function addProductsToSearchIndex($arrPages, $intRoot=0, $blnSitemap=false, $strLanguage=null)
    {
        $arrRoots = array();

        // If we have a root page id (sitemap.xml e.g.) we have to make sure we only consider categories in this tree
        if ($intRoot > 0) {
            $arrPageIds = \Database::getInstance()->getChildRecords($intRoot, \PageModel::getTable(), false);
            $arrPageIds[] = $intRoot;

            $objProducts = Product::findPublishedByCategories($arrPageIds);
            $objRoot = \PageModel::findByPk($intRoot);
        } else {
            $objProducts = Product::findPublished();
        }

        while ($objProducts->next()) {

            // Do the fun for all categories
            $arrCategories = $objProducts->current()->getCategories();

            foreach ($arrCategories as $intPage) {

                $objPage = \PageModel::findWithDetails($intPage);

                // No need to get the root page model of the page if it's restricted to one only anyway
                // Otherwise we need to get the root page model of the current page and for performance
                // reasons we cache that in an array
                if ($intRoot === 0) {
                    if (!isset($arrRoots[$intPage])) {
                        $arrRoots[$intPage] = \PageModel::findByPk($objPage->rootId);
                        $arrPageIds = \Database::getInstance()->getChildRecords($objPage->rootId, \PageModel::getTable(), false);
                        $arrPageIds[] = $objPage->rootId;
                    }

                    $objRoot = $arrRoots[$intPage];
                }

                // Do not generate a reader for the index page, except if it is the only one
                if ($objPage->alias == 'index' && count(array_intersect($arrCategories, $arrPageIds)) > 1) {
                    continue;
                }

                // Generate the absolute URL
                $strDomain = \Environment::get('base');

                // Overwrite the domain
                if ($objRoot->dns != '') {
                    $strDomain = ($objRoot->useSSL ? 'https://' : 'http://') . $objRoot->dns . TL_PATH . '/';
                }

                $arrPages[] = $strDomain . $objProducts->current()->generateUrl($objPage);

                // Only take the first matching category because this is our primary
                // one and multiple canonical links are not allowed
                break;
            }
        }

        // The reader page id can be the same for several categories so we have to make sure we only index the product once
        return array_unique($arrPages);
    }


    /**
     * save_callback for upload widget to store $_FILES data into the product
     * @param mixed
     * @param IsotopeProduct
     */
    public function saveUpload($varValue, IsotopeProduct $objProduct, Widget $objWidget)
    {
        if (is_array($_SESSION['FILES'][$objWidget->name]) && $_SESSION['FILES'][$objWidget->name]['uploaded'] == '1' && $_SESSION['FILES'][$objWidget->name]['error'] == 0)
        {
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

        foreach (trimsplit(',', $strPostalCodes) as $strCode)
        {
            $arrCode = trimsplit('-', $strCode);

            // Ignore codes with more than 1 range
            switch (count($arrCode))
            {
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
     * Add a request string to the given URI string or page ID
     * @param   string
     * @param   mixed
     * @return  string
     * @throws  \InvalidArgumentException
     */
    public static function addQueryStringToUrl($strRequest, $varUrl=null)
    {
        if ($varUrl === null) {
            $varUrl = \Environment::getInstance()->request;

        } elseif (is_numeric($varUrl)) {
            if (($objJump = \PageModel::findByPk($varUrl)) === null) {
                throw new \InvalidArgumentException('Given page id does not exist.');
            }
            $varUrl = \Controller::generateFrontendUrl($objJump->row());
        }

        if ($strRequest === '') {
            return $varUrl;
        }

        list($strScript, $strQueryString) = explode('?', $varUrl, 2);

        $strRequest = preg_replace('/^&(amp;)?/i', '', $strRequest);
        $queries = preg_split('/&(amp;)?/i', $strQueryString);

        // Overwrite existing parameters and ignore "language", see #64
        foreach ($queries as $k=>$v) {
            $explode = explode('=', $v, 2);

            if ($v === '' || $k === 'language' || preg_match('/(^|&(amp;)?)' . preg_quote($explode[0], '/') . '=/i', $strRequest)) {
                unset($queries[$k]);
            }
        }

        $href = '?';

        if (!empty($queries)) {
            $href .= implode('&amp;', $queries) . '&amp;';
        }

        return $strScript . $href . str_replace(' ', '%20', $strRequest);
    }


    /**
     * Wait for it
     * @return bool
     */
    public static function setTimeout($intSeconds=5, $intRepeat=12)
    {
        if (!isset($_SESSION['ISO_TIMEOUT']))
        {
            $_SESSION['ISO_TIMEOUT'] = $intRepeat;
        }
        else
        {
            $_SESSION['ISO_TIMEOUT'] = $_SESSION['ISO_TIMEOUT'] - 1;
        }

        if ($_SESSION['ISO_TIMEOUT'] > 0)
        {
            // Reload page every 5 seconds
            $GLOBALS['TL_HEAD'][] = '<meta http-equiv="refresh" content="' . $intSeconds . ',' . \Environment::get('base') . \Environment::get('request') . '">';

            return true;
        }

        return false;
    }


    /**
     * Cancel the timeout (clear session)
     */
    public static function clearTimeout()
    {
        unset($_SESSION['ISO_TIMEOUT']);
    }


    /**
     * Store the current article ID so we know it for the product list
     * @param \Database\Result
     */
    public function storeCurrentArticle($objRow)
    {
        $GLOBALS['ISO_CONFIG']['current_article']['id'] = $objRow->id;
        $GLOBALS['ISO_CONFIG']['current_article']['pid'] = $objRow->pid;
    }


    /**
     * Return pages in the current root available to the member
     * Necessary to check if a product is allowed in the current site and cache the value
     * @param   array
     * @param   \MemberModel|\FrontendUser
     * @return  array
     */
    public static function getPagesInCurrentRoot(array $arrPages, $objMember=null)
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
        if (static::getAutoItem('product') != '') {
            $objProduct = static::getProductByAlias(static::getAutoItem('product'));

            if (null !== $objProduct) {

                global $objPage;
                global $objIsotopeListPage;

                $last = count($arrItems) - 1;

                // If we have a reader page, rename the last item (the reader) to the product title
                if (null !== $objIsotopeListPage) {
                    $arrItems[$last]['title'] = $this->prepareMetaDescription($objProduct->meta_title ?: $objProduct->name);
                    $arrItems[$last]['link'] = $objProduct->name;
                }

                // Otherwise we add a new item for the product at the last position
                else {
                    $arrItems[$last]['isActive'] = false;

                    $arrItems[] = array
                    (
                        'isRoot'    => false,
                        'isActive'  => true,
                        'href'      => $objProduct->generateUrl($objPage),
                        'title'     => $this->prepareMetaDescription($objProduct->meta_title ?: $objProduct->name),
                        'link'      => $objProduct->name,
                        'data'      => $objPage->row(),
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
        if ($objPage->dateFormat == '')
        {
            $objPage->dateFormat = $GLOBALS['TL_CONFIG']['dateFormat'];
        }

        if ($objPage->timeFormat == '')
        {
            $objPage->timeFormat = $GLOBALS['TL_CONFIG']['timeFormat'];
        }

        if ($objPage->datimFormat == '')
        {
            $objPage->datimFormat = $GLOBALS['TL_CONFIG']['datimFormat'];
        }

        // Set the admin e-mail address
        if ($objPage->adminEmail != '')
        {
            list($GLOBALS['TL_ADMIN_NAME'], $GLOBALS['TL_ADMIN_EMAIL']) = \System::splitFriendlyName($objPage->adminEmail);
        }
        else
        {
            list($GLOBALS['TL_ADMIN_NAME'], $GLOBALS['TL_ADMIN_EMAIL']) = \System::splitFriendlyName($GLOBALS['TL_CONFIG']['adminEmail']);
        }

        // Define the static URL constants
        define('TL_FILES_URL', ($objPage->staticFiles != '' && !$GLOBALS['TL_CONFIG']['debugMode']) ? $objPage->staticFiles . TL_PATH . '/' : '');
        define('TL_SCRIPT_URL', ($objPage->staticSystem != '' && !$GLOBALS['TL_CONFIG']['debugMode']) ? $objPage->staticSystem . TL_PATH . '/' : '');
        define('TL_PLUGINS_URL', ($objPage->staticPlugins != '' && !$GLOBALS['TL_CONFIG']['debugMode']) ? $objPage->staticPlugins . TL_PATH . '/' : '');

        $objLayout = \Database::getInstance()->prepare("SELECT l.*, t.templates FROM tl_layout l LEFT JOIN tl_theme t ON l.pid=t.id WHERE l.id=? ORDER BY l.id=? DESC")
                                            ->limit(1)
                                            ->execute($objPage->layout, $objPage->layout);

        if ($objLayout->numRows)
        {
            // Get the page layout
            $objPage->template = strlen($objLayout->template) ? $objLayout->template : 'fe_page';
            $objPage->templateGroup = $objLayout->templates;

            // Store the output format
            list($strFormat, $strVariant) = explode('_', $objLayout->doctype);
            $objPage->outputFormat = $strFormat;
            $objPage->outputVariant = $strVariant;
        }

        $GLOBALS['TL_LANGUAGE'] = $objPage->language;

        return $objPage;
    }


    /**
     * Send response for an ajax request
     * @param   mixed
     */
    public static function ajaxResponse($varValue)
    {
        $varValue = static::replaceTags($varValue);

        if (is_array($varValue) || is_object($varValue))
        {
            $varValue = json_encode($varValue);
        }

        echo $varValue;
        exit;
    }

    /**
     * Get value of an auto_item parameter
     * @param   string Key
     * @return  string
     */
    public static function getAutoItem($strKey)
    {
        if ($GLOBALS['TL_CONFIG']['useAutoItem'] && in_array($strKey, $GLOBALS['TL_AUTO_ITEM'])) {

            return \Input::get('auto_item');
        }

        return \Input::get($strKey);
    }

    /**
     * Recursively replace inserttags in the return value
     * @param    array|string
     * @return    array|string
     */
    private static function replaceTags($varValue)
    {
        if (is_array($varValue)) {
            foreach ($varValue as $k => $v) {
                $varValue[$k] = static::replaceTags($v);
            }

            return $varValue;

        } elseif (is_object($varValue)) {
            return $varValue;
        }

        return Isotope::getInstance()->call('replaceInsertTags', array($varValue, false));
    }
}
