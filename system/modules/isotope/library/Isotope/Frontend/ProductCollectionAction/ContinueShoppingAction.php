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
use Contao\Module;
use Isotope\Interfaces\IsotopeProductCollection;

class  ContinueShoppingAction extends AbstractLink
{
    /**
     * @var \Module
     */
    private $module;

    /**
     * Constructor.
     *
     * @param Module $module
     */
    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    /**
     * @inheritDoc
     */
    public function isAvailable(IsotopeProductCollection $collection)
    {
        return $this->module->iso_continueShopping && Input::get('continue') != '';
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(IsotopeProductCollection $collection)
    {
        return $GLOBALS['TL_LANG']['MSC']['continueShoppingBT'];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'continue';
    }

    /**
     * Gets the link href.
     *
     * @return string
     */
    public function getHref()
    {
        return ampersand(base64_decode(Input::get('continue', true)));
    }
}
