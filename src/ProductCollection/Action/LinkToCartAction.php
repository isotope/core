<?php

namespace Isotope\CoreBundle\ProductCollection\Action;

use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\PageModel;
use Isotope\CoreBundle\HttpFoundation\ActionResult;
use Isotope\CoreBundle\ProductCollection\AbstractProductCollection;
use Isotope\Interfaces\IsotopeProductCollection;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

class  LinkToCartAction implements ProductCollectionActionInterface
{
    public function __construct(
        private readonly Environment $twig,
        private readonly ContentUrlGenerator $urlGenerator,
    ) {
    }

    public function supports(AbstractProductCollection $collection, array $context): bool
    {
        return null !== $this->getPageModel($context);
    }

    public function render(AbstractProductCollection $collection, array $context): string
    {
        return $this->twig->render('@Contao/isotope/product_collection_action/link_to_cart.html.twig', [
            'href' => $this->urlGenerator->generate($this->getPageModel($context)),
        ]);
    }

    public function handle(AbstractProductCollection $collection, array $context): ActionResult
    {
        return new ActionResult(null, $this->urlGenerator->generate($this->getPageModel($context)));
    }

    private function getPageModel(array $context): PageModel|null
    {
        if (!isset($context['data']['iso_cart_jumpTo'])) {
            return null;
        }

        return PageModel::findById($context['data']['iso_cart_jumpTo']);
    }
}
