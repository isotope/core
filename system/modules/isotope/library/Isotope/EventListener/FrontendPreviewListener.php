<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\EventListener;

use Contao\CoreBundle\Event\PreviewUrlCreateEvent;
use Contao\Input;

class FrontendPreviewListener
{
    public function onPreviewUrlCreate(PreviewUrlCreateEvent $event)
    {
        if ($event->getKey() !== 'iso_products' || Input::get('table') !== 'tl_iso_product_category' || !Input::get('page_id')) {
            return;
        }

        $event->setQuery('page=' . Input::get('page_id'));
    }
}
