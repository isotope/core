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
use Contao\StringUtil;
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
            StringUtil::specialchars($this->getHref()),
            StringUtil::specialchars($this->getName()),
            $this->getLabel($collection)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function handleSubmit(IsotopeProductCollection $collection)
    {
        Controller::redirect($this->getHref());
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
