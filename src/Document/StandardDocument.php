<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Document;

use Isotope\CoreBundle\DependencyInjection\Attribute\AsDocument;
use Isotope\CoreBundle\ModelType\ContainerAwareInterface;
use Isotope\CoreBundle\ModelType\ContainerAwareTrait;
use Isotope\CoreBundle\ProductCollection\Order\Order;
use Isotope\CoreBundle\ProductCollection\ProductCollectionRenderer;
use Isotope\CoreBundle\Store\Document\NumberFormat;
use Isotope\CoreBundle\Store\Document\OrderDocument;

#[AsDocument]
class StandardDocument extends AbstractDocument implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function __construct(
        int $id,
        string $fileTitle,
        NumberFormat $numberFormat,
        private readonly string $template,
    ) {
        parent::__construct($id, $fileTitle, $numberFormat);
    }

    public static function getSubscribedServices(): array
    {
        return [ProductCollectionRenderer::class];
    }

    public function createFile(Order $order, string $documentNumber, \DateTimeInterface $createdAt): OrderDocument
    {
        $contents = $this->get(ProductCollectionRenderer::class)->renderCollection(
            $this->template,
            [
                'created_at' => $createdAt,
                'document_number' => $documentNumber,
                'document_title' => $this->getDocumentTitle(),
                'file_title' => $this->getFileTitle(),
            ],
            $order,
            $order->getContext(),
            $this->getGalleryId(),
        );

        return new OrderDocument($documentNumber, $createdAt, $this->getFileTitle(), 'text/html', $contents);
    }
}
