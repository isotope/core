<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Document;

use Isotope\CoreBundle\Twig\TemplateMarkup;

/**
 * @experimental
 */
interface DocumentViewerInterface
{
    public function getViewerMarkup(string $documentUrl): TemplateMarkup|null;
}
