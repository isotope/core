<?php

namespace Isotope\CoreBundle\ProductCollection\Action;

use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\PageModel;
use Isotope\CoreBundle\HttpFoundation\ActionResult;
use Isotope\CoreBundle\ProductCollection\AbstractProductCollection;
use Twig\Environment;

class SubmitToCheckoutAction implements ProductCollectionActionInterface
{
    public function __construct(
        private readonly Environment $twig,
        private readonly ContentUrlGenerator $urlGenerator,
    ) {
    }

    public function supports(AbstractProductCollection $collection, array $context): bool
    {
        return !$collection->hasErrors() && null !== $this->getPageModel($context);
    }

    public function render(AbstractProductCollection $collection, array $context): string
    {
        return $this->twig->render('@Contao/isotope/product_collection_action/submit_to_checkout.html.twig');
    }

    public function handle(AbstractProductCollection $collection, array $context): ActionResult
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
