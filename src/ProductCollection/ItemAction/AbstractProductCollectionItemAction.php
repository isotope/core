<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\ItemAction;

use Twig\Environment;

abstract class AbstractProductCollectionItemAction implements ProductCollectionItemActionInterface
{
    public function __construct(
        private readonly Environment $twig,
    ) {
    }

    public function renderView(string $type, int $itemId, array $context = []): string
    {
        return $this->twig->render("@Contao/isotope/product_collection_item_action/$type.html.twig", [
            'type' => $type,
            'name' => $type,
            'value' => $itemId,
            ...$context,
        ]);
    }
}
