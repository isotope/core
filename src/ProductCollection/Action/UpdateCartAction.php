<?php

namespace Isotope\CoreBundle\ProductCollection\Action;

use Isotope\CoreBundle\HttpFoundation\ActionResult;
use Isotope\CoreBundle\ProductCollection\AbstractProductCollection;
use Twig\Environment;

class UpdateCartAction implements ProductCollectionActionInterface
{
    public function __construct(
        private readonly Environment $twig,
    ) {
    }

    public function supports(AbstractProductCollection $collection, array $context): bool
    {
        return true;
    }

    public function render(AbstractProductCollection $collection, array $context): string
    {
        return $this->twig->render('@Contao/isotope/product_collection_action/update_cart.html.twig');
    }

    public function handle(AbstractProductCollection $collection, array $context): ActionResult
    {
        return new ActionResult();
    }
}
