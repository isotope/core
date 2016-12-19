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
use Isotope\Model\ProductCollection\Wishlist as WishlistCollection;
use Isotope\Template;

class Wishlist extends AbstractProductCollection
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_iso_wishlist';

    /**
     * @inheritdoc
     */
    public function generate()
    {
        if ('FE' === TL_MODE && true !== FE_USER_LOGGED_IN) {
            return '';
        }

        return parent::generate();
    }

    /**
     * @inheritdoc
     */
    protected function getCollection()
    {
        if (\Input::get('uid') != '') {
            $wishlist = WishlistCollection::findOneBy('uniqid', \Input::get('uid'));
        } else {
            $wishlist = WishlistCollection::findByIdForCurrentUser(\Input::get('id'));
        }

        if (null === $wishlist) {
            $this->Template          = new Template('mod_message');
            $this->Template->type    = 'error';
            $this->Template->message = $GLOBALS['TL_LANG']['ERR']['wishlistNotFound'];

            return null;
        }

        // Order belongs to a member but not logged in
        if ('FE' === TL_MODE && $this->iso_loginRequired && \FrontendUser::getInstance()->id != $wishlist->member) {
            /** @var \PageModel $objPage */
            global $objPage;

            /** @var PageError403 $objHandler */
            $objHandler = new $GLOBALS['TL_PTY']['error_403']();
            $objHandler->generate($objPage->id);
            exit;
        }

        return $wishlist;
    }

    /**
     * @inheritdoc
     */
    protected function getEmptyMessage()
    {
        return $GLOBALS['TL_LANG']['MSC']['noItemsInFavorites'];
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
        return true;
    }
}
