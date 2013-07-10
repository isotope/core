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

namespace Isotope\Model\ProductCollection;

use Isotope\Isotope;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\Address;
use Isotope\Model\Config;
use Isotope\Model\OrderStatus;
use Isotope\Model\Payment;
use Isotope\Model\ProductCollection;
use Isotope\Model\Shipping;


/**
 * Class Order
 *
 * Provide methods to handle Isotope orders.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
class Order extends ProductCollection implements IsotopeProductCollection
{

    /**
     * Lock products from apply rule prices
     * @var boolean
     */
    protected $blnLocked = true;


    /**
     * Set a value
     * @param string
     * @param mixed
     * @throws Exception
     */
    public function __set($strKey, $varValue)
    {
        switch ($strKey)
        {
            // Order ID cannot be changed, it is created through Isotope\Model\ProductCollection\Order::generateOrderId on checkout
            case 'order_id':
                throw new \InvalidArgumentException('order_id cannot be changed trough __set().');
                break;

            default:
                parent::__set($strKey, $varValue);
        }
    }

    /**
     * Return true if order has been paid.
     * This is the case if either payment date is set or order status has the paid flag
     * @return  bool
     */
    public function isPaid()
    {
        // Order is paid if a payment date is set
        $paid = (int) $this->date_paid;

        if ($paid > 0 && $paid <= time()) {
            return true;
        }

        // Otherwise we check the orderstatus checkbox
        $objStatus = $this->getRelated('order_status');

        return (null !== $objStatus && $objStatus->isPaid()) ? true : false;
    }

    /**
     * Get label for current order status
     * @return  string
     */
    public function getStatusLabel()
    {
        $objStatus = $this->getRelated('order_status');

        return (null === $objStatus) ? '' : $objStatus->getName();
    }

    /**
     * Get the alias for current order status
     * @return  string
     */
    public function getStatusAlias()
    {
        $objStatus = $this->getRelated('order_status');

        return (null === $objStatus) ? $this->order_status : $objStatus->getAlias();
    }


    /**
     * Remove downloads when removing a product
     * @param object
     * @return boolean
     */
    public function deleteProduct(IsotopeProduct $objProduct)
    {
        if (parent::deleteProduct($objProduct))
        {
            \Database::getInstance()->query("DELETE FROM tl_iso_product_collection_download WHERE pid={$objProduct->collection_id}");
        }

        return false;
    }


    /**
     * Delete downloads when deleting this order
     * @return integer
     */
    public function delete()
    {
        if (parent::delete()) {
            \Database::getInstance()->query("DELETE FROM tl_iso_product_collection_download WHERE pid IN (SELECT id FROM tl_iso_product_collection_item WHERE pid={$this->id})");

            return true;
        }

        return false;
    }


    /**
     * Return current surcharges as array
     * @return array
     */
    public function getSurcharges()
    {
        $arrSurcharges = deserialize($this->arrData['surcharges']);

        return is_array($arrSurcharges) ? $arrSurcharges : array();
    }


    /**
     * Process the order checkout
     * @return boolean
     */
    public function checkout()
    {
        if ($this->checkout_complete) {
            return true;
        }

        global $objPage;

        // Load page configuration
        if (!is_object($objPage) && $this->pageId > 0) {
            $objPage = \Controller::getPageDetails($this->pageId);
            $objPage = \Isotope\Frontend::loadPageConfig($objPage);
        }

        if (($objCart = Cart::findByPk($this->source_collection_id)) === null) {
            \System::log('Could not find Cart ID '.$this->source_collection_id.' for Order ID '.$this->id, __METHOD__, TL_ERROR);

            return false;
        }

        // Set the current system to the language when the user placed the order.
        // This will result in correct e-mails and payment description.
        if ($GLOBALS['TL_LANGUAGE'] != $this->language) {
            $GLOBALS['TL_LANGUAGE'] = $this->language;
            \System::loadLanguageFile('default', $this->language, true);
        }

        // Initialize system
        Isotope::overrideConfig($this->config_id);
        Isotope::setCart($objCart);

        $this->arrData['date']                 = time();
        $this->arrData['shipping_id']          = $objCart->shipping_id;
        $this->arrData['payment_id']           = $objCart->payment_id;
        $this->arrData['subTotal']             = $objCart->subTotal;
        $this->arrData['grandTotal']           = $objCart->grandTotal;
        $this->arrData['currency']             = Isotope::getConfig()->currency;

        // Mark Order as modified to empty cache
        $this->setModified(true);

        // !HOOK: pre-process checkout
        if (isset($GLOBALS['ISO_HOOKS']['preCheckout']) && is_array($GLOBALS['ISO_HOOKS']['preCheckout'])) {
            foreach ($GLOBALS['ISO_HOOKS']['preCheckout'] as $callback) {
                $objCallback = \System::importStatic($callback[0]);

                if ($objCallback->$callback[1]($this, $objCart) === false) {
                    \System::log('Callback ' . $callback[0] . '::' . $callback[1] . '() cancelled checkout for Order ID ' . $this->id, __METHOD__, TL_ERROR);

                    return false;
                }
            }
        }

        // Copy all items from cart to oder
        $arrItemIds = $this->copyItemsFrom($objCart);

        // Set billing and shipping address and create private records
        $this->setBillingAddress($objCart->getBillingAddress());
        $this->setShippingAddress($objCart->getShippingAddress());
        $this->createPrivateAddresses();

        // @todo must add surcharges and downloads here

        $objCart->delete();

        $this->checkout_complete = true;

        // Set order status only if a payment module has not already set it
        if ($this->order_status == 0) {
            $this->order_status = Isotope::getConfig()->orderstatus_new;
        }

        $this->generateOrderId();
        $arrData = $this->getEmailData();
        $strRecipient = $this->getEmailRecipient();

        \System::log('New order ID ' . $this->id . ' has been placed', __METHOD__, TL_ACCESS);

        if ($this->iso_mail_admin && $this->iso_sales_email != '') {
            try {
                $objEmail = new \Isotope\Email($this->iso_mail_admin, $this->language, $this);
                $objEmail->replyTo($strRecipient);
                $objEmail->send($this->iso_sales_email, $arrData);
            } catch (\Exception $e) {
                log_message($e->getMessage());
                \System::log('Error when sending admin confirmation for order ID '.$this->id, __METHOD__, TL_ERROR);
            }
        }

        if ($this->iso_mail_customer && $strRecipient != '') {
            try {
                $objEmail = new \Isotope\Email($this->iso_mail_customer, $this->language, $this);
                $objEmail->send($strRecipient, $arrData);
            } catch (\Exception $e) {
                log_message($e->getMessage());
                \System::log('Error when sending customer confirmation for order ID '.$this->id, __METHOD__, TL_ERROR);
            }
        } else {
            \System::log('Unable to send customer confirmation for order ID '.$this->id, __METHOD__, TL_ERROR);
        }

        // !HOOK: post-process checkout
        if (isset($GLOBALS['ISO_HOOKS']['postCheckout']) && is_array($GLOBALS['ISO_HOOKS']['postCheckout'])) {
            foreach ($GLOBALS['ISO_HOOKS']['postCheckout'] as $callback) {
                $objCallback = \System::importStatic($callback[0]);
                $objCallback->$callback[1]($this, $arrItemIds, $arrData);
            }
        }

        $this->save();

        return true;
    }


    /**
     * Complete order if the checkout has been made. This will cleanup session data
     * @return  bool
     */
    public function complete()
    {
        if ($this->checkout_complete) {
            $intConfig = $_SESSION['ISOTOPE']['config_id'];

            unset($_SESSION['CHECKOUT_DATA']);
            unset($_SESSION['ISOTOPE']);
            unset($_SESSION['FORM_DATA']);
            unset($_SESSION['FILES']);

            if ($intConfig > 0) {
                $_SESSION['ISOTOPE']['config_id'] = $intConfig;
            }

            return true;
        }

        return false;
    }


    /**
     * Update the status of this order and trigger actions (email & hook)
     * @param int
     * @param bool
     * @return bool
     */
    public function updateOrderStatus($intNewStatus, $blnActions=true)
    {
        // Status already set, nothing to do
        if ($this->order_status == $intNewStatus) {
            return true;
        }

        $objNewStatus = OrderStatus::findByPk($intNewStatus);

        if (null === $objNewStatus) {
            return false;
        }

        // !HOOK: allow to cancel a status update
        if (isset($GLOBALS['ISO_HOOKS']['preOrderStatusUpdate']) && is_array($GLOBALS['ISO_HOOKS']['preOrderStatusUpdate']))
        {
            foreach ($GLOBALS['ISO_HOOKS']['preOrderStatusUpdate'] as $callback)
            {
                $objCallback = \System::importStatic($callback[0]);
                $blnCancel = $objCallback->$callback[1]($this, $objNewStatus, $blnActions);

                if ($blnCancel === true)
                {
                    return false;
                }
            }
        }

        // Add the payment date if there is none
        if ($objNewStatus->isPaid()) {
            if ($this->date_paid == '') {
                $this->date_paid = time();
            }
        }

        // Trigger email actions
        if ($objNewStatus->mail_customer > 0 || $objNewStatus->mail_admin > 0) {

            $arrData = $this->getEmailData();
            $arrData['new_status'] = $objNewStatus->getName();
            $strRecipient = $this->getEmailRecipient();

            if ($objNewStatus->mail_customer && $strRecipient != '') {

                try {
                    $objEmail = new \Isotope\Email($objNewStatus->mail_customer, $this->language, $this);
                    $objEmail->send($strRecipient, $arrData);

                    if (TL_MODE == 'BE') {
                        $this->addConfirmationMessage($GLOBALS['TL_LANG']['tl_iso_product_collection']['orderStatusEmail']);
                    }

                } catch (\Exception $e) {
                    log_message($e->getMessage());
                    \System::log('Error sending status update to customer for order ID '.$this->id, __METHOD__, TL_ERROR);
                }
            }

            $strSalesEmail = $objNewStatus->sales_email ? $objNewStatus->sales_email : $this->iso_sales_email;

            if ($objNewStatus->mail_admin && $strSalesEmail != '') {

                try {
                    $objEmail = new \Isotope\Email($objNewStatus->mail_admin, $this->language, $this);
                    $objEmail->replyTo($strRecipient);
                    $objEmail->send($strSalesEmail, $arrData);
                } catch (\Exception $e) {
                    log_message($e->getMessage());
                    \System::log('Error sending status update to admin for order ID '.$this->id, __METHOD__, TL_ERROR);
                }
            }
        }

        // Store old status and set the new one
        $intOldStatus = $this->order_status;
        $this->order_status = $objNewStatus->id;
        $this->save();

        // !HOOK: order status has been updated
        if (isset($GLOBALS['ISO_HOOKS']['postOrderStatusUpdate']) && is_array($GLOBALS['ISO_HOOKS']['postOrderStatusUpdate']))
        {
            foreach ($GLOBALS['ISO_HOOKS']['postOrderStatusUpdate'] as $callback)
            {
                $objCallback = \System::importStatic($callback[0]);
                $objCallback->$callback[1]($this, $intOldStatus, $objNewStatus, $blnActions);
            }
        }
    }


    /**
     * Retrieve the array of email data for parsing simple tokens
     * @return array
     */
    public function getEmailData()
    {
        $arrData = $this->email_data;
        $arrData['id'] = $this->id;
        $arrData['order_id'] = $this->order_id;
        $arrData['uniqid'] = $this->uniqid;
        $arrData['status'] = $this->getStatusLabel();
        $arrData['status_id'] = $this->arrData['status'];

        foreach ($this->getBillingAddress()->row() as $k => $v)
        {
            $arrData['billing_' . $k] = Isotope::formatValue('tl_iso_addresses', $k, $v);
        }

        foreach ($this->getShippingAddress()->row() as $k => $v)
        {
            $arrData['shipping_' . $k] = Isotope::formatValue('tl_iso_addresses', $k, $v);
        }

        if (($objConfig = Config::findByPk($this->config_id)) !== null)
        {
            foreach ($objConfig->row() as $k => $v)
            {
                $arrData['config_' . $k] = Isotope::formatValue('tl_iso_config', $k, $v);
            }
        }

        if ($this->pid > 0)
        {
            $objUser = \Database::getInstance()->execute("SELECT * FROM tl_member WHERE id=" . (int) $this->pid);

            foreach ($objUser->row() as $k => $v)
            {
                $arrData['member_' . $k] = Isotope::formatValue('tl_member', $k, $v);
            }
        }

        $arrData['items']       = $this->sumItemsQuantity();
        $arrData['products']    = $this->countItems();
        $arrData['subTotal']    = Isotope::formatPriceWithCurrency($this->getSubtotal(), false);
        $arrData['grandTotal']  = Isotope::formatPriceWithCurrency($this->getTotal(), false);
        $arrData['cart_text']   = strip_tags($this->replaceInsertTags($this->getProducts('iso_products_text')));
        $arrData['cart_html']   = $this->replaceInsertTags($this->getProducts('iso_products_html'));

        // !HOOK: add custom email tokens
        if (isset($GLOBALS['ISO_HOOKS']['getOrderEmailData']) && is_array($GLOBALS['ISO_HOOKS']['getOrderEmailData']))
        {
            foreach ($GLOBALS['ISO_HOOKS']['getOrderEmailData'] as $callback)
            {
                $objCallback = \System::importStatic($callback[0]);
                $arrData = $objCallback->$callback[1]($this, $arrData);
            }
        }

        return $arrData;
    }


    /**
     * Make sure the addresses belong to this collection only, so they will never be modified
     */
    protected function createPrivateAddresses()
    {
        if (!$this->id) {
            throw new \UnderflowException('Product collection must be saved before creating unique addresses.');
        }

        $objBillingAddress = $this->getBillingAddress();
        $objShippingAddress = $this->getShippingAddress();

        if (null !== $objBillingAddress && $objBillingAddress->ptable != static::$strTable && $objBillingAddress->pid != $this->id) {

            $objNew = clone $objBillingAddress;
            $objNew->ptable = static::$strTable;
            $objNew->pid = $this->id;
            $objNew->save(true);

            $this->setBillingAddress($objNew);

            if (null !== $objShippingAddress && $objBillingAddress->id == $objShippingAddress->id) {
                $this->setShippingAddress($objNew);

                // Return here, we do not need to check shipping address
                return;
            }
        }

        if (null !== $objShippingAddress && $objShippingAddress->ptable != static::$strTable && $objShippingAddress->pid != $this->id) {

            $objNew = clone $objShippingAddress;
            $objNew->ptable = static::$strTable;
            $objNew->pid = $this->id;
            $objNew->save(true);

            $this->setShippingAddress($objNew);
        }
    }


    /**
     * Generate the next higher Order-ID based on config prefix, order number digits and existing records
     * @return string
     */
    protected function generateOrderId()
    {
        if ($this->arrData['order_id'] != '')
        {
            return $this->arrData['order_id'];
        }

        // !HOOK: generate a custom order ID
        if (isset($GLOBALS['ISO_HOOKS']['generateOrderId']) && is_array($GLOBALS['ISO_HOOKS']['generateOrderId']))
        {
            foreach ($GLOBALS['ISO_HOOKS']['generateOrderId'] as $callback)
            {
                $this->import($callback[0]);
                $strOrderId = $this->$callback[0]->$callback[1]($this);

                if ($strOrderId !== false)
                {
                    $this->arrData['order_id'] = $strOrderId;
                    break;
                }
            }
        }

        if ($this->arrData['order_id'] == '')
        {
            $objDatabase = \Database::getInstance();
            $strPrefix = Isotope::getInstance()->call('replaceInsertTags', Isotope::getConfig()->orderPrefix);
            $intPrefix = utf8_strlen($strPrefix);
            $arrConfigIds = $objDatabase->prepare("SELECT id FROM tl_iso_config WHERE store_id=?")->execute(Isotope::getConfig()->store_id)->fetchEach('id');

            // Lock tables so no other order can get the same ID
            $objDatabase->lockTables(array(static::$strTable => 'WRITE'));

            // Retrieve the highest available order ID
            $objMax = $objDatabase->prepare("SELECT order_id FROM " . static::$strTable . " WHERE " . ($strPrefix != '' ? "order_id LIKE '$strPrefix%' AND " : '') . "config_id IN (" . implode(',', $arrConfigIds) . ") ORDER BY CAST(" . ($strPrefix != '' ? "SUBSTRING(order_id, " . ($intPrefix+1) . ")" : 'order_id') . " AS UNSIGNED) DESC")->limit(1)->executeUncached();
            $intMax = (int) substr($objMax->order_id, $intPrefix);

            $this->arrData['order_id'] = $strPrefix . str_pad($intMax+1, Isotope::getConfig()->orderDigits, '0', STR_PAD_LEFT);
        }

        $objDatabase->prepare("UPDATE " . static::$strTable . " SET order_id=? WHERE id={$this->id}")->executeUncached($this->arrData['order_id']);
        $objDatabase->unlockTables();

        return $this->arrData['order_id'];
    }
}
