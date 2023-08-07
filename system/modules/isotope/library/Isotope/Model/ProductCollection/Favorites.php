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

use Contao\FrontendUser;
use Contao\PageModel;
use Isotope\Isotope;
use Isotope\Model\ProductCollection;

class Favorites extends ProductCollection
{
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
        if (true !== FE_USER_LOGGED_IN) {
            return null;
        }

        /** @var PageModel $objPage */
        global $objPage;

        if ('FE' !== TL_MODE || null === $objPage || 0 === (int) $objPage->rootId) {
            return null;
        }

        /** @var PageModel|\stdClass $rootPage */
        $rootPage = PageModel::findByPk($objPage->rootId);
        $storeId  = (int) $rootPage->iso_store_id;

        $collection = static::findOneBy(
            array('tl_iso_product_collection.member=?', 'store_id=?'),
            array(FrontendUser::getInstance()->id, $storeId)
        );

        // Create new collection
        if (null === $collection) {
            $collection = new static();

            // Can't call the individual rows here, it would trigger markModified and a save()
            $collection->setRow(array_merge($collection->row(), array(
                'member'    => FrontendUser::getInstance()->id,
                'config_id' => Isotope::getConfig()->id,
                'store_id'  => $storeId,
            )));
        }

        return $collection;
    }
}
