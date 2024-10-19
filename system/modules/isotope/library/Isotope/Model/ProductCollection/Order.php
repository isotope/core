<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\ProductCollection;

use Contao\Controller;
use Contao\Message;
use Contao\StringUtil;
use Contao\System;
use Contao\Template;
use Haste\Generator\RowClass;
use Haste\Util\Format;
use Isotope\CompatibilityHelper;
use Isotope\Interfaces\IsotopeNotificationTokens;
use Isotope\Interfaces\IsotopeOrderStatusAware;
use Isotope\Interfaces\IsotopePurchasableCollection;
use Isotope\Isotope;
use Isotope\Model\Address;
use Isotope\Model\Document;
use Isotope\Model\OrderStatus;
use Isotope\Model\ProductCollection;
use Isotope\Model\ProductCollectionLog;
use NotificationCenter\Model\Notification;


/**
 * Class Order
 *
 * Provide methods to handle Isotope orders.
 *
 * @method static Order findOneBy(string $strColumn, $varValue, array $arrOptions=array())
 *
 * @property array  $checkout_info
 * @property array  $payment_data
 * @property array  $shipping_data
 * @property string $document_number
 * @property int    $order_status
 * @property int    $date_paid
 * @property int    $date_shipped
 * @property string $notes
 *
 * @method static Order|null findByPk($id)
 */
class Order extends ProductCollection implements IsotopePurchasableCollection
{
    public const STATUS_UPDATE_SKIP_NOTIFICATION = 1;
    public const STATUS_UPDATE_SKIP_LOG = 2;


