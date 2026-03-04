<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Twig;

use Twig\Environment;

readonly class TemplateMarkupFactory
{
    public function __construct(private Environment $twig)
    {
    }

    public function create(string $template, array $context): TemplateMarkup
    {
        return new TemplateMarkup($this->twig, $template, $context);
    }
}
