<?php

namespace Isotope\Frontend\ProductCollectionAction;

use Isotope\Interfaces\IsotopeProductCollection;

abstract class AbstractButton implements ProductCollectionActionInterface
{
    /**
     * {@inheritdoc}
     */
    public function isAvailable(IsotopeProductCollection $collection)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(IsotopeProductCollection $collection)
    {
        return sprintf(
            '<input type="submit" name="button_%s" class="submit %s" value="%s">',
            $this->getName(),
            $this->getName(),
            $this->getLabel($collection)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function handleSubmit(IsotopeProductCollection $collection)
    {
        return '' !== (string) \Input::post('button_' . $this->getName());
    }
}
