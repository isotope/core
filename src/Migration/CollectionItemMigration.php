<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use Isotope\CoreBundle\Store\Tax\CalculatedTax;
use Isotope\CoreBundle\Store\Tax\CalculatedTaxCollection;
use Isotope\CoreBundle\Store\Tax\TaxRate;

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

        $orderSurcharges = [];

        $orders = $this->connection->fetchAllAssociativeIndexed(
            "SELECT id, subtotal, total FROM tl_iso_product_collection WHERE type='order' AND locked IS NOT NULL",
        );

        $surcharges = $this->connection->fetchAllAssociative('SELECT * FROM tl_iso_product_collection_surcharge ORDER BY pid, sorting');

        foreach ($surcharges as $surcharge) {
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

            if (isset($orders[$surcharge['pid']])) {
                $surcharge['item'] = $data;
                $orderSurcharges[$surcharge['pid']][] = $surcharge;
                continue;
            }

            $this->connection->insert('tl_iso_product_collection_item', $data);
        }

        foreach ($orders as $id => $order) {
            $this->migrateOrder((int) $id, (float) $order['subtotal'], (float) $order['total'], $orderSurcharges[$id] ?? []);
        }

        $this->connection->executeStatement('DROP TABLE tl_iso_product_collection_surcharge');

        return $this->createResult(true);
    }

    private function migrateOrder(int $orderId, float $subtotal, float $total, array $surcharges): void
    {
        $taxSurcharges = [];
        $charges = [];

        foreach ($surcharges as $surcharge) {
            if ('tax' === $surcharge['type']) {
                $taxSurcharges[$surcharge['tax_id']] = $surcharge;
            } else {
                $charges[] = $surcharge;
            }
        }

        $products = $this->connection->fetchAllAssociative(
            "SELECT id, quantity, price, tax_free_price, tax_id FROM tl_iso_product_collection_item WHERE pid=? AND type='product' ORDER BY sorting, id",
            [$orderId],
        );

        $sorting = 0;
        $subtotalPrice = new CalculatedPrice($subtotal, $subtotal);

        foreach ($products as $product) {
            $quantity = (int) $product['quantity'];
            $unitPrice = (float) $product['price'];
            $price = new CalculatedPrice($unitPrice, $unitPrice * $quantity, quantity: $quantity);
            $this->addLineTaxes($price, (float) $product['tax_free_price'], $product['tax_id'], $taxSurcharges);
            $this->sumTaxes($subtotalPrice->getCalculatedTaxes(), $price->reduce()->getCalculatedTaxes());

            $this->connection->update(
                'tl_iso_product_collection_item',
                [
                    'sorting' => ++$sorting,
                    'price' => $price->getTotalPrice(),
                    'price_data' => json_encode($price, JSON_THROW_ON_ERROR),
                ],
                ['id' => $product['id']],
            );
        }

        $this->insertSummary($orderId, LineItem::TYPE_SUBTOTAL, 0, ++$sorting, $subtotalPrice);

        $levelSubtotal = $subtotal;
        $orderTaxes = new CalculatedTaxCollection();
        $this->sumTaxes($orderTaxes, $subtotalPrice->getCalculatedTaxes());

        foreach ($charges as $charge) {
            $amount = (float) $charge['total_price'];
            $price = new CalculatedPrice($amount, $amount);
            $this->addLineTaxes($price, (float) $charge['tax_free_total_price'], $charge['tax_id'], $taxSurcharges);
            $this->sumTaxes($orderTaxes, $price->getCalculatedTaxes());

            if ($charge['addToTotal']) {
                $levelSubtotal += $amount;
            }

            $item = $charge['item'];
            $price->setUnitLabel($item['unit_label'] ?? null);

            $this->connection->insert(
                'tl_iso_product_collection_item',
                array_replace($item, [
                    'sorting' => ++$sorting,
                    'level' => 1,
                    'quantity' => 1,
                    'price' => $amount,
                    'tax_id' => $charge['tax_id'],
                    'price_data' => json_encode($price, JSON_THROW_ON_ERROR),
                ]),
            );
        }

        $totalPrice = new CalculatedPrice($total, $total);
        $levelPrice = new CalculatedPrice($levelSubtotal, $levelSubtotal);

        // Only the tax surcharge stores the exact booked amount for each tax.
        foreach ($taxSurcharges as $number => $surcharge) {
            $base = $orderTaxes->get((string) $number)?->getPrice() ?? 0.0;
            $tax = $this->addTax($totalPrice, $surcharge, (float) $surcharge['total_price'], $base);
            $levelPrice->getCalculatedTaxes()->put($tax->getId(), $tax);
        }

        if ([] !== $charges) {
            $this->insertSummary($orderId, LineItem::TYPE_SUBTOTAL, 1, ++$sorting, $levelPrice);
        }

        $this->insertSummary($orderId, LineItem::TYPE_TOTAL, 0, ++$sorting, $totalPrice);
    }

    private function addLineTaxes(CalculatedPrice $price, float $net, string $numbers, array $taxSurcharges): void
    {
        $applicable = array_intersect_key(array_flip(explode(',', $numbers)), $taxSurcharges);
        $difference = $price->getUnitPrice() - $net;

        foreach (array_keys($applicable) as $number) {
            $surcharge = $taxSurcharges[$number];
            $percentage = str_ends_with($surcharge['price'], '%');
            $included = !$surcharge['addToTotal'];

            // Recover a single percentage tax without trusting the legacy net price.
            $recoverable = 1 === \count($applicable) && $percentage && (float) $surcharge['price'] > -100;
            $taxBase = $net;

            if ($recoverable) {
                $taxBase = $included ? $price->getUnitPrice() / (1 + (float) $surcharge['price'] / 100) : $price->getUnitPrice();
            }

            // Complex tax combinations retain the legacy net-price fallback.
            $amount = match (true) {
                $recoverable => $taxBase * (float) $surcharge['price'] / 100,
                $included && 1 === \count($applicable) && abs($difference) >= 0.005 => $difference,
                $percentage => $net * (float) $surcharge['price'] / 100,
                default => 0.0,
            };

            $this->addTax($price, $surcharge, $amount, $taxBase);

            if ($percentage) {
                $rate = new TaxRate($surcharge['tax_id'], (float) $surcharge['price'], label: $surcharge['label']);
                $price->getTaxRates()->add($rate);

                if ($included) {
                    $price->getIncludedTaxRates()->add($rate);
                }
            }
        }
    }

    private function addTax(CalculatedPrice $price, array $surcharge, float $amount, float $base): CalculatedTax
    {
        $id = $surcharge['tax_id'];

        // Legacy fixed taxes have no percentage rate in the new representation.
        $rate = str_ends_with($surcharge['price'], '%') ? (float) $surcharge['price'] : 0.0;

        $tax = (new CalculatedTax($id, $amount, $rate, $base))
            ->setLabel($surcharge['label'])
            ->setNumber((int) $id)
        ;

        $price->getCalculatedTaxes()->put($id, $tax);

        return $tax;
    }

    private function sumTaxes(CalculatedTaxCollection $sum, CalculatedTaxCollection $taxes): void
    {
        foreach ($taxes as $tax) {
            if ($current = $sum->get($tax->getId())) {
                $current->increment($tax);
            } else {
                $sum->put($tax->getId(), clone $tax);
            }
        }
    }

    private function insertSummary(int $orderId, string $type, int $level, int $sorting, CalculatedPrice $price): void
    {
        $this->connection->insert('tl_iso_product_collection_item', [
            'pid' => $orderId,
            'sorting' => $sorting,
            'type' => $type,
            'identifier' => LineItem::TYPE_SUBTOTAL === $type ? $type.'.'.$level : '',
            'quantity' => 1,
            'level' => $level,
            'price' => $price->getTotalPrice(),
            'price_data' => json_encode($price, JSON_THROW_ON_ERROR),
        ]);
    }
}
