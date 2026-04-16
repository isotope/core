<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Order;

use Symfony\Component\HttpFoundation\Response;

final class CreateOrderResult
{
    private Response|null $response = null;

    public function __construct(
        private readonly int $collectionId,
        private readonly string $identifier,
    ) {
    }

    public function getCollectionId(): int
    {
        return $this->collectionId;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }

    public function setResponse(Response|null $response): self
    {
        $this->response = $response;

        return $this;
    }
}
