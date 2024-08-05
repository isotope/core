<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\CheckoutStep;

use Isotope\Interfaces\IsotopeCheckoutStep;
use Isotope\Isotope;
use Isotope\Model\ProductCollection;
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
                'sorting' => ProductCollection::getItemsSortingCallable($this->objModule->iso_orderCollectionBy),
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
}
