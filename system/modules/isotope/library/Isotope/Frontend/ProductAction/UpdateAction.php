<?php

namespace Isotope\Frontend\ProductAction;

use Isotope\Interfaces\IsotopeProduct;

class UpdateAction extends AbstractButton
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
    public function getLabel(IsotopeProduct $product = null)
    {
        return $GLOBALS['TL_LANG']['MSC']['buttonLabel']['update'];
    }

    /**
     * {@inheritdoc}
     */
    public function handleSubmit(IsotopeProduct $product, array $config = [])
    {
        // does nothing, the page will reload
    }
}
