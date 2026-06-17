<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Action;

use Twig\Environment;

abstract class AbstractProductCollectionAction implements ProductCollectionActionInterface
{
    public function __construct(private readonly Environment $twig)
    {
    }

    public function renderView(string $type, array $context = []): string
    {
        return $this->twig->render("@Contao/isotope/product_collection_action/$type.html.twig", [
            'type' => $type,
            ...$context,
        ]);
    }
}
