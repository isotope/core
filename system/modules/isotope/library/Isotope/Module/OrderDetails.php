<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Module;

use Contao\PageError403;
use Haste\Util\Format;
use Haste\Util\Url;
use Isotope\Isotope;
use Isotope\Message;
use Isotope\Model\ProductCollection;
use Isotope\Model\ProductCollection\Order;
use Isotope\Template;

/**
 * @property int    $iso_cart_jumpTo
 * @property int    $iso_gallery
 * @property string $iso_collectionTpl
 * @property string $iso_orderCollectionBy
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
     *
     * @param bool $blnBackend
     *
     * @return string
     */
    public function generate($blnBackend = false)
    {
        if ('BE' === TL_MODE && !$blnBackend) {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ISOTOPE ECOMMERCE: ORDER DETAILS ###';

            $objTemplate->title = $this->headline;
            $objTemplate->id    = $this->id;
            $objTemplate->link  = $this->name;
            $objTemplate->href  = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        if ($blnBackend) {
            $this->backend = true;
            $this->jumpTo  = 0;
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
            $this->Template          = new Template('mod_message');
            $this->Template->type    = 'error';
            $this->Template->message = $GLOBALS['TL_LANG']['ERR']['orderNotFound'];

            return;
        }

        // Order belongs to a member but not logged in
        if ('FE' === TL_MODE && $this->iso_loginRequired && $objOrder->member > 0 && FE_USER_LOGGED_IN !== true) {
            /** @var \PageModel $objPage */
            global $objPage;

            /** @var PageError403 $objHandler */
            $objHandler = new $GLOBALS['TL_PTY']['error_403']();
            $objHandler->generate($objPage->id);
            exit;
        }

        if ($this->iso_cart_jumpTo && (int) \Input::get('reorder') === (int) $objOrder->id) {
            $this->reorder($objOrder);
        }

        Isotope::setConfig($objOrder->getConfig());

        /** @var Template|\stdClass $objTemplate */
        $objTemplate               = new Template($this->iso_collectionTpl);
        $objTemplate->linkProducts = true;

        $objOrder->addToTemplate(
            $objTemplate,
            array(
                'gallery' => $this->iso_gallery,
                'sorting' => ProductCollection::getItemsSortingCallable($this->iso_orderCollectionBy),
            )
        );

        $this->Template->collection           = $objOrder;
        $this->Template->products             = $objTemplate->parse();
        $this->Template->info                 = deserialize($objOrder->checkout_info, true);
        $this->Template->date                 = Format::date($objOrder->locked);
        $this->Template->time                 = Format::time($objOrder->locked);
        $this->Template->datim                = Format::datim($objOrder->locked);
        $this->Template->orderDetailsHeadline = sprintf($GLOBALS['TL_LANG']['MSC']['orderDetailsHeadline'], $objOrder->getDocumentNumber(), $this->Template->datim);
        $this->Template->orderStatus          = sprintf($GLOBALS['TL_LANG']['MSC']['orderStatusHeadline'], $objOrder->getStatusLabel());
        $this->Template->orderStatusKey       = $objOrder->getStatusAlias();
        $this->Template->reorder              = $this->iso_cart_jumpTo ? (Url::addQueryString('reorder=' . $objOrder->id)) : '';
    }

    private function reorder(Order $order)
    {
        Isotope::getCart()->copyItemsFrom($order);

        Message::addConfirmation($GLOBALS['TL_LANG']['MSC']['reorderConfirmation']);

        \Controller::redirect(
            Url::addQueryString(
                'continue=' . base64_encode(\System::getReferer()),
                $this->iso_cart_jumpTo
            )
        );
    }
}
