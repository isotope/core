<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\ItemAction;

use Isotope\CoreBundle\HttpFoundation\ActionResult;
use Isotope\CoreBundle\ProductCollection\AbstractProductCollection;
use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\Store\CartManager;
use Isotope\CoreBundle\Store\StoreManager;
use Isotope\Haste\Util\Url;
use Twig\Environment;

class RemoveFromCartAction implements ProductCollectionItemActionInterface
{
    public function __construct(
        private readonly StoreManager $storeManager,
        private readonly CartManager $cartManager,
        private readonly Environment $twig,
    ) {
    }

    public function supports(AbstractProductCollection $collection, LineItem $item, array $context): bool
    {
        return $collection instanceof Cart;
    }

    public function render(AbstractProductCollection $collection, LineItem $item, array $context): string
    {
        return $this->twig->render('@Contao/isotope/product_collection_item_action/remove_from_cart.html.twig', [
            'item' => $item,
            'href' => Url::addQueryString('_action=remove_from_cart&_item='.$item->getId()),
        ]);
    }

    public function handle(AbstractProductCollection $collection, LineItem $item, array $context, bool $isAjax = true): ActionResult
    {
        if (!$collection instanceof Cart) {
            throw new \RuntimeException(__METHOD__.' expects a Cart');
        }

        $this->cartManager->remove($collection, $item->getId(), $this->storeManager->getCurrentContext());

        return new ActionResult(null, $isAjax ? null : Url::removeQueryString(['remove']));
    }
}
