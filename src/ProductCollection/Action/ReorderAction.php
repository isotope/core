<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Action;

use Contao\System;
use Isotope\CoreBundle\HttpFoundation\ActionResult;
use Isotope\CoreBundle\ProductCollection\AbstractProductCollection;
use Isotope\CoreBundle\Store\CartManager;
use Isotope\CoreBundle\Store\StoreManager;
use Isotope\Haste\Util\Url;
use Twig\Environment;

class ReorderAction implements ProductCollectionActionInterface
{
    public function __construct(
        private readonly StoreManager $storeManager,
        private readonly CartManager $cartManager,
        private readonly Environment $twig,
    ) {
    }

    public function supports(AbstractProductCollection $collection, array $context): bool
    {
        return true;
    }

    public function render(AbstractProductCollection $collection, array $context): string
    {
        return $this->twig->render('@Contao/isotope/product_collection_action/reorder.html.twig');
    }

    public function handle(AbstractProductCollection $collection, array $context): ActionResult
    {
        $storeContext = $this->storeManager->getCurrentContext();
        $cart = $storeContext->getCart();

        $this->cartManager->add(
            $cart,
            $collection->getLineItems()->products()->clone()->toArray(),
            $storeContext,
        );

        $redirect = null;

        if (($context['data']['iso_cart_jumpTo'] ?? null) > 0) {
            $redirect = Url::addQueryString('continue='.base64_encode(System::getReferer()), $context['data']['iso_cart_jumpTo']);
        }

        return new ActionResult(
            $GLOBALS['TL_LANG']['MSC']['reorderConfirmation'],
            $redirect,
        );
    }
}
