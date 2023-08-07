<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Frontend\ProductAction;

use Contao\Controller;
use Contao\Environment;
use Contao\Input;
use Haste\Util\Url;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Message;
use Isotope\Model\ProductCollection\Wishlist;

class WishlistAction extends AbstractButton
{

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'add_to_wishlist';
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(IsotopeProduct $product = null)
    {
        return $GLOBALS['TL_LANG']['MSC']['buttonLabel']['add_to_wishlist'];
    }

    /**
     * {@inheritdoc}
     */
    public function isAvailable(IsotopeProduct $product, array $config = [])
    {
        return true === FE_USER_LOGGED_IN;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(IsotopeProduct $product, array $config = [])
    {
        $wishlists = Wishlist::findAllForCurrentUser();
        $options   = [['label' => $GLOBALS['TL_LANG']['MSC']['defaultWishlistLabel'], 'value' => '']];

        if (null !== $wishlists) {
            foreach ($wishlists as $wishlist) {
                $options[] = ['label' => $wishlist->getName(), 'value' => $wishlist->id];
            }
        }

        $widget = new \FormSelectMenu(
            array(
                'id' => $this->getName() . '_option',
                'name' => $this->getName() . '_option',
                'options' => $options,
            )
        );

        return $widget->parse() . parent::generate($product, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function handleSubmit(IsotopeProduct $product, array $config = [])
    {
        if (!isset($_POST[$this->getName()])) {
            return false;
        }

        $wishlistId = (int) Input::post('add_to_wishlist_option');

        if (0 === $wishlistId || ($wishlist = Wishlist::findByIdForCurrentUser($wishlistId)) === null) {
            $wishlist = Wishlist::createForCurrentUser();
        }

        if (!$wishlist->addProduct($product, 1, $config)) {
            return false;
        }

        Message::addConfirmation($GLOBALS['TL_LANG']['MSC']['addedToWishlist']);

        if (!$config['module']->iso_wishlistJumpTo) {
            Controller::reload();
        }

        Controller::redirect(
            Url::addQueryString(
                'id=' . $wishlist->id . '&amp;continue=' . base64_encode(Environment::get('request')),
                $config['module']->iso_wishlistJumpTo
            )
        );

        return true;
    }
}
