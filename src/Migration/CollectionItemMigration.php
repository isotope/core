<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

/**
 * @internal
 */
class CollectionItemMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tablesExist(['tl_iso_product_collection_surcharge', 'tl_iso_product_collection_item'])) {
            return false;
        }

        $table = $schemaManager->introspectTableByUnquotedName('tl_iso_product_collection_item');

        return !$table->hasColumn('identifier')
            && !$table->hasColumn('level')
            && !$table->hasColumn('label')
            && !$table->hasColumn('referencePage')
            && !$table->hasColumn('unit_label')
            && $table->hasColumn('type')
            && $table->hasColumn('product_id')
            && $table->hasColumn('name')
            && $table->hasColumn('jumpTo');
    }

    public function run(): MigrationResult
    {
        $this->connection->executeStatement(
            <<<'SQL'
                ALTER TABLE tl_iso_product_collection_item
                    ADD COLUMN `identifier` varchar(64) NOT NULL,
                    ADD COLUMN `level` tinyint unsigned NOT NULL default 0,
                    ADD COLUMN `unit_label` varchar(128) NULL,
                    ADD COLUMN `price_data` mediumblob NULL,
                    CHANGE COLUMN `name` `label` varchar(255) NOT NULL default '',
                    CHANGE COLUMN `jumpTo` `referencePage` int(0) unsigned NOT NULL default 0
                SQL,
        );

        if (!$this->connection->createSchemaManager()->introspectTableByUnquotedName('tl_iso_product_collection_item')->hasColumn('sorting')) {
            $this->connection->executeStatement('ALTER TABLE tl_iso_product_collection_item ADD COLUMN `sorting` int(10) unsigned NOT NULL default 0');
        }

        $this->connection->executeStatement("UPDATE tl_iso_product_collection_item SET type='product', identifier=IF(product_id REGEXP '^[0-9]+$', CONCAT('tl_iso_product.', product_id), product_id)");

        $surcharges = $this->connection->fetchAllAssociative('SELECT * FROM tl_iso_product_collection_surcharge ORDER BY pid, sorting');
        $surchargesByOrder = [];

        foreach ($surcharges as $surcharge) {
            $surchargesByOrder[$surcharge['pid']][] = $surcharge;

            $data = [
                'pid' => $surcharge['pid'],
                'sorting' => $surcharge['sorting'],
                'type' => $surcharge['type'],
                'identifier' => '',
                'label' => $surcharge['label'],
            ];

            if ($surcharge['source_id']) {
                $data['identifier'] = match ($surcharge['type']) {
                    'shipping' => 'tl_iso_shipping.'.$surcharge['source_id'],
                    'payment' => 'tl_iso_payment.'.$surcharge['source_id'],
                    default => '',
                };
            }

            if (is_numeric($surcharge['price'])) {
                $data['price'] = $surcharge['price'];
            } else {
                $data['unit_label'] = str_replace('&nbsp;', '', $surcharge['price']);
            }

            $this->connection->insert('tl_iso_product_collection_item', $data);
        }

        $this->migrateOrderPrices($surchargesByOrder);

        $this->connection->executeStatement('DROP TABLE tl_iso_product_collection_surcharge');

        return $this->createResult(true);
    }

    /**
     * Isotope 3 reads the prices of a placed order from the "price_data" of its items and from the "subtotal" and
     * "total" items. Isotope 2 stored the unit price of the products and the charges, totals and taxes as surcharges,
     * so without this conversion the prices of old orders are missing in documents and the order details.
     *
     * @param array<int|string, list<array<string, mixed>>> $surchargesByOrder
     */
    private function migrateOrderPrices(array $surchargesByOrder): void
    {
        $orders = $this->connection->fetchAllAssociative(
            "SELECT id, subtotal, total FROM tl_iso_product_collection WHERE type='order' AND locked IS NOT NULL",
        );

        foreach ($orders as $order) {
            $this->migrateOrderPrice(
                (int) $order['id'],
                (float) $order['subtotal'],
                (float) $order['total'],
                $surchargesByOrder[$order['id']] ?? [],
            );
        }
    }

    /**
     * @param list<array<string, mixed>> $surcharges
     */
    private function migrateOrderPrice(int $orderId, float $subtotal, float $total, array $surcharges): void
    {
        // Isotope 2 has one "tax" surcharge per tax rate: price is the rate ("8.1%"), total_price the tax amount
        $taxRates = [];

        foreach ($surcharges as $surcharge) {
            if ('tax' === $surcharge['type']) {
                $taxRates[(string) $surcharge['tax_id']] = [
                    'label' => (string) $surcharge['label'],
                    'rate' => (float) $surcharge['price'],
                    'number' => \count($taxRates) + 1,
                    'amount' => (float) $surcharge['total_price'],
                ];
            }
        }

        $now = time();
        $sorting = 0;
        $productTaxes = [];
        $allTaxes = [];

        $products = $this->connection->fetchAllAssociative(
            "SELECT id, quantity, price, tax_free_price, tax_id FROM tl_iso_product_collection_item WHERE pid=? AND type='product' ORDER BY id",
            [$orderId],
        );

        // The surcharge items have been created from the same data before, they are replaced
        $this->connection->executeStatement("DELETE FROM tl_iso_product_collection_item WHERE pid=? AND type!='product'", [$orderId]);

        foreach ($products as $product) {
            $quantity = max(1, (int) $product['quantity']);
            $unit = (float) $product['price'];
            $lineTotal = round($unit * $quantity, 2);
            $taxId = (string) strtok((string) $product['tax_id'], ',');
            $taxes = $this->createTaxes($taxId, ($unit - (float) $product['tax_free_price']) * $quantity, $lineTotal, $taxRates);

            $this->addTaxes($productTaxes, $taxes);
            $this->addTaxes($allTaxes, $taxes);

            $this->connection->update('tl_iso_product_collection_item', [
                'price' => $lineTotal,
                'tax_id' => $this->getTaxNumbers($taxes, $taxRates),
                'sorting' => ++$sorting,
                'level' => 0,
                'price_data' => $this->createPriceData($unit, $quantity, $lineTotal, $taxes, $taxRates),
            ], ['id' => $product['id']]);
        }

        $this->insertPriceItem($orderId, 'subtotal', 'subtotal.0', '', 0, ++$sorting, $subtotal, $productTaxes, $taxRates);

        foreach ($surcharges as $surcharge) {
            if ('tax' === $surcharge['type']) {
                continue;
            }

            $chargeTotal = (float) $surcharge['total_price'];
            $taxes = $this->createTaxes(
                (string) strtok((string) $surcharge['tax_id'], ','),
                $chargeTotal - (float) $surcharge['tax_free_total_price'],
                $chargeTotal,
                $taxRates,
            );

            $this->addTaxes($allTaxes, $taxes);

            $identifier = '';

            if ($surcharge['source_id']) {
                $identifier = match ($surcharge['type']) {
                    'shipping' => 'tl_iso_shipping.'.$surcharge['source_id'],
                    'payment' => 'tl_iso_payment.'.$surcharge['source_id'],
                    default => '',
                };
            }

            $this->insertPriceItem(
                $orderId,
                (string) $surcharge['type'],
                $identifier,
                (string) $surcharge['label'],
                1,
                ++$sorting,
                $chargeTotal,
                $taxes,
                $taxRates,
            );
        }

        // The tax amount booked with the order is authoritative, the sum of the items can differ by the rounding
        foreach ($taxRates as $taxId => $taxRate) {
            if ($taxRate['amount'] >= 0.005) {
                $allTaxes[$taxId] = ['amount' => $taxRate['amount'], 'gross' => $allTaxes[$taxId]['gross'] ?? $total];
            }
        }

        $this->insertPriceItem($orderId, 'subtotal', 'subtotal.1', '', 1, ++$sorting, $total, $allTaxes, $taxRates);
        $this->insertPriceItem($orderId, 'total', '', '', 0, ++$sorting, $total, $allTaxes, $taxRates);
    }

    /**
     * @param array<string, array{label: string, rate: float, number: int, amount: float}> $taxRates
     *
     * @return array<string, array{amount: float, gross: float}>
     */
    private function createTaxes(string $taxId, float $amount, float $gross, array $taxRates): array
    {
        if ($amount < 0.005) {
            return [];
        }

        // Without a tax id on the item the only tax rate of the order applies
        if ('' === $taxId || !isset($taxRates[$taxId])) {
            $taxId = (string) array_key_first($taxRates);
        }

        return '' === $taxId ? [] : [$taxId => ['amount' => round($amount, 2), 'gross' => $gross]];
    }

    /**
     * The "tax_id" of an item is the CSV list of the (incremental) numbers of the applicable taxes.
     *
     * @param array<string, array{amount: float, gross: float}> $taxes
     * @param array<string, array{label: string, rate: float, number: int, amount: float}> $taxRates
     */
    private function getTaxNumbers(array $taxes, array $taxRates): string
    {
        return implode(',', array_map(static fn (string $taxId): int => $taxRates[$taxId]['number'] ?? 1, array_keys($taxes)));
    }

    /**
     * @param array<string, array{amount: float, gross: float}> $target
     * @param array<string, array{amount: float, gross: float}> $taxes
     */
    private function addTaxes(array &$target, array $taxes): void
    {
        foreach ($taxes as $taxId => $tax) {
            $target[$taxId]['amount'] = round(($target[$taxId]['amount'] ?? 0.0) + $tax['amount'], 2);
            $target[$taxId]['gross'] = round(($target[$taxId]['gross'] ?? 0.0) + $tax['gross'], 2);
        }
    }

    /**
     * @param array<string, array{amount: float, gross: float}> $taxes
     * @param array<string, array{label: string, rate: float, number: int, amount: float}>  $taxRates
     */
    private function insertPriceItem(int $orderId, string $type, string $identifier, string $label, int $level, int $sorting, float $price, array $taxes, array $taxRates): void
    {
        $this->connection->insert('tl_iso_product_collection_item', [
            'pid' => $orderId,
            'tstamp' => time(),
            'sorting' => $sorting,
            'type' => $type,
            'identifier' => $identifier,
            'label' => $label,
            'quantity' => 1,
            'price' => $price,
            'tax_id' => $this->getTaxNumbers($taxes, $taxRates),
            'level' => $level,
            'price_data' => $this->createPriceData($price, 1, $price, $taxes, $taxRates),
        ]);
    }

    /**
     * Same structure as Isotope\CoreBundle\Store\Price\CalculatedPrice::jsonSerialize().
     *
     * @param array<string, array{amount: float, gross: float}> $taxes
     * @param array<string, array{label: string, rate: float, number: int, amount: float}>  $taxRates
     */
    private function createPriceData(float $unitPrice, int $quantity, float $totalPrice, array $taxes, array $taxRates): string
    {
        $calculatedTaxes = [];
        $rates = [];

        foreach ($taxes as $taxId => $tax) {
            $rate = $taxRates[$taxId]['rate'] ?? 0.0;
            $label = $taxRates[$taxId]['label'] ?? '';

            $calculatedTaxes[$taxId] = [
                'id' => (string) $taxId,
                'amount' => $tax['amount'],
                'tax_rate' => $rate,
                'price' => round($tax['gross'] - $tax['amount'], 2),
                'label' => $label,
                'number' => $taxRates[$taxId]['number'] ?? 1,
            ];

            $rates[] = ['id' => (string) $taxId, 'tax_rate' => $rate, 'percentage' => 100, 'label' => $label];
        }

        return json_encode([
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'total_price' => $totalPrice,
            'unit_label' => null,
            'is_range' => false,
            'calculated_taxes' => (object) $calculatedTaxes,
            'tax_rates' => ['rates' => $rates, 'included' => $rates],
        ], JSON_THROW_ON_ERROR);
    }
}
