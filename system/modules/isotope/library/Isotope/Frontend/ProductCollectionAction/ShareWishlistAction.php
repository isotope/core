<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Frontend\ProductCollectionAction;

use Contao\Controller;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\ProductCollection\Wishlist;

class ShareWishlistAction extends AbstractButton
{
    /**
     * {@inheritdoc}
     */
    public function isAvailable(IsotopeProductCollection $collection)
    {
        return $collection instanceof Wishlist;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'share';
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(IsotopeProductCollection $collection)
    {
        $label = $this->isShared($collection) ? 'unshare_wishlist' : 'share_wishlist';

        return $GLOBALS['TL_LANG']['MSC']['buttonLabel'][$label];
    }

    /**
     * {@inheritdoc}
     */
    public function handleSubmit(IsotopeProductCollection $collection)
    {
        if (!parent::handleSubmit($collection)) {
            return false;
        }

        $published = $this->isShared($collection);

        $collection->uniqid = $published ? null : uniqid('', true);
        $collection->date_shipped = $published ? null : time();
        $collection->save();

        Controller::reload();

        return true;
    }

    /**
     * @param IsotopeProductCollection $collection
     *
     * @return bool
     */
    private function isShared(IsotopeProductCollection $collection)
    {
        return $collection->getUniqueId() !== null;
    }
}
