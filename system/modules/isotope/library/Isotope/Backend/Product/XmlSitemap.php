<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2008-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Backend\Product;



class XmlSitemap extends \Backend
{

    /**
	 * Schedule an XML sitemap update
	 * @param \DataContainer
	 */
	public function scheduleUpdate($dc)
	{
		// Return if there is no ID
		if (!$dc->id) {
			return;
		}

		// Store the ID in the session
		$session = \Session::getInstance()->get('iso_product_updater');
		$session[] = $dc->id;
		\Session::getInstance()->set('iso_product_updater', array_unique($session));
	}

    /**
     * Check for modified products and update the XML files if necessary
     */
    public function generate()
    {
        $session = $this->Session->get('iso_product_updater');

        if (!is_array($session) || empty($session)) {
            return;
        }

        $objAutomator = new \Automator();
        $objAutomator->generateSitemap();

        $this->Session->set('iso_product_updater', null);
    }
}
