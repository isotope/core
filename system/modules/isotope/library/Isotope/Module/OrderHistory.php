<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2013 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Module;

use Haste\Generator\RowClass;
use Haste\Util\Format;
use Isotope\Isotope;
use Isotope\Model\ProductCollection\Order;


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
        $objOrders = Order::findBy(array('order_status>0', 'member=?', 'config_id IN (?)'), array(\FrontendUser::getInstance()->id, implode("','", $this->iso_config_ids)), array('order' => 'locked DESC'));

        // No orders found, just display an "empty" message
        if (null === $objOrders) {
            $this->Template          = new \Isotope\Template('mod_message');
            $this->Template->type    = 'empty';
            $this->Template->message = $GLOBALS['TL_LANG']['ERR']['emptyOrderHistory'];

            return;
        }

        while ($objOrders->next()) {
            Isotope::setConfig($objOrders->current()->getRelated('config_id'));

            $arrOrders[] = array
            (
                'collection' => $objOrders->current(),
                'raw'        => $objOrders->current()->row(),
                'date'       => Format::date($objOrders->current()->locked),
                'time'       => Format::time($objOrders->current()->locked),
                'datime'     => Format::datim($objOrders->current()->locked),
                'grandTotal' => Isotope::formatPriceWithCurrency($objOrders->current()->getTotal()),
                'status'     => $objOrders->current()->getStatusLabel(),
                'link'       => ($this->jumpTo ? (\Haste\Util\Url::addQueryString('uid=' . $objOrders->current()->uniqid, $this->jumpTo)) : ''),
                'class'      => $objOrders->current()->getStatusAlias(),
            );
        }

        RowClass::withKey('class')->addFirstLast()->addEvenOdd()->applyTo($arrOrders);

        $this->Template->orders = $arrOrders;
    }
}
