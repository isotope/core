<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Document;

use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\EventDispatcher\TransactionEventDispatcher;
use Isotope\CoreBundle\ProductCollection\Order\Order;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Event\DocumentNumberTransactionEvent;
use Symfony\Component\String\UnicodeString;

/**
 * @internal
 */
class DocumentNumberGenerator
{
    public function __construct(
        private readonly TransactionEventDispatcher $transactionDispatcher,
        private readonly Connection $connection,
    ) {
    }

    public function generatePreview(NumberFormat $format): string
    {
        return $format->getPrefix().str_repeat('X', $format->getLength());
    }

    public function generateForOrder(StoreContext $context): string
    {
        return $this->transactionDispatcher->dispatch(
            new DocumentNumberTransactionEvent($context->getOrderNumberFormat(), DocumentNumberTransactionEvent::TYPE_ORDER, $context->getStoreId()),
            $this->generateOrderNumber(...),
        );
    }

    public function generateForDocument(NumberFormat $format): string
    {
        return $this->transactionDispatcher->dispatch(
            new DocumentNumberTransactionEvent($format, DocumentNumberTransactionEvent::TYPE_DOCUMENT),
            $this->generateDocumentNumber(...),
        );
    }

    private function generateOrderNumber(DocumentNumberTransactionEvent $event): string
    {
        $prefix = $event->getNumberFormat()->getPrefix();

        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select('document_number')
            ->from('tl_iso_product_collection')
            ->where('type=:type')
            ->andWhere('store_id=:storeId')
            ->setParameter('type', Order::getType())
            ->setParameter('storeId', $event->getStoreId())
            ->orderBy('document_number', 'DESC')
        ;

        if ('' !== $prefix) {
            $qb
                ->andWhere('document_number LIKE :prefix')
                ->setParameter('prefix', $prefix.'%')
            ;
        }

        return $this->pad($qb->fetchOne(), $event->getNumberFormat());
    }

    private function generateDocumentNumber(DocumentNumberTransactionEvent $event): string
    {
        $prefix = $event->getNumberFormat()->getPrefix();

        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select('document_number')
            ->from('tl_iso_order_document')
            ->orderBy('document_number', 'DESC')
        ;

        if ('' !== $prefix) {
            $qb
                ->andWhere('document_number LIKE :prefix')
                ->setParameter('prefix', $prefix.'%')
            ;
        }

        return $this->pad($qb->fetchOne(), $event->getNumberFormat());
    }

    private function pad(string|false $result, NumberFormat $format): string
    {
        if (!\is_string($result)) {
            $max = 0;
        } else {
            $max = (int) substr($result, (new UnicodeString($format->getPrefix()))->length());
        }

        return $format->getPrefix().str_pad((string) ($max + 1), $format->getLength(), '0', STR_PAD_LEFT);
    }
}
