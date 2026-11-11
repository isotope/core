<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Store\Document;

use Doctrine\DBAL\DriverManager;
use Isotope\CoreBundle\EventDispatcher\TransactionEventDispatcher;
use Isotope\CoreBundle\Store\Document\DocumentNumberGenerator;
use Isotope\CoreBundle\Store\Document\NumberFormat;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class DocumentNumberGeneratorTest extends TestCase
{
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
