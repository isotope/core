<?php

namespace Isotope\Frontend\ProductCollectionAction;

use Isotope\Interfaces\IsotopeProductCollection;

abstract class AbstractLink implements ProductCollectionActionInterface
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
            '<a href="%s" class="submit %s">%s</a>',
            specialchars($this->getHref()),
            specialchars($this->getName()),
            $this->getLabel($collection)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function handleSubmit(IsotopeProductCollection $collection, \Module $module = null)
    {
        \Controller::redirect($this->getHref());
    }

    /**
     * Gets the link label.
     *
     * @param IsotopeProductCollection $collection
     *
     * @return string
     */
    abstract public function getLabel(IsotopeProductCollection $collection);

    /**
     * Gets the link href.
     *
     * @return string
     */
    abstract public function getHref();
}
