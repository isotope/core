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

use Contao\Controller;
use Contao\FrontendUser;
use Contao\Input;
use Contao\PageModel;
use Contao\System;
use Isotope\CompatibilityHelper;
use Isotope\Model\Config;
use Isotope\Model\ProductCollection;

class Favorites extends ProductCollection
{
    /**
     * Name of the temporary collection cookie
     */
    private const COOKIE_NAME = 'ISOTOPE_TEMP_FAVORITES';

    /**
     * @inheritDoc
     */
    public function getSurcharges()
    {
        return [];
    }

    /**
     * Find or create favorites list for the current user
     *
     * @return static
     */
    public static function findForCurrentStore()
    {
        /** @var PageModel $objPage */
        global $objPage;

        if (!CompatibilityHelper::isFrontend() || null === $objPage || 0 === (int) $objPage->rootId) {
            return null;
        }

        /** @var PageModel|\stdClass $rootPage */
        $rootPage = PageModel::findByPk($objPage->rootId);

        $time = time();
        $collection = null;
        $cookieHash = null;
        $storeId = (int) $rootPage->iso_store_id;

        if (true === FE_USER_LOGGED_IN) {
            $collection = static::findOneBy(
                array('tl_iso_product_collection.member=?', 'store_id=?'),
                array(FrontendUser::getInstance()->id, $storeId)
            );
        } else {
            $cookieHash = (string) Input::cookie(self::COOKIE_NAME);

            if ('' !== $cookieHash) {
                $collection = static::findOneBy(array('uniqid=?', 'store_id=?'), array($cookieHash, $storeId));
            }

            if (null === $collection) {
                $cookieHash = self::generateCookieId();
            }
        }

        // Create new collection
        if (null === $collection) {
            $config = Config::findByRootPageOrFallback($objPage->rootId);
            $collection = new static();

            // Can't call the individual rows here, it would trigger markModified and a save()
            $collection->setRow(array_merge($collection->row(), array(
                'tstamp'    => $time,
                'member'    => FE_USER_LOGGED_IN === true ? FrontendUser::getInstance()->id : 0,
                'uniqid'    => $cookieHash,
                'config_id' => $config->id,
                'store_id'  => $storeId,
            )));
        }

        $collection->tstamp = $time;

        // Renew the guest cart cookie
        if (!$collection->member && !headers_sent()) {
            System::setCookie(
                self::COOKIE_NAME,
                $collection->uniqid,
                $time + $GLOBALS['TL_CONFIG']['iso_cartTimeout']
            );
        }

        return $collection;
    }

    /**
     * Merge guest collection if necessary
     */
    public function mergeGuestCollection()
    {
        $this->ensureNotLocked();

        $strHash = (string) Input::cookie(self::COOKIE_NAME);

        // Temporary cart available, move to this cart. Must be after creating a new cart!
        if (FE_USER_LOGGED_IN === true && '' !== $strHash && $this->member > 0) {
            $objTemp = static::findOneBy(array('uniqid=?', 'store_id=?'), array($strHash, $this->store_id));

            if (null !== $objTemp) {
                $this->copyItemsFrom($objTemp);
                $objTemp->delete();
            }

            // Delete cookie
            System::setCookie(self::COOKIE_NAME, '', time() - 3600);
            Controller::reload();
        }
    }

    public function save()
    {
        parent::save();

        // Create/renew the guest collection cookie
        if (!$this->member && !headers_sent()) {
            System::setCookie(
                self::COOKIE_NAME,
                $this->uniqid,
                $this->tstamp + $GLOBALS['TL_CONFIG']['iso_cartTimeout']
            );
        }

        return $this;
    }

    private static function generateCookieId()
    {
        if (!function_exists('random_bytes')) {
            return uniqid('', true);
        }

        try {
            return bin2hex(random_bytes(32));
        } catch (\Exception $e) {
            return uniqid('', true);
        }
    }
}
