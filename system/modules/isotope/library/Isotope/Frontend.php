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
     * Import the Isotope object
     */
    public function __construct()
    {
        parent::__construct();
    }


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
     * Add the navigation CSS class to pages belonging to the active product
     * @param object
     * @link http://www.contao.org/hooks.html#parseTemplate
     */
    public function addNavigationClass(&$objTemplate)
    {
        // Unset hook to prevent further execution on non-reader pages
        if (Frontend::getAutoItem('product') == '')
        {
            unset($GLOBALS['TL_HOOKS']['parseTemplate'][array_search(array('Isotope\Frontend', 'fixNavigationTrail'), $GLOBALS['TL_HOOKS']['parseTemplate'])]);

            return;
        }

        if (substr($objTemplate->getName(), 0, 4) == 'nav_')
        {
            static $arrTrail = null;

            // Only fetch the product once
            if ($arrTrail == null)
            {
                $arrTrail = array();
                $objProduct = static::getProductByAlias(static::getAutoItem('product'));

                // getProductByAlias will return null if the product is not found
                if ($objProduct !== null)
                {
                    $arrCategories = $objProduct->getCategories();

                    if (is_array($arrCategories) && !empty($arrCategories))
                    {
                        foreach ($arrCategories as $pageId)
                        {
                            $objPage = $this->getPageDetails($pageId);

                            if (is_array($objPage->trail))
                            {
                                $arrTrail = array_merge($arrTrail, $objPage->trail);
                            }
                        }

                        $arrTrail = array_unique($arrTrail);
                    }
                }
            }

            if (!empty($arrTrail))
            {
                $arrItems = $objTemplate->items;

                foreach ($arrItems as $k => $arrItem)
                {
                    if (in_array($arrItem['id'], $arrTrail))
                    {
                        $arrItems[$k]['class'] .= ' product';
                    }
                }

                $objTemplate->items = $arrItems;
            }
        }
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
     * Prepare form fields from a form generator form ID
     * Useful if you want to give the user the possibility to use a custom form for a certain action (e.g. order conditions)
     * @param integer Database ID
     * @param string Form ID (FORM SUBMIT)
     * @param array    Form config that gets merged with the form data from the database
     * @return object|null
     */
    public function prepareForm($intId, $strFormId, $arrConfig=array())
    {
        $this->loadDataContainer('tl_form');
        $this->loadDataContainer('tl_form_field');

        $objForm = new stdClass();
        $objForm->arrHidden     = array();
        $objForm->arrFields        = array();
        $objForm->arrFormData   = array();
        $objForm->arrFiles      = array();
        $objForm->blnSubmitted  = false;
        $objForm->blnHasErrors  = false;
        $objForm->blnHasUploads    = false;

        $objForm->arrData = array_merge(\Database::getInstance()->execute("SELECT * FROM tl_form WHERE id=".(int) $intId)->fetchAssoc(), $arrConfig);

        // Form not found
        if (!$objForm->arrData['id'])
        {
            return null;
        }

        // Get all form fields
        $objFields = \Database::getInstance()->execute("SELECT * FROM tl_form_field WHERE pid={$objForm->arrData['id']} AND invisible='' ORDER BY sorting");

        $row = 0;
        $max_row = $objFields->numRows;

        while ($objFields->next())
        {
            $strClass = $GLOBALS['TL_FFL'][$objFields->type];

            // Continue if the class is not defined
            if (!class_exists($strClass))
            {
                continue;
            }

            $arrData = $objFields->row();

            // make sure "name" is set because not all form fields do need it and it would thus overwrite the array indexes
            $arrData['name'] = ($arrData['name']) ? $arrData['name'] : 'field_' . $arrData['id'];

            $arrData['decodeEntities'] = true;
            $arrData['allowHtml'] = $objForm->arrData['allowTags'];
            $arrData['rowClass'] = 'row_'.$row . (($row == 0) ? ' row_first' : (($row == ($max_row - 1)) ? ' row_last' : '')) . ((($row % 2) == 0) ? ' even' : ' odd');
            $arrData['tableless'] = $objForm->arrData['tableless'];

            // Increase the row count if its a password field
            if ($objFields->type == 'password')
            {
                ++$row;
                ++$max_row;

                $arrData['rowClassConfirm'] = 'row_'.$row . (($row == ($max_row - 1)) ? ' row_last' : '') . ((($row % 2) == 0) ? ' even' : ' odd');
            }

            $objWidget = new $strClass($arrData);
            $objWidget->required = $objFields->mandatory ? true : false;

            // HOOK: load form field callback
            if (isset($GLOBALS['TL_HOOKS']['loadFormField']) && is_array($GLOBALS['TL_HOOKS']['loadFormField']))
            {
                foreach ($GLOBALS['TL_HOOKS']['loadFormField'] as $callback)
                {
                    $objCallback = \System::importStatic($callback[0]);
                    $objWidget = $objCallback->$callback[1]($objWidget, $strFormId, $objForm->arrData);
                }
            }

            // Validate input
            if (\Input::post('FORM_SUBMIT') == $strFormId)
            {
                $objForm->blnSubmitted = true;
                $objWidget->validate();

                // HOOK: validate form field callback
                if (isset($GLOBALS['TL_HOOKS']['validateFormField']) && is_array($GLOBALS['TL_HOOKS']['validateFormField']))
                {
                    foreach ($GLOBALS['TL_HOOKS']['validateFormField'] as $callback)
                    {
                        $objCallback = \System::importStatic($callback[0]);
                        $objWidget = $objCallback->$callback[1]($objWidget, $strFormId, $objForm->arrData);
                    }
                }

                if ($objWidget->hasErrors())
                {
                    $objForm->blnHasErrors = true;
                }

                // Store current value in the session
                elseif ($objWidget->submitInput())
                {
                    $objForm->arrFormData[$objFields->name] = $objWidget->value;
                    $_SESSION['FORM_DATA'][$objFields->name] = $objWidget->value;
                }

                // Store file uploads
                elseif ($objWidget instanceof \uploadable)
                {
                    $objForm->arrFiles[$objFields->name]    = $_SESSION['FILES'][$objFields->name];
                }

                unset($_POST[$objFields->name]);
            }

            if ($objWidget instanceof \uploadable)
            {
                $objForm->blnHasUploads = true;
            }

            if ($objWidget instanceof \FormHidden)
            {
                --$max_row;
                $objForm->arrHidden[$arrData['name']]    = $objWidget;
                continue;
            }

            $objForm->arrFields[$arrData['name']]        = $objWidget;

            ++$row;
        }

        $strAttributes = '';
        $arrAttributes = deserialize($objForm->arrData['attributes'], true);

        // Form attributes
        if (strlen($arrAttributes[1]))
        {
            $strAttributes .= ' ' . $arrAttributes[1];
        }

        $objForm->attributes = $strAttributes;
        $objForm->enctype = $objForm->blnHasUpload ? 'multipart/form-data' : 'application/x-www-form-urlencoded';

        return $objForm;
    }


    /**
     * Shortcut for a single product by ID or from database result
     * @param IsotopeProduct|int
     * @param boolean
     * @return IsotopeProduct|null
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
     * @param array absolute page urls
     * @param int root page id
     * @return array extended array of absolute page urls
     */
    public function addProductsToSearchIndex($arrPages, $intRoot=0, $blnSitemap=false, $strLanguage=null)
    {
        $time = time();
        $arrJump = array();
        $arrRoot = array();
        $strAllowedPages = '';

        // if we have a root page id (sitemap.xml e.g.) we have to make sure we only consider categories in this tree
        if ($intRoot > 0)
        {
            $arrPageIds = \Database::getInstance()->getChildRecords($intRoot, 'tl_page', false);
            $arrPageIds[] = $intRoot;

            $strAllowedPages = ' AND c.page_id IN (' . implode(',', $arrPageIds) . ')';
        }

        $objProducts = \Database::getInstance()->query("
            SELECT tl_page.*, p.id AS product_id, p.alias AS product_alias FROM tl_iso_product_categories c
                JOIN tl_iso_products p ON p.id=c.pid
                JOIN tl_iso_producttypes t ON t.id=p.type
                JOIN tl_page ON tl_page.id=c.page_id
            WHERE
                t.class='regular'
                AND p.language=''
                AND p.pid=0
                AND p.published=1
                AND (p.start='' OR p.start<$time)
                AND (p.stop='' OR p.stop>$time)"
                . $strAllowedPages
        );

        while ($objProducts->next())
        {
            // Cache redirect page with a placeholder, so we only need to replace the string
            if (!isset($arrJump[$objProducts->id]))
            {
                // we need the root page language if we dont have it (maintenance module)
                $intJump = static::getReaderPageId($objProducts);
                $objJump = null === $strLanguage ? $this->getPageDetails($intJump) : \Database::getInstance()->execute("SELECT *, '$intRoot' AS rootId FROM tl_page WHERE published=1 AND (start='' OR start<$time) AND (stop='' OR stop>$time) AND id=" . (int) $intJump);

                if ($objJump->numRows)
                {
                    if (!isset($arrRoot[$objJump->rootId]))
                    {
                        $arrRoot[$objJump->rootId] = \Database::getInstance()->execute("SELECT * FROM tl_page WHERE id=" . (int) $objJump->rootId);
                    }

                    $strDomain = Environment::get('base');

                    // Overwrite the domain
                    if ($arrRoot[$objJump->rootId]->dns != '')
                    {
                        $strDomain = ($arrRoot[$objJump->rootId]->useSSL ? 'https://' : 'http://') . $arrRoot[$objJump->rootId]->dns . TL_PATH . '/';
                    }

                    // @todo use Product::generateUrl() here or don't we do this because of performance?

                    $arrJump[$objProducts->page_id] = $strDomain . Controller::generateFrontendUrl($objJump->row(), ($GLOBALS['TL_CONFIG']['useAutoItem'] ? '/' : '/product/') . '##alias##', ($strLanguage=='' ? $arrRoot[$objJump->rootId]->language : $strLanguage));
                }
                else
                {
                    $arrJump[$objProducts->page_id] = false;
                }
            }

            if (false !== $arrJump[$objProducts->page_id])
            {
                $strAlias = $objProducts->product_alias == '' ? $objProducts->product_id : $objProducts->product_alias;
                $arrPages[] = str_replace('##alias##', $strAlias, $arrJump[$objProducts->page_id]);
            }
        }

        // the reader page id can be the same for several categories so we have to make sure we only index the product once
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
     * Gets the product reader of a certain page
     * @param \Database\Result|int    page object or page ID
     * @param int    override setting from a module or content element
     * @return int reader page id
     */
    public static function getReaderPageId($objOriginPage=null, $intOverride=0)
    {
        if ($intOverride > 0)
        {
            return (int) $intOverride;
        }

        if ($objOriginPage === null)
        {
            global $objPage;
            $objOriginPage = $objPage;
        }

        $intPage = is_object($objOriginPage) ? (int) $objOriginPage->id : (int) $objOriginPage;

        // return from cache
        if (isset(static::$arrReaderPageIds[$intPage]))
        {
            return static::$arrReaderPageIds[$intPage];
        }

        if (!is_object($objOriginPage))
        {
            $objOriginPage = \Database::getInstance()->execute("SELECT * FROM tl_page WHERE id=" . $intPage);
        }

        // if the reader page is set on the current page id we return this one
        if ($objOriginPage->iso_setReaderJumpTo > 0)
        {
            static::$arrReaderPageIds[$intPage] = $objOriginPage->iso_readerJumpTo;

            return (int) $objOriginPage->iso_readerJumpTo;
        }

        // now move up the page tree until we find a page where the reader is set
        $trail = array();
        $pid = (int) $objOriginPage->pid;

        do
        {
            $objParentPage = \Database::getInstance()->execute("SELECT * FROM tl_page WHERE id=" . $pid);

            if ($objParentPage->numRows < 1)
            {
                break;
            }

            $trail[] = $objParentPage->id;

            if ($objParentPage->iso_setReaderJumpTo > 0)
            {
                // cache the reader page for all trail pages
                static::$arrReaderPageIds = array_merge(static::$arrReaderPageIds, array_fill_keys($trail, $objParentPage->iso_readerJumpTo));

                return (int) $objParentPage->iso_readerJumpTo;
            }

            $pid = (int) $objParentPage->pid;
        }
        while ($pid > 0 && $objParentPage->type != 'root');

        // if there is no reader page set at all, we take the current page object
        global $objPage;
        static::$arrReaderPageIds[$intPage] = (int) $objPage->id;

        return (int) $objPage->id;
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
        }
        elseif (is_numeric($varUrl)) {
            if (($objJump = \PageModel::findByPk($varUrl)) === null) {
                throw new \InvalidArgumentException('Given page id does not exist.');
            }
            $varUrl = Isotope::getInstance()->generateFrontendUrl($objJump->row());
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

            if ($k === 'language' || preg_match('/(^|&(amp;)?)' . preg_quote($explode[0], '/') . '=/i', $strRequest)) {
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
     * Manipulate the breadcrumb to show the page reader
     * @param  array
     * @param  object
     * @return array
     */
    public function generateBreadcrumb($arrItems, $objModule)
    {
        if (static::getAutoItem('product') != '')
        {
            $objProduct = static::getProductByAlias(static::getAutoItem('product'));

            if ($objProduct !== null)
            {
                global $objPage;

                $intPage = null;
                $objParent = null;
                $arrTrail = $objPage->trail;
                $arrCategories = $objProduct->getCategories();

                foreach (array_reverse($arrTrail) as $intTrail)
                {
                    // Trail page is a category for this product
                    if (in_array($intTrail, $arrCategories))
                    {
                        $intPage = $intTrail;
                        $intParent = $intTrail;
                        break;
                    }

                    // Check if a child record of our trail is in categories
                    $arrChildren = \Database::getInstance()->getChildRecords($intTrail, 'tl_page', true);
                    $arrMatch = array_intersect($arrChildren, $arrCategories);

                    if (!empty($arrMatch))
                    {
                        $intPage = array_shift($arrMatch);
                        $intParent = $intTrail;
                        break;
                    }
                }

                // If we still haven't found a list page, don't alter the breadcrumb
                if ($intPage === null)
                {
                    return $arrItems;
                }

                $time = time();
                $arrResult = array();

                while ($intPage != $intParent)
                {
                    $objResult = \Database::getInstance()->prepare("SELECT * FROM tl_page WHERE id=?" . (!BE_USER_LOGGED_IN ? " AND (start='' OR start<$time) AND (stop='' OR stop>$time) AND published=1" : ""))->execute($intPage);

                    if (!$objResult->numRows)
                    {
                        break;
                    }

                    $intPage = $objResult->pid;

                    if ($objResult->hide && !$objModule->showHidden)
                    {
                        continue;
                    }

                    // Get href
                    switch ($objResult->type)
                    {
                        case 'redirect':
                            $href = $objResult->url;

                            if (strncasecmp($href, 'mailto:', 7) === 0)
                            {
                                $href = \String::encodeEmail($href);
                            }
                            break;

                        case 'forward':
                            $objNext = \Database::getInstance()->prepare("SELECT id, alias FROM tl_page WHERE id=?")
                                                      ->limit(1)
                                                      ->execute($objResult->jumpTo);

                            if ($objNext->numRows)
                            {
                                $href = \Controller::generateFrontendUrl($objNext->fetchAssoc());
                                break;
                            }
                            // DO NOT ADD A break; STATEMENT

                        default:
                            $href = \Controller::generateFrontendUrl($objResult->row());
                            break;
                    }

                    $arrResult[] = array
                    (
                        'isRoot'    => false,
                        'isActive'  => false,
                        'href'      => $href,
                        'title'     => ($objResult->pageTitle != '' ? specialchars($objResult->pageTitle, true) : specialchars($objResult->title, true)),
                        'link'      => $objResult->title,
                        'data'      => $objResult->row()
                    );
                }


                $arrItems = array_reverse($arrItems);

                // Remove wrong items from breadcrumb, but do not re-generate the correct ones
                foreach ($arrItems as $i => $arrItem)
                {
                    if ($arrItem['data']['id'] == $intParent)
                    {
                        // Reconvert the last item into a link
                        if ($arrItem['isActive'])
                        {
                            $arrItems[$i]['isActive'] = false;
                            $arrItems[$i]['href'] = \Controller::generateFrontendUrl($arrItems[$i]['data']);
                        }

                        break;
                    }

                    unset($arrItems[$i]);
                }

                $arrItems = array_reverse(array_merge($arrResult, $arrItems));

                // Add the reader as breadcrumb item
                $arrItems[] = array
                (
                    'isRoot'    => false,
                    'isActive'  => true,
                    'href'      => $objProduct->generateUrl($objPage->id),
                    'title'     => specialchars($objProduct->name, true),
                    'link'      => $objProduct->name,
                    'data'      => $objPage->row(),
                );
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
            list($GLOBALS['TL_ADMIN_NAME'], $GLOBALS['TL_ADMIN_EMAIL']) = System::splitFriendlyName($objPage->adminEmail);
        }
        else
        {
            list($GLOBALS['TL_ADMIN_NAME'], $GLOBALS['TL_ADMIN_EMAIL']) = System::splitFriendlyName($GLOBALS['TL_CONFIG']['adminEmail']);
        }

        // Define the static URL constants
        define('TL_FILES_URL', ($objPage->staticFiles != '' && !$GLOBALS['TL_CONFIG']['debugMode']) ? $objPage->staticFiles . TL_PATH . '/' : '');
        define('TL_SCRIPT_URL', ($objPage->staticSystem != '' && !$GLOBALS['TL_CONFIG']['debugMode']) ? $objPage->staticSystem . TL_PATH . '/' : '');
        define('TL_PLUGINS_URL', ($objPage->staticPlugins != '' && !$GLOBALS['TL_CONFIG']['debugMode']) ? $objPage->staticPlugins . TL_PATH . '/' : '');

        $objLayout = Database::getInstance()->prepare("SELECT l.*, t.templates FROM tl_layout l LEFT JOIN tl_theme t ON l.pid=t.id WHERE l.id=? OR l.fallback=1 ORDER BY l.id=? DESC")
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

        return \Input::get(Isotope::getConfig()->getUrlParam($strKey));
    }

    /**
     * Recursively replace inserttags in the return value
     * @param    array|string
     * @return    array|string
     */
    private static function replaceTags($varValue)
    {
        if (is_array($varValue))
        {
            foreach( $varValue as $k => $v )
            {
                $varValue[$k] = static::replaceTags($v);
            }

            return $varValue;
        }
        elseif (is_object($varValue))
        {
            return $varValue;
        }

        return Isotope::getInstance()->call('replaceInsertTags', array($varValue, false));
    }
}
