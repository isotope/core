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
use Contao\Date;
use Contao\PageModel;
use Haste\Generator\RowClass;
use Haste\Util\Url;
use Isotope\Isotope;
use Isotope\Model\ProductCollection\Wishlist;
use Isotope\Template;

class WishlistViewer extends Module
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_iso_wishlistviewer';

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

        if (0 === \count($this->iso_config_ids)) {
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
                "uniqid IS NOT NULL",
                "date_shipped IS NOT NULL",
                /*'config_id IN (' . implode(',', array_map('intval', $this->iso_config_ids)) . ')'*/
            ],
            [],
            [
                'order' => 'date_shipped DESC'
            ]
        );

        if (null === $wishlists) {
            $this->Template          = new Template('mod_message');
            $this->Template->type    = 'empty';
            $this->Template->message = $GLOBALS['TL_LANG']['ERR']['emptyWishlists'];

            return;
        }

        $url = $this->getJumpTo()->getFrontendUrl();

        foreach ($wishlists as $wishlist) {
            Isotope::setConfig($wishlist->getConfig());

            $items[] = [
                'collection' => $wishlist,
                'id'         => $wishlist->id,
                'name'       => $wishlist->getName(),
                'published'  => Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $wishlist->date_shipped),
                'member'     => $wishlist->getRelated('member'),
                'href'       => Url::addQueryString('uid=' . $wishlist->uniqid, $url),
                'model'      => $wishlist,
            ];
        }

        RowClass::withKey('class')->addFirstLast()->addEvenOdd()->applyTo($items);

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
