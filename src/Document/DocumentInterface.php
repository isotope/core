<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Document;

use Isotope\CoreBundle\ProductCollection\Order\Order;
use Isotope\CoreBundle\Store\Document\NumberFormat;
use Isotope\CoreBundle\Store\Document\OrderDocument;

interface DocumentInterface
{
    public function getId(): int;

    public function getNumberFormat(): NumberFormat;

    public function createFile(Order $order, string $documentNumber, \DateTimeInterface $createdAt): OrderDocument;
}
