<?php

namespace Isotope\EventListener;

use Contao\CoreBundle\Event\PreviewUrlCreateEvent;

class FrontendPreviewListener
{
    public function onPreviewUrlCreate(PreviewUrlCreateEvent $event)
    {
        if ($event->getKey() !== 'iso_products' || \Input::get('table') !== 'tl_iso_product_category' || !\Input::get('page_id')) {
            return;
        }

        $event->setQuery('page=' . \Input::get('page_id'));
    }
}
