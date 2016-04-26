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

namespace Isotope\Module;

use Haste\Generator\RowClass;
use Haste\Util\Format;
use Haste\Util\Url;
use Isotope\Isotope;
use Isotope\Model\ProductCollection\Order;
use Isotope\Template;


/**
 * Class OrderHistory
 *
 * Front end module Isotope "order history".
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
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
     * Display a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ISOTOPE ECOMMERCE: ORDER HISTORY ###';

            $objTemplate->title = $this->headline;
            $objTemplate->id    = $this->id;
            $objTemplate->link  = $this->name;
            $objTemplate->href  = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        $this->iso_config_ids = deserialize($this->iso_config_ids);

        if (FE_USER_LOGGED_IN !== true || !is_array($this->iso_config_ids) || !count($this->iso_config_ids)) // Can't use empty() because its an object property (using __get)
        {
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
        $arrOrders = array();
        $objOrders = Order::findBy(
            array(
                'order_status>0',
                'member=?',
                'config_id IN (' . implode(',', array_map('intval', $this->iso_config_ids)) . ')'),
            array(\FrontendUser::getInstance()->id),
            array('order' => 'locked DESC')
        );

        // No orders found, just display an "empty" message
        if (null === $objOrders) {
            $this->Template          = new Template('mod_message');
            $this->Template->type    = 'empty';
            $this->Template->message = $GLOBALS['TL_LANG']['ERR']['emptyOrderHistory'];

            return;
        }

        /** @type Order $objOrder */
        foreach ($objOrders as $objOrder) {
            Isotope::setConfig($objOrder->getConfig());

            $arrOrders[] = array
            (
                'collection' => $objOrder,
                'raw'        => $objOrder->row(),
                'date'       => Format::date($objOrder->locked),
                'time'       => Format::time($objOrder->locked),
                'datime'     => Format::datim($objOrder->locked),
                'grandTotal' => Isotope::formatPriceWithCurrency($objOrder->getTotal()),
                'status'     => $objOrder->getStatusLabel(),
                'link'       => $this->jumpTo ? (Url::addQueryString('uid=' . $objOrder->uniqid, $this->jumpTo)) : '',
                'class'      => $objOrder->getStatusAlias(),
            );
        }

        RowClass::withKey('class')->addFirstLast()->addEvenOdd()->applyTo($arrOrders);

        $this->Template->orders = $arrOrders;
    }
}
