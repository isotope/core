<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

/**
 * @internal
 */
class PricesMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        return $this
            ->connection
            ->createSchemaManager()
            ->tablesExist(['tl_iso_product', 'tl_iso_product_price', 'tl_iso_product_pricetier'])
        ;
    }

    public function run(): MigrationResult
    {
        $this->addPricesFields();

        $advancedPrices = [];
        $advancedTypes = $this->getProductTypePrices();

        /** @var array{
         *     tax_class: int,
         *     config_id: int,
         *     member_group: int,
         *     start: string,
         *     stop: string,
         *     tier_keys: string,
         *     tier_values: string,
         *     type: int,
         *     parent_type: int
         * } $prices
         */
        $prices = $this->connection->fetchAllAssociative(<<<'SQL'
            SELECT
                tl_iso_product_price.*,
                GROUP_CONCAT(tl_iso_product_pricetier.min) AS tier_keys,
                GROUP_CONCAT(tl_iso_product_pricetier.price) AS tier_values,
                p.type,
                pp.type AS parent_type
            FROM tl_iso_product_price
            LEFT JOIN tl_iso_product_pricetier ON tl_iso_product_pricetier.pid = tl_iso_product_price.id
            LEFT JOIN tl_iso_product p ON p.id=tl_iso_product_price.pid
            LEFT JOIN tl_iso_product pp ON pp.id=p.pid
            GROUP BY tl_iso_product_price.id
            ORDER BY config_id DESC, member_group DESC, start DESC, stop DESC
            SQL);

        foreach ($prices as $price) {
            $tiers = array_combine(explode(',', (string) $price['tier_keys']), explode(',', (string) $price['tier_values']));

            if ($advancedTypes[$price['type'] ?: $price['parent_type']] ?? false) {
                $advancedPrices[$price['pid']][] = [
                    'price_tiers' => $tiers,
                    'tax_class' => $price['tax_class'],
                    'config_id' => $price['config_id'],
                    'member_group' => $price['member_group'],
                    'start' => $price['start'],
                    'stop' => $price['stop'],
                ];

                continue;
            }

            // Ignore advanced prices when not enabled for current product (leftover data?!)
            if (!isset($tiers[1]) || $price['config_id'] || $price['member_group'] || $price['start'] || $price['stop']) {
                continue;
            }

            $this->connection->executeStatement(
                'UPDATE tl_iso_product SET price=? WHERE id=?',
                [
                    serialize([
                        'price' => $tiers[1],
                        'tax_class' => $price['tax_class'],
                    ]),
                    $price['pid'],
                ],
            );
        }

        foreach ($advancedPrices as $productId => $prices) {
            $this->connection->executeStatement(
                'UPDATE tl_iso_product SET prices=? WHERE id=?',
                [serialize($prices), $productId],
            );
        }

        $this->connection->executeStatement('DROP TABLE tl_iso_product_price, tl_iso_product_pricetier');

        return $this->createResult(true);
    }

    private function addPricesFields(): void
    {
        $table = $this->connection->createSchemaManager()->introspectTableByUnquotedName('tl_iso_product');

        if (!$table->hasColumn('price')) {
            $this->connection->executeStatement('ALTER TABLE tl_iso_product ADD COLUMN `price` text NULL');
        }

        if (!$table->hasColumn('prices')) {
            $this->connection->executeStatement('ALTER TABLE tl_iso_product ADD COLUMN `prices` text NULL');
        }
    }

    /**
     * @return array<int, bool>
     */
    private function getProductTypePrices(): array
    {
        $prices = $this->connection->fetchAllKeyValue('SELECT id, prices FROM tl_iso_producttype');

        return array_map(boolval(...), $prices);
    }
}
