<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\CheckoutStep;

use Isotope\Isotope;
use Isotope\Interfaces\IsotopeCheckoutStep;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\Document;


class OrderProducts extends CheckoutStep implements IsotopeCheckoutStep
{

    /**
     * Returns true to enable the module
     * @return  bool
     */
    public function isAvailable()
    {
        return true;
    }

    /**
     * Generate the checkout step
     * @return  string
     */
    public function generate()
    {
        $objTemplate = new \Isotope\Template($this->objModule->iso_collectionTpl);

        Isotope::getCart()->addToTemplate(
            $objTemplate,
            array(
                'gallery'   => $this->objModule->iso_gallery,
                'sorting'   => Isotope::getCart()->getItemsSortingCallable($this->objModule->iso_orderCollectionBy),
            )
        );

        return $objTemplate->parse();
    }

    /**
     * Cart product view does not have review information
     * @return  string
     */
    public function review()
    {
        return '';
    }

    /**
     * Return array of tokens for notification
     * @param   IsotopeProductCollection
     * @return  array
     */
    public function getNotificationTokens(IsotopeProductCollection $objCollection)
    {
        return array();
    }
}
