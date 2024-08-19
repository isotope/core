<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\Product;

use Contao\Automator;
use Contao\Backend;
use Contao\DataContainer;
use Contao\System;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;

class XmlSitemap extends Backend
{

    /**
     * Schedule an XML sitemap update
     * @param DataContainer
     */
    public function scheduleUpdate($dc)
    {
        // Return if there is no ID
        if (!$dc->id) {
            return;
        }

        /** @var AttributeBagInterface $sessionBag */
        $sessionBag = System::getContainer()->get('request_stack')->getSession()->getBag('contao_backend');

        // Store the ID in the session
        $session   = $sessionBag->get('iso_product_updater');
        $session[] = $dc->id;
        $sessionBag->set('iso_product_updater', array_unique($session));
    }

    /**
     * Check for modified products and update the XML files if necessary
     */
    public function generate()
    {
        /** @var AttributeBagInterface $sessionBag */
        $sessionBag = System::getContainer()->get('request_stack')->getSession()->getBag('contao_backend');

        $session = $sessionBag->get('iso_product_updater');

        if (!\is_array($session) || empty($session)) {
            return;
        }

        $objAutomator = new Automator();
        $objAutomator->generateSitemap();

        $sessionBag->remove('iso_product_updater');
    }
}
