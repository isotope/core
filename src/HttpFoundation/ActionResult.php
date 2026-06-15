<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\HttpFoundation;

use Isotope\CoreBundle\Twig\TemplateMarkup;

class ActionResult
{
    public function __construct(
        public readonly string|TemplateMarkup|null $message = null,
        public readonly string|null $redirect = null,
        public readonly bool $success = true
    ) {
    }
}
