<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store;

use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\Document\DocumentInterface;
use Isotope\CoreBundle\Model\OrderDocumentModel;
use Isotope\CoreBundle\ProductCollection\Order\Order;
use Isotope\CoreBundle\Store\Document\DocumentNumberGenerator;
use Isotope\CoreBundle\Store\Document\OrderDocument;
use Isotope\CoreBundle\Store\Document\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

class DocumentManager
{
    public function __construct(
        private readonly Connection $connection,
        private readonly DocumentNumberGenerator $documentNumberGenerator,
        private readonly Storage $storage,
    ) {
    }

    public function preview(Order $order, DocumentInterface $document): Response
    {
        $file = $document->createFile($order, $this->documentNumberGenerator->generatePreview($document->getNumberFormat()), new \DateTimeImmutable());

        return $file->getResponse();
    }

    public function create(Order $order, DocumentInterface $document): OrderDocument
    {
        $createdAt = new \DateTimeImmutable();

        $model = new OrderDocumentModel();
        $model->pid = $order->getId();
        $model->uniqid = Uuid::v4()->toString();
        $model->tstamp = (int) $createdAt->format('U');
        $model->type = $document->getId();

        try {
            $this->connection->executeStatement('LOCK TABLES tl_iso_order_document WRITE');

            $model->document_number = $this->documentNumberGenerator->generateForDocument($document->getNumberFormat());
            $model->save();
        } finally {
            $this->connection->executeStatement('UNLOCK TABLES');
        }

        $file = $document->createFile($order, $model->document_number, $createdAt);

        $model->file_id = $this->storage->store($file->getContents());
        $model->file_name = $file->getFilename();
        $model->mime_type = $file->getContentType();

        $model->save();

        return $file;
    }

    public function find(string $uuid): OrderDocument|null
    {
        if (
            !($model = OrderDocumentModel::findOneBy('uniqid', $uuid))
            || !($contents = $this->storage->retrieve((string) $model->file_id))
        ) {
            return null;
        }

        return new OrderDocument(
            (string) $model->document_number,
            \DateTimeImmutable::createFromFormat('U', (string) $model->tstamp),
            (string) $model->file_name,
            (string) $model->mime_type,
            $contents,
        );
    }
}
