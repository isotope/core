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

namespace Isotope\Module;

use Isotope\Isotope;
use Isotope\Model\ProductCollection\Order;


/**
 * Class OrderDetails
 *
 * Front end module Isotope "order details".
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
class OrderDetails extends Module
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_iso_orderdetails';

    /**
     * Disable caching of the frontend page if this module is in use
     * @var boolean
     */
    protected $blnDisableCache = true;


    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate($blnBackend=false)
    {
        if (TL_MODE == 'BE' && !$blnBackend) {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ISOTOPE ECOMMERCE: ORDER DETAILS ###';

            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        if ($blnBackend) {
            $this->backend = true;
            $this->jumpTo = 0;
        }

        return parent::generate();
    }


    /**
     * Generate the module
     */
    protected function compile()
    {
        // Also check owner (see #126)
        if (($objOrder = Order::findOneBy('uniqid', (string) \Input::get('uid'))) === null || (FE_USER_LOGGED_IN === true && $objOrder->member > 0 && \FrontendUser::getInstance()->id != $objOrder->member)) {
            $this->Template = new \Isotope\Template('mod_message');
            $this->Template->type = 'error';
            $this->Template->message = $GLOBALS['TL_LANG']['ERR']['orderNotFound'];

            return;
        }

        Isotope::setConfig($objOrder->getRelated('config_id'));

        $objTemplate = new \Isotope\Template($this->iso_collectionTpl);
        $objTemplate->linkProducts = true;

        $objOrder->addToTemplate(
            $objTemplate,
            array(
                'gallery'   => $this->iso_gallery,
                'sorting'   => $this->getProductCollectionItemsSortingCallable(),
            )
        );

        $this->Template->collection = $objOrder;
        $this->Template->products = $objTemplate->parse();
        $this->Template->info = deserialize($objOrder->checkout_info, true);

        $this->Template->date = Isotope::formatDate($objOrder->locked);
        $this->Template->time = Isotope::formatTime($objOrder->locked);
        $this->Template->datim = Isotope::formatDatim($objOrder->locked);
        $this->Template->orderDetailsHeadline = sprintf($GLOBALS['TL_LANG']['MSC']['orderDetailsHeadline'], $objOrder->document_number, $this->Template->datim);
        $this->Template->orderStatus = sprintf($GLOBALS['TL_LANG']['MSC']['orderStatusHeadline'], $objOrder->getStatusLabel());
        $this->Template->orderStatusKey = $objOrder->getStatusAlias();
    }
}
