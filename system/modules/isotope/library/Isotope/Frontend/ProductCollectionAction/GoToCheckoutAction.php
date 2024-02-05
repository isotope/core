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
use Contao\Module;
use Contao\PageModel;
use Isotope\Interfaces\IsotopeProductCollection;

class GoToCheckoutAction extends AbstractButton
{
    /**
     * @var \Module
     */
    private $module;

    /**
     * Constructor.
     */
    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    /**
     * {@inheritdoc}
     */
    public function isAvailable(IsotopeProductCollection $collection)
    {
        return $this->module->iso_checkout_jumpTo > 0
            && !$collection->hasErrors()
            && PageModel::findByPk($this->module->iso_checkout_jumpTo) !== null
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'checkout';
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(IsotopeProductCollection $collection)
    {
        return $GLOBALS['TL_LANG']['MSC']['checkoutBT'];
    }

    /**
     * {@inheritdoc}
     * @return false|void
     */
    public function handleSubmit(IsotopeProductCollection $collection)
    {
        if (!parent::handleSubmit($collection)) {
            return false;
        }

        Controller::redirect($this->getHref());
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return PageModel::findByPk($this->module->iso_checkout_jumpTo)->getFrontendUrl();
    }
}
