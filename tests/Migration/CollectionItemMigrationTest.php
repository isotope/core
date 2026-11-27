<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Table;
use Isotope\CoreBundle\Migration\CollectionItemMigration;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use Isotope\CoreBundle\Store\Tax\CalculatedTax;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CollectionItemMigrationTest extends TestCase
{
    #[DataProvider('taxScenarios')]
    public function testMigratesTaxes(float $unitPrice, float $legacyNet, string $rate, bool $added, bool $multiple, float $expectedBase, float $expectedTax): void
    {
        $table = new Table('tl_iso_product_collection_item');
        $table->addColumn('sorting', 'integer');

        $schema = $this->createStub(AbstractSchemaManager::class);
        $schema
            ->method('introspectTableByUnquotedName')
            ->willReturn($table)
        ;

        $tax = [
            'pid' => 1,
            'sorting' => 1,
            'type' => 'tax',
            'source_id' => 0,
            'label' => 'Historical tax',
            'price' => $rate,
            'tax_id' => '7',
            'total_price' => '24.30',
            'addToTotal' => $added,
        ];

        $numbers = $multiple ? '7,8' : '7';

        $charge = [
            'pid' => 1,
            'sorting' => 3,
            'type' => 'shipping',
            'source_id' => 4,
            'label' => 'Shipping',
            'price' => $unitPrice,
            'tax_id' => $numbers,
            'total_price' => $unitPrice,
            'tax_free_total_price' => $legacyNet,
            'addToTotal' => true,
        ];

        $surcharges = [$tax, $charge];
        if ($multiple) {
            $surcharges[] = array_replace($tax, ['tax_id' => '8', 'sorting' => 2]);
        }

        $connection = $this->createMock(Connection::class);
        $connection
            ->method('createSchemaManager')
            ->willReturn($schema)
        ;
        $connection
            ->method('fetchAllAssociativeIndexed')
            ->willReturn([1 => ['subtotal' => $unitPrice * 2, 'total' => '999.00']])
        ;
        $connection
            ->method('fetchAllAssociative')
            ->willReturnOnConsecutiveCalls($surcharges, [[
                'id' => 10,
                'quantity' => 2,
                'price' => $unitPrice,
                'tax_free_price' => $legacyNet,
                'tax_id' => $numbers,
            ]])
        ;

        $product = null;
        $inserted = [];

        $connection
            ->expects($this->once())
            ->method('update')
            ->willReturnCallback(
                static function (string $table, array $data, array $criteria) use (&$product): int {
                    self::assertSame(['id' => 10], $criteria);

                    $product = CalculatedPrice::createFromJson($data['price_data']);

                    return 1;
                },
            )
        ;
        $connection
            ->expects($this->exactly(4))
            ->method('insert')
            ->willReturnCallback(
                static function (string $table, array $data) use (&$inserted): int {
                    $inserted[$data['type'].'.'.($data['level'] ?? 0)] = CalculatedPrice::createFromJson($data['price_data']);

                    return 1;
                },
            )
        ;

        $this->assertTrue((new CollectionItemMigration($connection))->run()->isSuccessful());
        $this->assertSame($unitPrice, $product->getUnitPrice());
        $this->assertSame($unitPrice * 2, $product->getTotalPrice());

        foreach ([$product, $inserted['shipping.1']] as $price) {
            $tax = $price->getCalculatedTaxes()->get('7');
            $this->assertInstanceOf(CalculatedTax::class, $tax);
            $this->assertEqualsWithDelta($expectedBase, $tax->getPrice(), 0.00001);
            $this->assertEqualsWithDelta($expectedTax, $tax->getAmount(), 0.00001);
            $this->assertSame(7, $tax->getNumber());
            $this->assertSame('Historical tax', $tax->getLabel());
        }

        $this->assertEqualsWithDelta($expectedTax * 2, $inserted['subtotal.0']->getCalculatedTaxes()->get('7')->getAmount(), 0.00001);
        $this->assertEqualsWithDelta(999.0, $inserted['total.0']->getTotalPrice(), PHP_FLOAT_EPSILON);
        $this->assertEqualsWithDelta(24.3, $inserted['total.0']->getCalculatedTaxes()->get('7')->getAmount(), PHP_FLOAT_EPSILON);
    }

    public static function taxScenarios(): iterable
    {
        yield 'included tax with wrong legacy net' => [108.1, 108.1, '8.1%', false, false, 100.0, 8.1];
        yield 'added tax with wrong legacy net' => [100.0, 40.0, '8.1%', true, false, 100.0, 8.1];
        yield 'zero percentage with wrong legacy net' => [100.0, 40.0, '0%', false, false, 100.0, 0.0];
        yield 'negative charge' => [-108.1, -40.0, '8.1%', false, false, -100.0, -8.1];
        yield 'multiple taxes retain legacy net' => [108.1, 50.0, '8.1%', false, true, 50.0, 4.05];
        yield 'fixed tax retains legacy difference' => [108.1, 100.0, '8.10', false, false, 100.0, 8.1];
        yield 'invalid percentage retains fallback' => [100.0, 40.0, '-100%', false, false, 40.0, 60.0];
    }
}