    /**
     * @inheritdoc
     */
    public function isPaid()
    {
        // Order is paid if a payment date is set
        if (null !== $this->date_paid && $this->date_paid <= time()) {
            return true;
        }

        // Otherwise we check the orderstatus checkbox
        try {
            /** @var OrderStatus $objStatus */
            $objStatus = $this->getRelated('order_status');

            return (null !== $objStatus && $objStatus->isPaid());
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function getDatePaid()
    {
        return $this->date_paid;
    }

    /**
     * @inheritdoc
     */
    public function setDatePaid($timestamp = null)
    {
        $this->date_paid = $timestamp;
    }

    /**
     * @inheritdoc
     */
    public function isShipped()
    {
        return null !== $this->date_shipped;
    }

    /**
     * @inheritdoc
     */
    public function getDateShipped()
    {
        return $this->date_shipped;
    }

    /**
     * @inheritdoc
     */
    public function setDateShipped($timestamp = null)
    {
        $this->date_shipped = $timestamp;
    }

    /**
     * Returns true if checkout has been completed
     *
     * @return bool
     */
    public function isCheckoutComplete()
    {
        return (bool) $this->checkout_complete;
    }

    /**
     * Get label for current order status
     *
     * @return string
     */
    public function getStatusLabel()
    {
        try {
            /** @var OrderStatus $objStatus */
            $objStatus = $this->getRelated('order_status');

            return (null === $objStatus) ? '' : $objStatus->getName();
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Get the alias for current order status
     *
     * @return string
     */
    public function getStatusAlias()
    {
        try {
            /** @var OrderStatus $objStatus */
            $objStatus = $this->getRelated('order_status');

            return null === $objStatus ? $this->order_status : $objStatus->getAlias();
        } catch (\Exception $e) {
            return $this->order_status;
        }
    }

    /**
     * Process the order checkout
     *
     * @return bool
     */
    public function checkout()
    {
        if ($this->isCheckoutComplete()) {
            return true;
        }

        // Finish and lock the order
        // (do this now, because otherwise surcharges etc. will not be loaded form the database)
        $this->checkout_complete = true;
        $this->generateDocumentNumber(
            $this->getConfig()->orderPrefix,
            (int) $this->getConfig()->orderDigits
        );

        if (!$this->isLocked()) {
            $this->lock();
        }

        System::log('New order ID ' . $this->id . ' has been placed', __METHOD__, TL_ACCESS);

        // Delete cart after migrating to order
        if (($objCart = Cart::findByPk($this->source_collection_id)) !== null) {
            $objCart->delete();
        }

        // Delete all other orders that relate to the current cart
        if (($objOrders = static::findSiblingsBy('source_collection_id', $this)) !== null) {

            /** @var static $objOrder */
            foreach ($objOrders as $objOrder) {
                if (!$objOrder->isCheckoutComplete()) {
                    $objOrder->delete(true);
                }
            }
        }

        $notificationIds = array_filter(explode(',', $this->nc_notification));

        // Send the notifications
        foreach ($notificationIds as $notificationId) {
            // Generate tokens
            $arrTokens = $this->getNotificationTokens($notificationId);

            // Send notification
            $blnNotificationError = true;

            /** @var Notification $objNotification */
            if (($objNotification = Notification::findByPk($notificationId)) !== null) {
                $arrResult = $objNotification->send($arrTokens, $this->language);

                if (\count($arrResult) > 0 && !\in_array(false, $arrResult, true)) {
                    $blnNotificationError = false;
                }
            }

            if ($blnNotificationError === true) {
                System::log('Error sending new order notification for order ID ' . $this->id, __METHOD__, TL_ERROR);
            }
        }

        // Set order status only if a payment module has not already set it
        if ($this->order_status == 0) {
            $this->updateOrderStatus($this->getRelated('config_id')->orderstatus_new);
        }

        // !HOOK: post-process checkout
        if (isset($GLOBALS['ISO_HOOKS']['postCheckout']) && \is_array($GLOBALS['ISO_HOOKS']['postCheckout'])) {
            // Generate the default notification tokens if none set yet
            if (!isset($arrTokens)) {
                $arrTokens = $this->getNotificationTokens(0);
            }

            foreach ($GLOBALS['ISO_HOOKS']['postCheckout'] as $callback) {
                System::importStatic($callback[0])->{$callback[1]}($this, $arrTokens);
            }
        }

        return true;
    }

    /**
     * Complete order if the checkout has been made. This will cleanup session data
     *
     * @return bool
     */
    public function complete()
    {
        if ($this->isCheckoutComplete()) {
            unset($_SESSION['CHECKOUT_DATA'], $_SESSION['FILES']);

            // Retain custom config ID
            if (($objCart = Isotope::getCart()) !== null && $objCart->config_id != $this->config_id) {
                $objCart->config_id = $this->config_id;
            }

            // !HOOK: complete checkout
            if (isset($GLOBALS['ISO_HOOKS']['checkoutComplete']) && \is_array($GLOBALS['ISO_HOOKS']['checkoutComplete'])) {
                foreach ($GLOBALS['ISO_HOOKS']['checkoutComplete'] as $callback) {
                    System::importStatic($callback[0])->{$callback[1]}($this);
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Update the status of this order and trigger actions (email & hook)
     *
     * @param int|array $updates
     * @param int $flags Order::STATUS_UPDATE_SKIP_NOTIFICATION and/or Order::STATUS_UPDATE_SKIP_LOG
     *
     * @return bool
     */
    public function updateOrderStatus($updates, $flags = 0)
    {
        // For BC reasons the parameter can be the new order status ID
        if (!is_array($updates)) {
            $updates = ['order_status' => $updates];
        }

        $previous = [];
        $hasChanges = false;
        foreach ($updates as $k => $v) {
            $previous[$k] = $this->{$k};
            $hasChanges = $hasChanges ?: $v !== $previous[$k];
        }

        if (!isset($updates['order_status'])) {
            throw new \LogicException('You must update the order status when calling Order::updateOrderStatus()');
        }

        if (!$hasChanges) {
            return true;
        }

        /** @var OrderStatus $objNewStatus */
        $objNewStatus = OrderStatus::findByPk($updates['order_status']);

        if (null === $objNewStatus) {
            return false;
        }

        // !HOOK: allow to cancel a status update
        if (isset($GLOBALS['ISO_HOOKS']['preOrderStatusUpdate'])
            && \is_array($GLOBALS['ISO_HOOKS']['preOrderStatusUpdate'])
        ) {
            foreach ($GLOBALS['ISO_HOOKS']['preOrderStatusUpdate'] as $callback) {
                $blnCancel = System::importStatic($callback[0])->{$callback[1]}($this, $objNewStatus, $updates);

                if ($blnCancel === true) {
                    return false;
                }
            }
        }

        // Add the payment date if there is none
        if ($objNewStatus->isPaid() && $this->date_paid == '' && !isset($updates['date_paid'])) {
            $updates['date_paid'] = time();
        }

        // Store old status and set the new one
        $oldStatusId = $this->order_status;
        $oldStatusLabel = $this->getStatusLabel();
        foreach ($updates as $k => $v) {
            $this->{$k} = $v;
        }
        $this->save();

        if (!($flags & static::STATUS_UPDATE_SKIP_NOTIFICATION)) {
            // Trigger notification
            $blnNotificationError = null;
            foreach (array_filter(explode(',', $objNewStatus->notification)) as $notificationId) {
                $arrTokens = $this->getNotificationTokens($notificationId);

                // Override order status and save the old one to the tokens too
                $arrTokens['order_status_id']       = $objNewStatus->id;
                $arrTokens['order_status']          = $objNewStatus->getName();
                $arrTokens['order_status_old']      = $oldStatusLabel;
                $arrTokens['order_status_id_old']   = $oldStatusId;

                $blnNotificationError = true;

                /** @var Notification $objNotification */
                if (($objNotification = Notification::findByPk($notificationId)) !== null) {
                    $arrResult = $objNotification->send($arrTokens, $this->language);

                    if (\in_array(false, $arrResult, true)) {
                        $blnNotificationError = true;
                        System::log(
                            'Error sending status update notification for order ID ' . $this->id,
                            __METHOD__,
                            TL_ERROR
                        );
                    } elseif (\count($arrResult) > 0) {
                        $blnNotificationError = false;
                    }
                } else {
                    System::log('Invalid notification for order status ID ' . $objNewStatus->id, __METHOD__, TL_ERROR);
                }
            }

            if (CompatibilityHelper::isBackend()) {
                Message::addConfirmation($GLOBALS['TL_LANG']['tl_iso_product_collection']['orderStatusUpdate']);

                if ($blnNotificationError === true) {
                    Message::addError($GLOBALS['TL_LANG']['tl_iso_product_collection']['orderStatusNotificationError']);
                } elseif ($blnNotificationError === false) {
                    Message::addConfirmation($GLOBALS['TL_LANG']['tl_iso_product_collection']['orderStatusNotificationSuccess']);
                }
            }
        }

        // Add a log entry
        if (!($flags & static::STATUS_UPDATE_SKIP_LOG)) {
            $log = new ProductCollectionLog();
            $log->pid = $this->id;
            $log->tstamp = time();
            $log->setData([
                'order_status' => $this->order_status,
                'date_paid' => $this->date_paid,
                'date_shipped' => $this->date_shipped,
            ]);
            $log->save();
        }

        // !HOOK: order status has been updated
        if (isset($GLOBALS['ISO_HOOKS']['postOrderStatusUpdate'])
            && \is_array($GLOBALS['ISO_HOOKS']['postOrderStatusUpdate'])
        ) {
            foreach ($GLOBALS['ISO_HOOKS']['postOrderStatusUpdate'] as $callback) {
                System::importStatic($callback[0])->{$callback[1]}($this, $oldStatusId, $objNewStatus);
            }
        }

        // Trigger payment and shipping methods that implement the interface
        if (($objPayment = $this->getPaymentMethod()) !== null && $objPayment instanceof IsotopeOrderStatusAware) {
            $objPayment->onOrderStatusUpdate($this, $oldStatusId, $objNewStatus);
        }
        if (($objShipping = $this->getShippingMethod()) !== null && $objShipping instanceof IsotopeOrderStatusAware) {
            $objShipping->onOrderStatusUpdate($this, $oldStatusId, $objNewStatus);
        }

        return true;
    }

    /**
     * Retrieve the array of notification data for parsing simple tokens
     *
     * @param int $intNotification
     *
     * @return array
     */
    public function getNotificationTokens($intNotification)
    {
        $objConfig = $this->getRelated('config_id') ?: Isotope::getConfig();
        Isotope::setConfig($objConfig);

        $arrTokens                    = StringUtil::deserialize($this->email_data, true);
        $arrTokens['uniqid']          = $this->uniqid;
        $arrTokens['order_status_id'] = $this->order_status;
        $arrTokens['order_status']    = Controller::replaceInsertTags($this->getStatusLabel());
        $arrTokens['recipient_email'] = $this->getEmailRecipient();
        $arrTokens['order_id']        = $this->id;
        $arrTokens['order_items']     = $this->sumItemsQuantity();
        $arrTokens['order_products']  = $this->countItems();
        $arrTokens['order_subtotal']  = Isotope::formatPriceWithCurrency($this->getSubtotal(), false, $objConfig->currency);
        $arrTokens['order_total']     = Isotope::formatPriceWithCurrency($this->getTotal(), false, $objConfig->currency);
        $arrTokens['document_number'] = $this->document_number;
        $arrTokens['bank_name']       = $objConfig->bankName;
        $arrTokens['bank_account']    = $objConfig->bankAccount;
        $arrTokens['bank_code']       = $objConfig->bankCode;
        $arrTokens['tax_number']      = $objConfig->taxNumber;
        $arrTokens['cart_html']       = '';
        $arrTokens['cart_text']       = '';
        $arrTokens['document']        = '';

        // Add all the collection fields
        foreach ($this->row() as $k => $v) {
            $arrTokens['collection_' . $k] = $v;
        }

        // Add billing/customer address fields
        if (($objAddress = $this->getBillingAddress()) !== null) {
            foreach ($objAddress->row() as $k => $v) {
                $arrTokens['billing_address_' . $k] = Format::dcaValue(Address::getTable(), $k, $v);

                // @deprecated (use ##billing_address_*##)
                $arrTokens['billing_' . $k] = $arrTokens['billing_address_' . $k];
            }

            $arrTokens['billing_address'] = $objAddress->generate($objConfig->getBillingFieldsConfig());

            // @deprecated (use ##billing_address##)
            $arrTokens['billing_address_text'] = $arrTokens['billing_address'];
        }

        // Add shipping address fields
        if (($objAddress = $this->getShippingAddress()) !== null) {
            foreach ($objAddress->row() as $k => $v) {
                $arrTokens['shipping_address_' . $k] = Format::dcaValue(Address::getTable(), $k, $v);

                // @deprecated (use ##billing_address_*##)
                $arrTokens['shipping_' . $k] = $arrTokens['shipping_address_' . $k];
            }

            $arrTokens['shipping_address'] = $objAddress->generate($objConfig->getShippingFieldsConfig());

            // Shipping address equals billing address
            // @deprecated (use ##shipping_address##)
            if ($objAddress->id == $this->getBillingAddress()->id) {
                $arrTokens['shipping_address_text'] = ($this->requiresPayment() ? $GLOBALS['TL_LANG']['MSC']['useBillingAddress'] : $GLOBALS['TL_LANG']['MSC']['useCustomerAddress']);
            } else {
                $arrTokens['shipping_address_text'] = $arrTokens['shipping_address'];
            }
        }

        // Add payment method info
        if ($this->hasPayment() && ($objPayment = $this->getPaymentMethod()) !== null) {
            $arrTokens['payment_id']        = $objPayment->getId();
            $arrTokens['payment_label']     = Controller::replaceInsertTags($objPayment->getLabel());
            $arrTokens['payment_note']      = Controller::replaceInsertTags($objPayment->getNote());

            if ($objPayment instanceof IsotopeNotificationTokens) {
                $arrTokens = array_merge($arrTokens, $objPayment->getNotificationTokens($this));
            }
        }

        // Add shipping method info
        if ($this->hasShipping() && ($objShipping = $this->getShippingMethod()) !== null) {
            $arrTokens['shipping_id']        = $objShipping->getId();
            $arrTokens['shipping_label']     = Controller::replaceInsertTags($objShipping->getLabel());
            $arrTokens['shipping_note']      = Controller::replaceInsertTags($objShipping->getNote());

            if ($objShipping instanceof IsotopeNotificationTokens) {
                $arrTokens = array_merge($arrTokens, $objShipping->getNotificationTokens($this));
            }
        }

        // Add config fields
        if ($this->getRelated('config_id') !== null) {
            foreach ($this->getRelated('config_id')->row() as $k => $v) {
                $arrTokens['config_' . $k] = Format::dcaValue($this->getRelated('config_id')->getTable(), $k, $v);
            }
        }

        // Add member fields
        if ($this->member > 0 && $this->getRelated('member') !== null) {
            foreach ($this->getRelated('member')->row() as $k => $v) {
                $arrTokens['member_' . $k] = Format::dcaValue($this->getRelated('member')->getTable(), $k, $v);
            }
        }

        /** @var Notification|object $objNotification */
        if ($intNotification > 0 && ($objNotification = Notification::findByPk($intNotification)) !== null) {
            /** @var \Isotope\Template|object $objTemplate */
            $objTemplate                 = new \Isotope\Template($objNotification->iso_collectionTpl);
            $objTemplate->isNotification = true;

            $this->addToTemplate(
                $objTemplate,
                array(
                    'gallery' => $objNotification->iso_gallery,
                    'sorting' => static::getItemsSortingCallable($objNotification->iso_orderCollectionBy),
                )
            );

            $arrTokens['cart_html'] = Controller::replaceInsertTags($objTemplate->parse(), false);
            $objTemplate->textOnly  = true;
            $arrTokens['cart_text'] = strip_tags(Controller::replaceInsertTags($objTemplate->parse(), false));

            // Generate and "attach" document
            /** @var \Isotope\Interfaces\IsotopeDocument $objDocument */
            if ($objNotification->iso_document > 0
                && (($objDocument = Document::findByPk($objNotification->iso_document)) !== null)
            ) {
                $strFilePath           = $objDocument->outputToFile($this, TL_ROOT . '/system/tmp');
                $arrTokens['document'] = str_replace(TL_ROOT . '/', '', $strFilePath);
            }
        }

        // !HOOK: add custom email tokens
        if (isset($GLOBALS['ISO_HOOKS']['getOrderNotificationTokens'])
            && \is_array($GLOBALS['ISO_HOOKS']['getOrderNotificationTokens'])
        ) {
            foreach ($GLOBALS['ISO_HOOKS']['getOrderNotificationTokens'] as $callback) {
                $arrTokens = System::importStatic($callback[0])->{$callback[1]}($this, $arrTokens);
            }
        }

        return $arrTokens;
    }

    /**
     * Include downloads when adding items to template
     *
     * @return array
     */
    protected function addItemsToTemplate(Template $objTemplate, $varCallable = null)
    {
        $taxIds          = [];
        $arrItems        = [];
        $arrAllDownloads = [];

        foreach ($this->getItems($varCallable) as $objItem) {
            $arrDownloads = [];
            $arrItem      = $this->generateItem($objItem);

            foreach ($objItem->getDownloads() as $objDownload) {
                $arrDownloads = array_merge($arrDownloads, $objDownload->getForTemplate($this->isPaid(), $this->orderdetails_page));
            }

            $arrItem['downloads'] = $arrDownloads;
            $arrAllDownloads      = array_merge($arrAllDownloads, $arrDownloads);

            $taxIds[]   = $arrItem['tax_id'];
            $arrItems[] = $arrItem;
        }

        RowClass::withKey('rowClass')->addCount('row_')->addFirstLast('row_')->addEvenOdd('row_')->applyTo($arrItems);

        $objTemplate->items         = $arrItems;
        $objTemplate->downloads     = $arrAllDownloads;
        $objTemplate->total_tax_ids = \count(array_count_values($taxIds));

        return $arrItems;
    }

    /**
     * Generate unique order ID including the order prefix
     *
     * @return string
     */
    protected function generateUniqueId()
    {
        if (!empty($this->arrData['uniqid'])) {
            return $this->arrData['uniqid'];
        }

        $objConfig = $this->getConfig();

        if (null === $objConfig) {
            $objConfig = Isotope::getConfig();
        }

        return uniqid(
            Controller::replaceInsertTags((string) $objConfig->orderPrefix, false),
            true
        );
    }
}
