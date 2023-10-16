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

use Isotope\CompatibilityHelper;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\FrontendUser;
use Contao\Input;
use Contao\PageError403;
use Contao\PageModel;
use Contao\StringUtil;
use Haste\Util\Format;
use Isotope\Frontend\ProductCollectionAction\ReorderAction;
use Isotope\Model\ProductCollection\Order;
use Isotope\Template;

/**
 * @property int    $iso_cart_jumpTo
 * @property int    $iso_gallery
 * @property string $iso_collectionTpl
 * @property string $iso_orderCollectionBy
 */
class OrderDetails extends AbstractProductCollection
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_iso_orderdetails';

    /**
     * Display a wildcard in the back end
     *
     * @param bool $blnBackend
     *
     * @return string
     */
    public function generate($blnBackend = false)
    {
        if ($blnBackend) {
            $this->backend = true;
            $this->jumpTo  = 0;
            $this->setWildcard(false);
        }

        return parent::generate();
    }

    /**
     * Generate the module
     */
    protected function compile()
    {
        $order = $this->getCollection();

        if (null === $order) {
            return;
        }

        parent::compile();

        $this->Template->info                 = StringUtil::deserialize($order->checkout_info, true);
        $this->Template->date                 = Format::date($order->locked);
        $this->Template->time                 = Format::time($order->locked);
        $this->Template->datim                = Format::datim($order->locked);
        $this->Template->orderDetailsHeadline = sprintf($GLOBALS['TL_LANG']['MSC']['orderDetailsHeadline'], $order->getDocumentNumber(), $this->Template->datim);
        $this->Template->orderStatus          = sprintf($GLOBALS['TL_LANG']['MSC']['orderStatusHeadline'], $order->getStatusLabel());
        $this->Template->orderStatusKey       = $order->getStatusAlias();
    }

    /**
     * @inheritdoc
     */
    protected function getCollection()
    {
        static $order = false;

        if (false !== $order) {
            return $order;
        }

        $order = Order::findOneBy('uniqid', (string) Input::get('uid'));

        // Also check owner (see #126)
        if (null === $order
            || (FE_USER_LOGGED_IN === true
                && $order->member > 0
                && FrontendUser::getInstance()->id != $order->member
            )
        ) {
            $this->Template          = new Template('mod_message');
            $this->Template->type    = 'error';
            $this->Template->message = $GLOBALS['TL_LANG']['ERR']['orderNotFound'];

            return null;
        }

        // Order belongs to a member but not logged in
        if (CompatibilityHelper::isFrontend() && $this->iso_loginRequired && $order->member > 0 && FE_USER_LOGGED_IN !== true) {
            throw new AccessDeniedException();
        }

        if CompatibilityHelper::isFrontend()) {
            /** @var PageModel $objPage */
            global $objPage;

            $order->preventSaving(false);
            $order->orderdetails_page = $objPage->id;
        }

        return $order;
    }

    /**
     * @inheritdoc
     */
    protected function getEmptyMessage()
    {
        // An order can never be empty
        return '';
    }

    /**
     * @inheritdoc
     */
    protected function canEditQuantity()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    protected function canRemoveProducts()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function getActions()
    {
        if (CompatibilityHelper::isBackend()) {
            return [];
        }

        return [
            new ReorderAction($this),
        ];
    }
}
