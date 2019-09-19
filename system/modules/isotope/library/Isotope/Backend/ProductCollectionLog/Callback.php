<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\ProductCollectionLog;

use Contao\Backend;
use Contao\BackendUser;
use Contao\Config;
use Contao\Controller;
use Contao\Database;
use Contao\DataContainer;
use Contao\Date;
use Contao\Input;
use Contao\System;
use Isotope\Frontend;
use Isotope\Model\OrderStatus;
use Isotope\Model\ProductCollection\Order;
use NotificationCenter\Model\Notification;

class Callback
{
    /**
     * On data container load callback
     */
    public function onLoadCallback()
    {
        // Do not allow access to any view other than edit and create
        if ('edit' !== Input::get('act') && 'create' !== Input::get('act')) {
            Controller::redirect(Backend::getReferer());
        }

        // Remove the "save" button so default action is "save and close"
        if ('edit' === Input::get('act')) {
            $GLOBALS['TL_MOOTOOLS'][] = '<script>document.getElementById("save").remove()</script>';
            $GLOBALS['TL_MOOTOOLS'][] = '<script>document.getElementById("saveNcreate").remove()</script>';
            $GLOBALS['TL_MOOTOOLS'][] = '<script>document.getElementById("saveNback").remove()</script>';
            $GLOBALS['TL_MOOTOOLS'][] = '<script>document.getElementById("sbtog").remove()</script>';
        }
    }

    /**
     * On data container submit callback
     *
     * @param DataContainer $dc
     */
    public function onSubmitCallback(DataContainer $dc)
    {
        if (Input::post('SUBMIT_TYPE') === 'auto') {
            return;
        }

        if (!$dc->activeRecord->author) {
            Database::getInstance()->prepare("UPDATE {$dc->table} SET author=? WHERE id=?")->execute(BackendUser::getInstance()->id, $dc->id);
        }

        // Update the order
        if (($order = Order::findByPk($dc->activeRecord->pid)) !== null) {
            if ('BE' === TL_MODE) {
                if ($order->pageId == 0) {
                    unset($GLOBALS['objPage']);
                }

                Frontend::loadOrderEnvironment($order);
            }

            // Status update has been cancelled, do not update
            if (false !== $GLOBALS['ISO_ORDER_STATUS']) {
                foreach ($GLOBALS['ISO_ORDER_STATUS'] as $from => $to) {
                    $order->order_status = $from;
                    if (!$order->updateOrderStatus($to)) {
                        // Will save the old status set in the line above
                        $order->save();
                    }
                }
            }

            $order->setDatePaid($dc->activeRecord->date_paid);
            $order->setDateShipped($dc->activeRecord->date_shipped);
            $order->notes = $dc->activeRecord->notes;
            $order->save();

            // Send a notification
            if ($dc->activeRecord->sendNotification && $dc->activeRecord->notification && ($notification = Notification::findByPk($dc->activeRecord->notification)) !== null) {
                $tokens = $order->getNotificationTokens($dc->activeRecord->notification);
                $tokens['log_shipping_tracking'] = str_replace("\n", '<br>', trim($dc->activeRecord->notification_shipping_tracking));
                $tokens['log_customer_notes'] = str_replace("\n", '<br>', trim($dc->activeRecord->notification_customer_notes));

                $notification->send($tokens);
            }

            // !HOOK: add additional functionality when saving collection
            if (isset($GLOBALS['ISO_HOOKS']['saveCollection']) && \is_array($GLOBALS['ISO_HOOKS']['saveCollection'])) {
                foreach ($GLOBALS['ISO_HOOKS']['saveCollection'] as $callback) {
                    System::importStatic($callback[0])->{$callback[1]}($order);
                }
            }
        }
    }

    /**
     * On child record callback
     *
     * @param array $row
     *
     * @return string
     */
    public function onChildRecordCallback(array $row)
    {
        return sprintf(
            '<span class="tl_gray">[%s]</span> <strong>%s</strong>: %s; <strong>%s</strong>: %s',
            Date::parse(Config::get('datimFormat'), $row['tstamp']),
            $GLOBALS['TL_LANG']['tl_iso_product_collection_log']['order_status'][0],
            OrderStatus::findByPk($row['order_status'])->name,
            $GLOBALS['TL_LANG']['tl_iso_product_collection_log']['notes'][0],
            $row['notes'] ?: '–'
        );
    }

    /**
     * On order status load callback
     *
     * @param int $value
     * @param DataContainer $dc
     *
     * @return int
     */
    public function onOrderStatusLoadCallback($value, DataContainer $dc)
    {
        return $this->getOrderFieldValue($dc, $value, 'order_status');
    }

    /**
     * On order status save callback
     *
     * @param string
     * @param DataContainer
     *
     * @return string
     */
    public function onOrderStatusSaveCallback($value, DataContainer $dc)
    {
        $GLOBALS['ISO_ORDER_STATUS'] = false;

        // Trigger order status update when changing the status in the backend
        if ($dc->activeRecord && $dc->activeRecord->order_status != $value) {
            $GLOBALS['ISO_ORDER_STATUS'] = array($dc->activeRecord->order_status => $value);
        }

        return $value;
    }

    /**
     * On date paid load callback
     *
     * @param int $value
     * @param DataContainer $dc
     *
     * @return int
     */
    public function onDatePaidLoadCallback($value, DataContainer $dc)
    {
        return $this->getOrderFieldValue($dc, $value, 'date_paid');
    }

    /**
     * On date shipped load callback
     *
     * @param int $value
     * @param DataContainer $dc
     *
     * @return int
     */
    public function onDateShippedLoadCallback($value, DataContainer $dc)
    {
        return $this->getOrderFieldValue($dc, $value, 'date_shipped');
    }

    /**
     * On notes load callback
     *
     * @param int $value
     * @param DataContainer $dc
     *
     * @return int
     */
    public function onNotesLoadCallback($value, DataContainer $dc)
    {
        return $this->getOrderFieldValue($dc, $value, 'notes');
    }

    /**
     * On notification options callback
     *
     * @return array
     */
    public function onNotificationOptionsCallback()
    {
        $options = [];

        if (($notifications = Notification::findBy('type', 'iso_order_status_change', ['order' => 'title'])) !== null) {
            $options = $notifications->fetchEach('title');
        }

        return $options;
    }

    /**
     * Get the order field value
     *
     * @param DataContainer $dc
     * @param mixed $value
     * @param string $field
     *
     * @return mixed
     */
    private function getOrderFieldValue(DataContainer $dc, $value, $field)
    {
        if (isset($_POST[$field]) || $dc === null || !$dc->activeRecord->pid || ($order = Order::findByPk($dc->activeRecord->pid)) === null) {
            return $value;
        }

        return $order->$field;
    }
}
