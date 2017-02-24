<?php

namespace Isotope\Frontend\ProductCollectionAction;

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

        $collection->uniqid = $this->isShared($collection) ? null : uniqid('', true);
        $collection->save();

        \Controller::reload();

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
