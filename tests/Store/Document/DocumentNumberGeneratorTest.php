<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Store\Document;

use Doctrine\DBAL\DriverManager;
use Isotope\CoreBundle\EventDispatcher\TransactionEventDispatcher;
use Isotope\CoreBundle\ProductCollection\Order\Order;
use Isotope\CoreBundle\Store\Document\DocumentNumberGenerator;
use Isotope\CoreBundle\Store\Document\NumberFormat;
use Isotope\CoreBundle\Test\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class DocumentNumberGeneratorTest extends TestCase
{
    #[DataProvider('numberSequenceProvider')]
    public function testDocumentNumberSequence(string $prefix, int $length, array $numbers, string $expected, array $otherNumbers = []): void
    {
        $connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $connection->executeStatement('CREATE TABLE tl_iso_order_document (document_number VARCHAR(64))');

        foreach ($numbers as $number) {
            $connection->insert('tl_iso_order_document', ['document_number' => $prefix.$number]);
        }

        foreach ($otherNumbers as $number) {
            $connection->insert('tl_iso_order_document', ['document_number' => $number]);
        }

        $generator = new DocumentNumberGenerator(new TransactionEventDispatcher(new EventDispatcher()), $connection);

        $this->assertSame($expected, $generator->generateForDocument(new NumberFormat($prefix, $length)));
    }

    #[DataProvider('numberSequenceProvider')]
    public function testOrderNumberSequence(string $prefix, int $length, array $numbers, string $expected, array $otherNumbers = []): void
    {
        $connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $connection->executeStatement('CREATE TABLE tl_iso_product_collection (type VARCHAR(16), store_id INTEGER, document_number VARCHAR(64))');

        foreach ($numbers as $number) {
            $connection->insert('tl_iso_product_collection', ['type' => Order::getType(), 'store_id' => 0, 'document_number' => $prefix.$number]);
        }

        foreach ($otherNumbers as $number) {
            $connection->insert('tl_iso_product_collection', ['type' => Order::getType(), 'store_id' => 0, 'document_number' => $number]);
        }

        $generator = new DocumentNumberGenerator(new TransactionEventDispatcher(new EventDispatcher()), $connection);
        $context = $this->createStoreContext()->setOrderNumberFormat(new NumberFormat($prefix, $length));

        $this->assertSame($expected, $generator->generateForOrder($context));
    }

    public static function numberSequenceProvider(): iterable
    {
        yield 'normal padding' => ['INV-', 4, ['0009', '0010'], 'INV-0011'];
        yield 'padding overflow' => ['INV-', 4, ['9999', '10000'], 'INV-10001'];
        yield 'changed padding' => ['INV-', 4, ['9', '0002'], 'INV-0010'];
        yield 'Unicode prefix overflow' => ['Ä-', 2, ['99', '100'], 'Ä-101'];
        yield 'no prefix overflow' => ['', 2, ['99', '100'], '101'];
        yield 'literal underscore prefix' => ['INV_', 4, ['0009'], 'INV_0010', ['INVX9999']];
        yield 'literal percent prefix' => ['INV%', 4, ['0009'], 'INV%0010', ['INVX9999']];
        yield 'literal escape character prefix' => ['INV!', 4, ['0009'], 'INV!0010', ['INVX9999']];
        yield 'literal backslash prefix' => ['INV\\', 4, ['0009'], 'INV\\0010', ['INVX9999']];
    }

    public function testDocumentNumberWithUnicodePrefix(): void
    {
        $connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $connection->executeStatement('CREATE TABLE tl_iso_order_document (document_number VARCHAR(64))');
        $connection->insert('tl_iso_order_document', ['document_number' => 'Ä-0009']);

        $generator = new DocumentNumberGenerator(
            new TransactionEventDispatcher(new EventDispatcher()),
            $connection,
        );

        $this->assertSame('Ä-0010', $generator->generateForDocument(new NumberFormat('Ä-', 4)));
    }
}
