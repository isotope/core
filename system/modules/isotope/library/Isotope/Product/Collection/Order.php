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

namespace Isotope\Product\Collection;

use Isotope\Interfaces\IsotopeProduct;


/**
 * Class Order
 *
 * Provide methods to handle Isotope orders.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
class Order extends Collection
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_orders';

    /**
     * Name of the child table
     * @var string
     */
    protected static $ctable = 'tl_iso_order_items';

    /**
     * This current order's unique ID with eventual prefix
     * @param string
     */
    protected $strOrderId = '';

    /**
     * Lock products from apply rule prices
     * @var boolean
     */
    protected $blnLocked = true;


    public function __construct(\Database\Result $objResult=null)
    {
        parent::__construct($objResult);

        if ($objResult !== null) {
            if ($this->payment_id > 0) {
                $objPayment = $this->Database->execute("SELECT * FROM tl_iso_payment_modules WHERE id=" . $this->payment_id);

                if ($objPayment->numRows) {
                    try {
                        $this->Payment = \Isotope\Factory\Payment::build($objPayment->type, $objPayment->row());
                    } catch (Exception $e) {
                        trigger_error($e->getMessage());
                    }
                }
            }

            if ($this->shipping_id > 0) {
                $objShipping = $this->Database->execute("SELECT * FROM tl_iso_shipping_modules WHERE id=" . $this->shipping_id);

                if ($objShipping->numRows) {
                    try {
                        $this->Shipping = \Isotope\Factory\Shipping::build($objShipping->type, $objShipping->row());
                    } catch (Exception $e) {
                        trigger_error($e->getMessage());
                    }
                }
            }

            // The order_id must not be stored in arrData, or it would overwrite the database on save().
            $this->strOrderId = $this->arrData['order_id'];
            unset($this->arrData['order_id']);
        }
    }


    /**
     * Return a value
     * @param string
     * @return mixed
     */
    public function __get($strKey)
    {
        switch ($strKey)
        {
            case 'order_id':
                return $this->strOrderId;

            case 'paid':
                // Order is paid if a payment date is set
                $paid = (int) $this->date_paid;
                if ($paid > 0 && $paid <= time())
                {
                    return true;
                }

                // Otherwise we check the orderstatus checkbox
                $objStatus = $this->Database->execute("SELECT * FROM tl_iso_orderstatus WHERE id=" . (int) $this->status);
                return $objStatus->paid ? true : false;

            case 'statusLabel':
                $strStatus = $this->Database->prepare("SELECT name FROM tl_iso_orderstatus WHERE id=?")
                                            ->execute($this->arrData['status'])
                                            ->name;

                return $this->Isotope->translate($strStatus);
                break;

            default:
                if (!isset($this->arrCache[$strKey]))
                {
                    switch( $strKey )
                    {
                        case 'billingAddress':
                            $objAddress = new \IsotopeAddressModel();
                            $objAddress->setData(deserialize($this->arrData['billing_address'], true));
                            $this->arrCache[$strKey] = $objAddress;
                            break;

                        case 'shippingAddress':
                            $objAddress = new \IsotopeAddressModel();
                            $objAddress->setData(deserialize($this->arrData['shipping_address'], true));
                            $this->arrCache[$strKey] = $objAddress;
                            break;
                    }
                }

                return parent::__get($strKey);
        }
    }


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
            // Order ID cannot be changed, it is created through Isotope\Product\Collection\Order::generateOrderId on checkout
            case 'order_id':
                throw new InvalidArgumentException('order_id cannot be changed trough __set().');
                break;

            default:
                parent::__set($strKey, $varValue);
        }
    }


    /**
     * Add downloads to this order
     * @param object
     * @param boolean
     * @return array
     */
    public function transferFromCollection(Collection $objCollection, $blnDuplicate=true)
    {
        $time = time();
        $arrIds = parent::transferFromCollection($objCollection, $blnDuplicate);

        // Add product downloads to the order
        $objDownloads = $this->Database->execute("SELECT d.*, ct.product_quantity, ct.id AS item_id FROM {static::$ctable} ct JOIN tl_iso_downloads d ON d.pid IN ((SELECT id FROM tl_iso_products WHERE id=ct.product_id), (SELECT pid FROM tl_iso_products WHERE id=ct.product_id)) WHERE ct.id IN (" . implode(',', $arrIds) . ") GROUP BY ct.id, d.id ORDER BY item_id, sorting");

        while ($objDownloads->next())
        {
            $expires = '';

            if ($objDownloads->expires != '')
            {
                $arrExpires = deserialize($objDownloads->expires, true);
                if ($arrExpires['value'] > 0 && $arrExpires['unit'] != '')
                {
                    $expires = strtotime('+' . $arrExpires['value'] . ' ' . $arrExpires['unit']);
                }
            }

            $arrSet = array
            (
                'pid'					=> $objDownloads->item_id,
                'tstamp'				=> $time,
                'download_id'			=> $objDownloads->id,
                'downloads_remaining'	=> ($objDownloads->downloads_allowed > 0 ? ($objDownloads->downloads_allowed * $objDownloads->product_quantity) : ''),
                'expires'				=> $expires,
            );

            $this->Database->prepare("INSERT INTO tl_iso_order_downloads %s")->set($arrSet)->executeUncached();
        }

        // Update the product IDs of surcharges (see #3029)
        $arrSurcharges = $this->surcharges;

        if (is_array($arrSurcharges) && !empty($arrSurcharges))
        {
            foreach ($arrSurcharges as $k => $arrSurcharge)
            {
                $arrProducts = $arrSurcharge['products'];

                if (is_array($arrProducts) && !empty($arrProducts))
                {
                    foreach ($arrProducts as $kk => $intId)
                    {
                        $arrProducts[$kk] = $arrIds[$intId];
                    }

                    $arrSurcharges[$k]['products'] = $arrProducts;
                }
            }

            $this->surcharges = $arrSurcharges;
        }

        return $arrIds;
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
            $this->Database->query("DELETE FROM tl_iso_order_downloads WHERE pid={$objProduct->cart_id}");
        }

        return false;
    }


    /**
     * Delete downloads when deleting this order
     * @return integer
     */
    public function delete()
    {
        $this->Database->query("DELETE FROM tl_iso_order_downloads WHERE pid IN (SELECT id FROM {static::$ctable} WHERE pid={$this->id})");
        return parent::delete();
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
     * @param object
     * @return boolean
     */
    public function checkout($objCart=null)
    {
        if ($this->checkout_complete)
        {
            return true;
        }

        // This is the case when not using ModuleIsotopeCheckout
        if (!is_object($objCart))
        {
            if (($objCart = Cart::findByPk($this->cart_id)) === null)
            {
                $this->log('Could not find Cart ID '.$this->cart_id.' for Order ID '.$this->id, __METHOD__, TL_ERROR);
                return false;
            }

            // Set the current system to the language when the user placed the order.
            // This will result in correct e-mails and payment description.
            $GLOBALS['TL_LANGUAGE'] = $this->language;
            $this->loadLanguageFile('default');

            // Initialize system
            $this->Isotope->overrideConfig($this->config_id);
            $this->Isotope->Cart = $objCart;
        }

        // !HOOK: pre-process checkout
        if (isset($GLOBALS['ISO_HOOKS']['preCheckout']) && is_array($GLOBALS['ISO_HOOKS']['preCheckout']))
        {
            foreach ($GLOBALS['ISO_HOOKS']['preCheckout'] as $callback)
            {
                $this->import($callback[0]);

                if ($this->$callback[0]->$callback[1]($this, $objCart) === false)
                {
                    $this->log('Callback ' . $callback[0] . '::' . $callback[1] . '() cancelled checkout for Order ID ' . $this->id, __METHOD__, TL_ERROR);
                    return false;
                }
            }
        }

        $arrItemIds = $this->transferFromCollection($objCart);
        $objCart->delete();

        $this->checkout_complete = true;
        $this->status = ($this->new_order_status ? $this->new_order_status : $this->Isotope->Config->orderstatus_new);

        $this->generateOrderId();
        $arrData = $this->getEmailData();

        $this->log('New order ID ' . $this->id . ' has been placed', __METHOD__, TL_ACCESS);

        if ($this->iso_mail_admin && $this->iso_sales_email != '')
        {
            $this->Isotope->sendMail($this->iso_mail_admin, $this->iso_sales_email, $this->language, $arrData, $this->iso_customer_email, $this);
        }

        if ($this->iso_mail_customer && $this->iso_customer_email != '')
        {
            $this->Isotope->sendMail($this->iso_mail_customer, $this->iso_customer_email, $this->language, $arrData, '', $this);
        }
        else
        {
            $this->log('Unable to send customer confirmation for order ID '.$this->id, __METHOD__, TL_ERROR);
        }

        // Store address in address book
        if ($this->iso_addToAddressbook && $this->pid > 0)
        {
            $time = time();

            foreach (array('billing', 'shipping') as $address)
            {
                $arrAddress = deserialize($this->arrData[$address . '_address'], true);

                if ($arrAddress['id'] == 0)
                {
                    $arrAddress = array_intersect_key($arrAddress, array_flip($this->Isotope->Config->{$address . '_fields_raw'}));
                    $arrAddress['pid'] = $this->pid;
                    $arrAddress['tstamp'] = $time;
                    $arrAddress['store_id'] = $this->Isotope->Config->store_id;

                    $this->Database->prepare("INSERT INTO tl_iso_addresses %s")->set($arrAddress)->execute();
                }
            }
        }

        // !HOOK: post-process checkout
        if (isset($GLOBALS['ISO_HOOKS']['postCheckout']) && is_array($GLOBALS['ISO_HOOKS']['postCheckout']))
        {
            foreach ($GLOBALS['ISO_HOOKS']['postCheckout'] as $callback)
            {
                $this->import($callback[0]);
                $this->$callback[0]->$callback[1]($this, $arrItemIds, $arrData);
            }
        }

        $this->save();
        return true;
    }


    /**
     * Complete order if the checkout has been made. This will cleanup session data
     */
    public function complete()
    {
        if ($this->checkout_complete)
        {
            $intConfig = $_SESSION['ISOTOPE']['config_id'];

            unset($_SESSION['CHECKOUT_DATA']);
            unset($_SESSION['ISOTOPE']);
            unset($_SESSION['FORM_DATA']);
            unset($_SESSION['FILES']);

            if ($intConfig > 0)
            {
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
        if ($this->status == $intNewStatus)
        {
            return true;
        }

        $objNewStatus = $this->Database->execute("SELECT * FROM tl_iso_orderstatus WHERE id=" . (int) $intNewStatus);

        if ($objNewStatus->numRows == 0)
        {
            return false;
        }

        // !HOOK: allow to cancel a status update
        if (isset($GLOBALS['ISO_HOOKS']['preOrderStatusUpdate']) && is_array($GLOBALS['ISO_HOOKS']['preOrderStatusUpdate']))
        {
            foreach ($GLOBALS['ISO_HOOKS']['preOrderStatusUpdate'] as $callback)
            {
                $strClass = $callback[0];
                $objCallback = (in_array('getInstance', get_class_methods($strClass))) ? call_user_func(array($strClass, 'getInstance')) : new $strClass();
                $blnCancel = $this->$callback[0]->$callback[1]($this, $objNewStatus, $blnActions);

                if ($blnCancel === true)
                {
                    return false;
                }
            }
        }

        // Trigger email actions
        if ($objNewStatus->mail_customer > 0 || $objNewStatus->mail_admin > 0)
        {
            $arrData = $this->getEmailData();
            $arrData['new_status'] = $objNewStatus->name;

            if ($objNewStatus->mail_customer && $this->iso_customer_email != '')
            {
                $this->Isotope->sendMail($objNewStatus->mail_customer, $this->iso_customer_email, $this->language, $arrData, '', $this);

                if (TL_MODE == 'BE')
                {
                    $this->addConfirmationMessage($GLOBALS['TL_LANG']['tl_iso_orders']['orderStatusEmail']);
                }
            }

            $strSalesEmail = $objNewStatus->sales_email ? $objNewStatus->sales_email : $this->iso_sales_email;
            if ($objNewStatus->mail_admin && $strSalesEmail != '')
            {
                $this->Isotope->sendMail($objNewStatus->mail_admin, $strSalesEmail, $this->language, $arrData, $this->iso_customer_email, $this);
            }
        }

        // Store old status and set the new one
        $intOldStatus = $this->status;
        $this->status = $objNewStatus->id;
        $this->save();

        // !HOOK: order status has been updated
        if (isset($GLOBALS['ISO_HOOKS']['postOrderStatusUpdate']) && is_array($GLOBALS['ISO_HOOKS']['postOrderStatusUpdate']))
        {
            foreach ($GLOBALS['ISO_HOOKS']['postOrderStatusUpdate'] as $callback)
            {
                $strClass = $callback[0];
                $objCallback = (in_array('getInstance', get_class_methods($strClass))) ? call_user_func(array($strClass, 'getInstance')) : new $strClass();
                $this->$callback[0]->$callback[1]($this, $intOldStatus, $objNewStatus, $blnActions);
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
        $arrData['status'] = $this->statusLabel;
        $arrData['status_id'] = $this->arrData['status'];

        foreach ($this->billing_address as $k => $v)
        {
            $arrData['billing_' . $k] = $this->Isotope->formatValue('tl_iso_addresses', $k, $v);
        }

        foreach ($this->shipping_address as $k => $v)
        {
            $arrData['shipping_' . $k] = $this->Isotope->formatValue('tl_iso_addresses', $k, $v);
        }

        if ($this->pid > 0)
        {
            $objUser = $this->Database->execute("SELECT * FROM tl_member WHERE id=" . (int) $this->pid);

            foreach ($objUser->row() as $k => $v)
            {
                $arrData['member_' . $k] = $this->Isotope->formatValue('tl_member', $k, $v);
            }
        }

        // !HOOK: add custom email tokens
        if (isset($GLOBALS['ISO_HOOKS']['getOrderEmailData']) && is_array($GLOBALS['ISO_HOOKS']['getOrderEmailData']))
        {
            foreach ($GLOBALS['ISO_HOOKS']['getOrderEmailData'] as $callback)
            {
                $objCallback = (in_array('getInstance', get_class_methods($callback[0]))) ? call_user_func(array($callback[0], 'getInstance')) : new $callback[0]();
                $arrData = $objCallback->$callback[1]($this, $arrData);
            }
        }

        return $arrData;
    }


    /**
     * Generate the next higher Order-ID based on config prefix, order number digits and existing records
     * @return string
     */
    private function generateOrderId()
    {
        if ($this->strOrderId != '')
        {
            return $this->strOrderId;
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
                    $this->strOrderId = $strOrderId;
                    break;
                }
            }
        }

        if ($this->strOrderId == '')
        {
            $strPrefix = $this->Isotope->replaceInsertTags($this->Isotope->Config->orderPrefix);
            $intPrefix = utf8_strlen($strPrefix);
            $arrConfigIds = $this->Database->execute("SELECT id FROM tl_iso_config WHERE store_id=" . $this->Isotope->Config->store_id)->fetchEach('id');

            // Lock tables so no other order can get the same ID
            $this->Database->lockTables(array('tl_iso_orders'=>'WRITE'));

            // Retrieve the highest available order ID
            $objMax = $this->Database->prepare("SELECT order_id FROM tl_iso_orders WHERE " . ($strPrefix != '' ? "order_id LIKE '$strPrefix%' AND " : '') . "config_id IN (" . implode(',', $arrConfigIds) . ") ORDER BY CAST(" . ($strPrefix != '' ? "SUBSTRING(order_id, " . ($intPrefix+1) . ")" : 'order_id') . " AS UNSIGNED) DESC")->limit(1)->executeUncached();
            $intMax = (int) substr($objMax->order_id, $intPrefix);

            $this->strOrderId = $strPrefix . str_pad($intMax+1, $this->Isotope->Config->orderDigits, '0', STR_PAD_LEFT);
        }

        $this->Database->prepare("UPDATE tl_iso_orders SET order_id=? WHERE id={$this->id}")->executeUncached($this->strOrderId);
        $this->Database->unlockTables();

        return $this->strOrderId;
    }
}
