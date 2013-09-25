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
use Isotope\Model\Config;
use Isotope\Model\OrderStatus;
use Isotope\Model\Payment;
use Isotope\Model\ProductCollection;
use Isotope\Model\ProductCollectionItem;
use Isotope\Model\ProductCollectionDownload;
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
     * Set a value
     * @param string
     * @param mixed
     * @throws Exception
     */
    public function __set($strKey, $varValue)
    {
        switch ($strKey)
        {
            // Document Number cannot be changed, it is created through Isotope\Model\ProductCollection\Order::generateDocumentNumber on checkout
            case 'document_number':
                throw new \InvalidArgumentException('document_number cannot be changed trough __set().');
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
     * Remove downloads when deleting an item
     * @param   object
     * @return  boolean
     */
    public function deleteItem(ProductCollectionItem $objItem)
    {
        $intPid = $objItem->id;

        if (parent::deleteItem($objItem) && $intPid > 0) {
            \Database::getInstance()->query("DELETE FROM tl_iso_product_collection_download WHERE pid=$intPid");
        }

        return false;
    }


    /**
     * Delete downloads when deleting this order
     * @return integer
     */
    public function delete()
    {
        $intPid = $this->id;

        if (parent::delete() && $intPid > 0) {
            \Database::getInstance()->query("DELETE FROM tl_iso_product_collection_download WHERE pid IN (SELECT id FROM tl_iso_product_collection_item WHERE pid=$intPid)");

            return true;
        }

        return false;
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
        Isotope::setConfig($this->getRelated('config_id'));
        Isotope::setCart($objCart);

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
        $this->copySurchargesFrom($objCart, $arrItemIds);

        // Set billing and shipping address and create private records
        $this->setBillingAddress($objCart->getBillingAddress());
        $this->setShippingAddress($objCart->getShippingAddress());
        $this->createPrivateAddresses();

        // Store address in address book
        if ($this->iso_addToAddressbook && $this->member > 0) {
            $time = time();

            if ($objCart->getBillingAddress()->ptable != \MemberModel::getTable()) {
                $objAddress = clone $objCart->getBillingAddress();
                $objAddress->pid = $this->member;
                $objAddress->tstamp = time();
                $objAddress->ptable = \MemberModel::getTable();
                $objAddress->save();
            }

            if ($objCart->getBillingAddress()->id != $objCart->getShippingAddress()->id && $objCart->getShippingAddress()->ptable != \MemberModel::getTable()) {
                $objAddress = clone $objCart->getShippingAddress();
                $objAddress->pid = $this->member;
                $objAddress->tstamp = time();
                $objAddress->ptable = \MemberModel::getTable();
                $objAddress->save();
            }
        }

        // Add downloads from products to the collection
        $arrDownloads = ProductCollectionDownload::createForProductsInCollection($this);
        foreach ($arrDownloads as $objDownload) {
            $objDownload->save();
        }

        // Delete cart after migrating to order
        $objCart->delete();

        $this->checkout_complete = true;

        // Set order status only if a payment module has not already set it
        if ($this->order_status == 0) {
            $this->order_status = Isotope::getConfig()->orderstatus_new;
        }

        $this->generateDocumentNumber(Isotope::getConfig()->orderPrefix, (int) Isotope::getConfig()->orderDigits);
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

        // Lock will trigger save() of the model
        $this->lock();

        return true;
    }


    /**
     * Complete order if the checkout has been made. This will cleanup session data
     * @return  bool
     */
    public function complete()
    {
        if ($this->checkout_complete) {
            unset($_SESSION['FORM_DATA']);
            unset($_SESSION['FILES']);

            // Retain custom config ID
            if (($objCart = Isotope::getCart()) !== null && $objCart->config_id != $this->config_id) {
                $objCart->config_id = $this->config_id;
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
        if ($objNewStatus->isPaid() && $this->date_paid == '') {
            $this->date_paid = time();
        }

        // Trigger email actions
        $blnEmail = null;
        if ($objNewStatus->mail_customer > 0 || $objNewStatus->mail_admin > 0) {

            $arrData = $this->getEmailData();
            $arrData['new_status'] = $objNewStatus->getName();
            $strRecipient = $this->getEmailRecipient();

            if ($objNewStatus->mail_customer && $strRecipient != '') {

                try {
                    $objEmail = new \Isotope\Email($objNewStatus->mail_customer, $this->language, $this);
                    $blnEmail = $objEmail->send($strRecipient, $arrData);
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

        if (TL_MODE == 'BE') {
            \Message::addConfirmation($GLOBALS['TL_LANG']['tl_iso_product_collection']['orderStatusUpdate']);

            if ($blnEmail === true) {
                \Message::addConfirmation($GLOBALS['TL_LANG']['tl_iso_product_collection']['orderStatusEmailSuccess']);
            } elseif ($blnEmail === false) {
                \Message::addConfirmation($GLOBALS['TL_LANG']['tl_iso_product_collection']['orderStatusEmailError']);
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
        $arrData['document_number'] = $this->document_number;
        $arrData['uniqid'] = $this->uniqid;
        $arrData['status'] = $this->getStatusLabel();
        $arrData['status_id'] = $this->order_status;

        if ($this->getRelated('config_id') !== null) {
            foreach ($this->getRelated('config_id')->row() as $k => $v) {
                $arrData['config_' . $k] = Isotope::formatValue($this->getRelated('config_id')->getTable(), $k, $v);
            }
        }

        if ($this->pid > 0 && $this->getRelated('pid') !== null) {
            foreach ($this->getRelated('pid')->row() as $k => $v) {
                $arrData['member_' . $k] = Isotope::formatValue($this->getRelated('pid')->getTable(), $k, $v);
            }
        }

        // !HOOK: add custom email tokens
        if (isset($GLOBALS['ISO_HOOKS']['getOrderEmailData']) && is_array($GLOBALS['ISO_HOOKS']['getOrderEmailData'])) {
            foreach ($GLOBALS['ISO_HOOKS']['getOrderEmailData'] as $callback) {
                $objCallback = \System::importStatic($callback[0]);
                $arrData = $objCallback->$callback[1]($this, $arrData);
            }
        }

        return $arrData;
    }

    /**
     * Include downloads when adding items to template
     * @param   Isotope\Template
     * @param   Callable
     * @return  array
     */
    protected function addItemsToTemplate(\Isotope\Template $objTemplate, $varCallable=null)
    {
        $arrItems = array();
        $arrAllDownloads = array();

        foreach ($this->getItems($varCallable) as $objItem) {

            $arrDownloads = array();
            $arrItems[] = $this->generateItem($objItem);

            foreach ($objItem->getDownloads() as $objDownload) {
                $arrDownloads = array_merge($arrDownloads, $objDownload->getForTemplate($this->isPaid()));
            }

            $arrAllDownloads = array_merge($arrAllDownloads, $arrDownloads);
        }

        $objTemplate->items = \Isotope\Frontend::generateRowClass($arrItems, 'row', 'rowClass', 0, ISO_CLASS_COUNT|ISO_CLASS_FIRSTLAST|ISO_CLASS_EVENODD);
        $objTemplate->downloads = $arrAllDownloads;

        return $arrItems;
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

        if (null !== $objBillingAddress && ($objBillingAddress->ptable != static::$strTable || $objBillingAddress->pid != $this->id)) {

            $objNew = clone $objBillingAddress;
            $objNew->pid = $this->id;
            $objNew->tstamp = time();
            $objNew->ptable = static::$strTable;
            $objNew->save();

            $this->setBillingAddress($objNew);

            if (null !== $objShippingAddress && $objBillingAddress->id == $objShippingAddress->id) {
                $this->setShippingAddress($objNew);

                // Return here, we do not need to check shipping address
                return;
            }
        }

        if (null !== $objShippingAddress && ($objShippingAddress->ptable != static::$strTable || $objShippingAddress->pid != $this->id)) {

            $objNew = clone $objShippingAddress;
            $objNew->pid = $this->id;
            $objNew->tstamp = time();
            $objNew->ptable = static::$strTable;
            $objNew->save();

            $this->setShippingAddress($objNew);
        }
    }
}
