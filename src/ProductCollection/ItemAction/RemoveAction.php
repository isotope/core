<?php

namespace Isotope\CoreBundle\ProductCollection\ItemAction;

use Contao\System;
use Isotope\CoreBundle\HttpFoundation\ActionResult;
use Isotope\Haste\Util\Url;
use Isotope\Model\ProductCollectionItem;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

class RemoveAction implements ProductCollectionItemActionInterface
{
    public function __construct(
        private readonly Environment $twig,
    ) {
    }

    public function supports(ProductCollectionItem $item, array $context): bool
    {
        return !$item->getCollection()->isLocked();
    }

    public function render(ProductCollectionItem $item, array $context): string
    {
        return $this->twig->render(
            '@Contao/isotope/product_collection_item_action/remove.html.twig',
            [
                'item' => $item,
                'href' => Url::addQueryString('_action=remove&_item='.$item->id),
            ],
        );
    }

    public function handle(ProductCollectionItem $item, array $context, bool $isAjax = true): ActionResult
    {
        $item->getCollection()->deleteItem($item);

        return new ActionResult(null, $isAjax ? null : Url::removeQueryString(['remove']));
    }
}
