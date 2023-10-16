<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\ProductCollection;

use Isotope\CompatibilityHelper;
use Contao\FrontendUser;
use Contao\PageModel;
use Isotope\Model\ProductCollection;

class Wishlist extends ProductCollection
{
    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @inheritDoc
     */
    public function getSurcharges()
    {
        return [];
    }

    public static function createForCurrentUser()
    {
        $wishlist = new static();
        $wishlist->setName($GLOBALS['TL_LANG']['MSC']['defaultWishlistName'] ?: 'Wishlist 1');
        $wishlist->member = FrontendUser::getInstance()->id;
        $wishlist->store_id = (int) static::getCurrentStoreId();

        $wishlist->save();

        return $wishlist;
    }

    public static function findByIdForCurrentUser($id)
    {
        if (!CompatibilityHelper::isFrontend() || true !== FE_USER_LOGGED_IN || ($storeId = static::getCurrentStoreId()) === null) {
            return null;
        }

        return static::findOneBy(
            array('id=?', 'tl_iso_product_collection.member=?', 'store_id=?'),
            array((int) $id, FrontendUser::getInstance()->id, $storeId)
        );
    }

    /**
     * @return Wishlist[]
     */
    public static function findAllForCurrentUser()
    {
        if (!CompatibilityHelper::isFrontend() || true !== FE_USER_LOGGED_IN || ($storeId = static::getCurrentStoreId()) === null) {
            return null;
        }

        return static::findBy(
            array('tl_iso_product_collection.member=?', 'store_id=?'),
            array(FrontendUser::getInstance()->id, $storeId)
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function generateUniqueId()
    {
        return $this->arrData['uniqid'];
    }

    private static function getCurrentStoreId()
    {
        if (!CompatibilityHelper::isFrontend()) {
            return null;
        }

        /** @var PageModel $objPage */
        global $objPage;

        if (null === $objPage || 0 === (int) $objPage->rootId) {
            return null;
        }

        /** @var PageModel|\stdClass $rootPage */
        $rootPage = PageModel::findByPk($objPage->rootId);

        return (int) $rootPage->iso_store_id;
    }
}
