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

use Contao\Input;
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
        $objTemplate = new Template('iso_action');

        $objTemplate->name = 'button_' . $this->getName();
        $objTemplate->classes = '';
        $objTemplate->label = $this->getLabel($collection);

        return $objTemplate->parse();
    }

    /**
     * {@inheritdoc}
     */
    public function handleSubmit(IsotopeProductCollection $collection)
    {
        return '' !== (string) Input::post('button_' . $this->getName());
    }
}
