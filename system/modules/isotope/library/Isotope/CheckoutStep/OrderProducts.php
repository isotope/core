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
use Isotope\Model\Document;
use Isotope\Template;


class OrderProducts extends CheckoutStep implements IsotopeCheckoutStep
{

    /**
     * Returns true to enable the module
     *
     * @return bool
     */
    public function isAvailable()
    {
        return true;
    }

    /**
     * Generate the checkout step
     *
     * @return string
     */
    public function generate()
    {
        $objTemplate = new Template($this->objModule->iso_collectionTpl);

        Isotope::getCart()->addToTemplate(
            $objTemplate,
            array(
                'gallery' => $this->objModule->iso_gallery,
                'sorting' => Isotope::getCart()->getItemsSortingCallable($this->objModule->iso_orderCollectionBy),
            )
        );

        return $objTemplate->parse();
    }

    /**
     * Cart product view does not have review information
     *
     * @return string
     */
    public function review()
    {
        return '';
    }

    /**
     * Return array of tokens for notification
     *
     * @param IsotopeProductCollection $objCollection
     *
     * @return array
     */
    public function getNotificationTokens(IsotopeProductCollection $objCollection)
    {
        return array();
    }
}
