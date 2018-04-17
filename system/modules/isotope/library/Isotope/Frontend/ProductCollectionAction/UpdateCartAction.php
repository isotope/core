<?php

namespace Isotope\Frontend\ProductCollectionAction;

use Isotope\Interfaces\IsotopeProductCollection;

class UpdateCartAction extends AbstractButton
{

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'update';
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(IsotopeProductCollection $collection)
    {
        return $GLOBALS['TL_LANG']['MSC']['updateCartBT'];
    }
}
