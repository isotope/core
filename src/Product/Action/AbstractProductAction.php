<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Action;

use Twig\Environment;

abstract class AbstractProductAction implements ProductActionInterface
{
    public function __construct(
        private readonly Environment $twig,
    ) {
    }

    public function renderView(string $type, array $context = []): string
    {
        return $this->twig->render("@Contao/isotope/product_action/$type.html.twig", [
            'type' => $type,
            ...$context,
        ]);
    }
}
