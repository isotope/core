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
use Contao\PageModel;
use Haste\Generator\RowClass;
use Haste\Util\Url;
use Isotope\CompatibilityHelper;
use Isotope\Isotope;
use Isotope\Model\ProductCollection\Wishlist;
use Isotope\Template;

/**
 * @property int $iso_cart_jumpTo
 */
class WishlistManager extends Module
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_iso_wishlistmanager';

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
        $items = [];

        /** @var Wishlist[] $wishlists */
        $wishlists = Wishlist::findBy(
            [
                'tl_iso_product_collection.member=?',
                /*'config_id IN (' . implode(',', array_map('intval', $this->iso_config_ids)) . ')',*/
            ],
            [\FrontendUser::getInstance()->id]
        );

        if (null === $wishlists) {
            $this->Template          = new Template('mod_message');
            $this->Template->type    = 'empty';
            $this->Template->message = $GLOBALS['TL_LANG']['ERR']['emptyWishlists'];

            return;
        }

        if ('wishlists'.$this->id === Input::post('FORM_SUBMIT')) {
            $names = Input::post('name');
            $new   = (string) Input::post('new');
            $delete = (int) Input::post('delete');

            if ($delete > 0) {
                $wishlist = Wishlist::findByIdForCurrentUser($delete);

                if ($wishlist instanceof Wishlist) {
                    $wishlist->delete();
                }
            } else {
                if (\is_array($names) && 0 !== \count($names)) {
                    foreach ($wishlists as $wishlist) {
                        if (isset($names[$wishlist->id])) {
                            $wishlist->setName($names[$wishlist->id]);
                            $wishlist->save();
                        }
                    }
                }

                if ('' !== $new) {
                    $wishlist = Wishlist::createForCurrentUser();
                    $wishlist->setName($new);
                    $wishlist->save();
                }
            }

            Controller::reload();
        }

        $url = $this->getJumpTo()->getFrontendUrl();

        foreach ($wishlists as $wishlist) {
            Isotope::setConfig($wishlist->getConfig());

            $items[] = [
                'collection' => $wishlist,
                'id'         => $wishlist->id,
                'name'       => $wishlist->getName(),
                'member'     => $wishlist->getRelated('member'),
                'href'       => Url::addQueryString('id=' . $wishlist->id, $url)
            ];
        }

        RowClass::withKey('class')->addFirstLast()->addEvenOdd()->applyTo($items);

        $this->Template->id = $this->id;
        $this->Template->items = $items;
    }


    private function getJumpTo()
    {
        if ($this->jumpTo > 0 && ($jumpTo = PageModel::findByPk($this->jumpTo)) !== null) {
            return $jumpTo;
        }

        global $objPage;

        return $objPage;
    }
}
