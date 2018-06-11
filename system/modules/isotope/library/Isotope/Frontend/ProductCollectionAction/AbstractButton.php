<?php

namespace Isotope\Frontend\ProductCollectionAction;

use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Template;

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
        /** @var Template|\stdClass $objTemplate */
        $objTemplate = new Template('iso_button');

        $objTemplate->name    = 'button_' . $this->getName();
        $objTemplate->classes = $this->getName();
        $objTemplate->label   = $this->getLabel($collection);

        return $objTemplate->parse();
    }

    /**
     * {@inheritdoc}
     */
    public function handleSubmit(IsotopeProductCollection $collection)
    {
        return '' !== (string) \Input::post('button_' . $this->getName());
    }
}
