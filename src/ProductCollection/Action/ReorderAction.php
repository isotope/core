<?php

namespace Isotope\CoreBundle\ProductCollection\Action;

use Contao\System;
use Isotope\CoreBundle\HttpFoundation\ActionResult;
use Isotope\Haste\Util\Url;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Twig\Environment;

class ReorderAction implements ProductCollectionActionInterface
{
    public function __construct(
        private readonly Environment $twig,
    ) {
    }

    public function supports(IsotopeProductCollection $collection, array $context): bool
    {
        return true;
    }

    public function render(IsotopeProductCollection $collection, array $context): string
    {
        return $this->twig->render('@Contao/isotope/product_collection_action/reorder.html.twig');
    }

    public function handle(IsotopeProductCollection $collection, array $context): ActionResult
    {
        Isotope::getCart()?->copyItemsFrom($collection);

        $redirect = null;

        if (($context['data']['iso_cart_jumpTo'] ?? null) > 0) {
            $redirect = Url::addQueryString('continue=' . base64_encode(System::getReferer()), $context['data']['iso_cart_jumpTo']);
        }

        return new ActionResult(
            $GLOBALS['TL_LANG']['MSC']['reorderConfirmation'],
            $redirect,
        );
    }
}
