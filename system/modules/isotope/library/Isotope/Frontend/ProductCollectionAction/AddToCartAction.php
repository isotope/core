<?php

namespace Isotope\Frontend\ProductCollectionAction;

use Isotope\Interfaces\IsotopeProductCollection;

class AddToCartAction extends AbstractButton
{

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'add_to_cart';
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(IsotopeProductCollection $collection)
    {
        return $GLOBALS['TL_LANG']['MSC']['buttonLabel']['add_all_to_cart'];
    }
}
