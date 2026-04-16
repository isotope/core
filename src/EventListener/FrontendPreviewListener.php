<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener;

use Contao\CoreBundle\DataContainer\DcaUrlAnalyzer;
use Contao\CoreBundle\Event\PreviewUrlConvertEvent;
use Contao\CoreBundle\Event\PreviewUrlCreateEvent;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\Input;
use Isotope\CoreBundle\Model\ProductModel;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FrontendPreviewListener
{
    public function __construct(
        private readonly DcaUrlAnalyzer $dcaUrlAnalyzer,
        private readonly RequestStack $requestStack,
        private readonly ContentUrlGenerator $urlGenerator,
    ) {
    }

    #[AsEventListener]
    public function createPreviewUrl(PreviewUrlCreateEvent $event): void
    {
        if ('iso_products' !== $event->getKey()) {
            return;
        }

        if ('tl_iso_product_category' === Input::get('table')) {
            $event->setQuery('page='.Input::get('id'));

            return;
        }

        [$table, $id] = $this->dcaUrlAnalyzer->getCurrentTableId();

        if ($id && ('tl_iso_product' === $table || null === $table)) {
            $event->setQuery('product='.$id);
        }
    }

    #[AsEventListener]
    public function convertPreviewUrl(PreviewUrlConvertEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request?->query->has('product')) {
            return;
        }

        $event->setUrl($this->urlGenerator->generate(ProductModel::findById($request?->query->get('product')), [], UrlGeneratorInterface::ABSOLUTE_URL));
    }
}
