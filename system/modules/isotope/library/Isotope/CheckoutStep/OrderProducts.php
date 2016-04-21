<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\CheckoutStep;

use Isotope\Interfaces\IsotopeCheckoutStep;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Isotope\Template;


class OrderProducts extends CheckoutStep implements IsotopeCheckoutStep
{
    /**
     * @inheritdoc
     */
    public function isAvailable()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        $objTemplate = new Template($this->objModule->iso_collectionTpl);
        $objOrder = Isotope::getCart()->getDraftOrder();

        $objOrder->addToTemplate(
            $objTemplate,
            array(
                'gallery' => $this->objModule->iso_gallery,
                'sorting' => $objOrder->getItemsSortingCallable($this->objModule->iso_orderCollectionBy),
            )
        );

        return $objTemplate->parse();
    }

    /**
     * Cart product view does not have review information
     *
     * @inheritdoc
     */
    public function review()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getNotificationTokens(IsotopeProductCollection $objCollection)
    {
        return [];
    }
}
