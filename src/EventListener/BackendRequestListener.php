<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener;

use Contao\CoreBundle\Routing\ScopeMatcher;
use Isotope\CoreBundle\Controller\Backend\ShopConfigController;
use Symfony\Component\Asset\Packages;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsEventListener]
class BackendRequestListener
{
    public function __construct(
        private readonly ScopeMatcher $scopeMatcher,
        private readonly Packages $packages,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        if (!$this->scopeMatcher->isBackendMainRequest($event)) {
            return;
        }

        $GLOBALS['TL_JAVASCRIPT'][] = $this->packages->getUrl('backend.js', 'isotope_core');
        $GLOBALS['TL_CSS'][] = $this->packages->getUrl('backend.css', 'isotope_core');

        $request = $event->getRequest();

        if ('iso_setup' === $request->query->get('do')) {
            $query = $request->query->all();
            unset($query['do']);

            $event->setResponse(new RedirectResponse($this->urlGenerator->generate(ShopConfigController::class, $query), Response::HTTP_SEE_OTHER));
        }
    }
}
