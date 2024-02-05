<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Module;

use Contao\Controller;
use Contao\Input;
use Contao\System;
use Haste\Generator\RowClass;
use Haste\Util\Format;
use Haste\Util\Url;
use Isotope\CompatibilityHelper;
use Isotope\Isotope;
use Isotope\Message;
use Isotope\Model\ProductCollection\Order;
use Isotope\Template;


/**
 * @property int $iso_cart_jumpTo
 */
class OrderHistory extends Module
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_iso_orderhistory';

    /**
     * Disable caching of the frontend page if this module is in use
     * @var boolean
     */
    protected $blnDisableCache = true;

    /**
     * @inheritDoc
     */
    protected function getSerializedProperties()
    {
        $props = parent::getSerializedProperties();

        $props[] = 'iso_config_ids';

        return $props;
    }

    /**
     * Display a wildcard in the back end
     *
     * @return string
     */
    public function generate()
    {
        if (CompatibilityHelper::isBackend()) {
            return $this->generateWildcard();
        }

        if (FE_USER_LOGGED_IN !== true || 0 === \count($this->iso_config_ids)) {
            return '';
        }

        return parent::generate();
    }


    /**
     * Generate the module
     * @return void
     */
    protected function compile()
    {
        $arrOrders = [];
        $objOrders = Order::findBy(
            [
                'order_status>0',
                'tl_iso_product_collection.member=?',
                'config_id IN (' . implode(',', array_map('intval', $this->iso_config_ids)) . ')'
            ],
            [\FrontendUser::getInstance()->id],
            ['order' => 'locked DESC']
        );

        // No orders found, just display an "empty" message
        if (null === $objOrders) {
            $this->Template          = new Template('mod_message');
            $this->Template->type    = 'empty';
            $this->Template->message = $GLOBALS['TL_LANG']['ERR']['emptyOrderHistory'];

            return;
        }

        $reorder = (int) Input::get('reorder');

        foreach ($objOrders as $objOrder) {
            if ($this->iso_cart_jumpTo && $reorder === (int) $objOrder->id) {
                $this->reorder($objOrder);
            }

            Isotope::setConfig($objOrder->getConfig());

            $arrOrders[] = [
                'collection' => $objOrder,
                'raw'        => $objOrder->row(),
                'date'       => Format::date($objOrder->locked),
                'time'       => Format::time($objOrder->locked),
                'datime'     => Format::datim($objOrder->locked),
                'grandTotal' => Isotope::formatPriceWithCurrency($objOrder->getTotal()),
                'status'     => $objOrder->getStatusLabel(),
                'link'       => $this->jumpTo ? (Url::addQueryString('uid=' . $objOrder->uniqid, $this->jumpTo)) : '',
                'reorder'    => $this->iso_cart_jumpTo ? (Url::addQueryString('reorder=' . $objOrder->id)) : '',
                'class'      => $objOrder->getStatusAlias(),
            ];
        }

        RowClass::withKey('class')->addFirstLast()->addEvenOdd()->applyTo($arrOrders);

        $this->Template->orders = $arrOrders;
    }

    private function reorder(Order $order)
    {
        Isotope::getCart()->copyItemsFrom($order);

        Message::addConfirmation($GLOBALS['TL_LANG']['MSC']['reorderConfirmation']);

        Controller::redirect(
            Url::addQueryString(
                'continue=' . base64_encode(System::getReferer()),
                $this->iso_cart_jumpTo
            )
        );
    }
}
