<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\HttpFoundation;

use Symfony\Component\HttpFoundation\JsonResponse;

class ActionResult
{
    public function __construct(
        public readonly string|null $message = null,
        public readonly string|null $redirect = null,
        public readonly bool $success = true
    ) {
    }

    public function toResponse(): JsonResponse
    {
        return new JsonResponse([
            'success' => $this->success,
            'message' => $this->message,
            'redirect' => $this->redirect,
        ], 200, [
            'Content-Type' => 'application/isotope-action+json'
        ]);
    }
}
