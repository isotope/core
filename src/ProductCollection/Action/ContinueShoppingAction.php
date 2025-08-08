<?php

namespace Isotope\CoreBundle\ProductCollection\Action;

use Isotope\CoreBundle\HttpFoundation\ActionResult;
use Isotope\Interfaces\IsotopeProductCollection;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

class  ContinueShoppingAction implements ProductCollectionActionInterface
{
    public function __construct(
        private readonly Environment $twig,
    ) {
    }

    public function supports(IsotopeProductCollection $collection, array $context): bool
    {
        return ($context['data']['iso_continueShopping'] ?? null) && null !== $this->getHref($context);
    }

    public function render(IsotopeProductCollection $collection, array $context): string
    {
        return $this->twig->render('@Contao/isotope/product_collection_action/continue_shopping.html.twig', [
            'href' => $this->getHref($context),
        ]);
    }

    public function handle(IsotopeProductCollection $collection, array $context): ActionResult
    {
        return new ActionResult(null, $this->getHref($context));
    }

    private function getHref(array $context): string|null
    {
        $request = $context['app']?->getRequest();

        if (!$request instanceof Request || !$request->query->has('continue')) {
            return null;
        }

        return base64_decode($request->query->get('continue'));
    }
}
