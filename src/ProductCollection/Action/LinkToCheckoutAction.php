<?php

namespace Isotope\CoreBundle\ProductCollection\Action;

use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\PageModel;
use Isotope\CoreBundle\HttpFoundation\ActionResult;
use Isotope\Interfaces\IsotopeProductCollection;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

class  LinkToCheckoutAction implements ProductCollectionActionInterface
{
    public function __construct(
        private readonly Environment $twig,
        private readonly ContentUrlGenerator $urlGenerator,
    ) {
    }

    public function supports(IsotopeProductCollection $collection, array $context): bool
    {
        return null !== $this->getPageModel($context);
    }

    public function render(IsotopeProductCollection $collection, array $context): string
    {
        return $this->twig->render('@Contao/isotope/product_collection_action/link_to_checkout.html.twig', [
            'href' => $this->urlGenerator->generate($this->getPageModel($context)),
        ]);
    }

    public function handle(IsotopeProductCollection $collection, array $context): ActionResult
    {
        return new ActionResult(null, $this->urlGenerator->generate($this->getPageModel($context)));
    }

    private function getPageModel(array $context): PageModel|null
    {
        if (!isset($context['data']['iso_checkout_jumpTo'])) {
            return null;
        }

        return PageModel::findById($context['data']['iso_checkout_jumpTo']);
    }
}
