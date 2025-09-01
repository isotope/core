<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener;

use Contao\CoreBundle\Routing\ScopeMatcher;
use Symfony\Component\Asset\Packages;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;

#[AsEventListener]
class BackendAssetsListener
{
    public function __construct(
        private readonly ScopeMatcher $scopeMatcher,
        private readonly Packages $packages,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        if ($this->scopeMatcher->isBackendMainRequest($event)) {
            $GLOBALS['TL_JAVASCRIPT'][] = $this->packages->getUrl('backend.js', 'isotope_core');
            $GLOBALS['TL_CSS'][] = $this->packages->getUrl('backend.css', 'isotope_core');
        }
    }
}
