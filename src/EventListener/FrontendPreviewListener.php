<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener;

use Contao\CoreBundle\Event\PreviewUrlCreateEvent;
use Contao\Input;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener('contao.preview_url_create')]
class FrontendPreviewListener
{
    public function __invoke(PreviewUrlCreateEvent $event): void
    {
        if ('iso_products' !== $event->getKey() || 'tl_iso_product_category' !== Input::get('table') || !Input::get('page_id')) {
            return;
        }

        $event->setQuery('page='.Input::get('page_id'));
    }
}
