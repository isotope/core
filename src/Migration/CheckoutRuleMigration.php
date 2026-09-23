<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;

/**
 * @internal
 */
class CheckoutRuleMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tablesExist(['tl_iso_rule', 'tl_iso_rule_condition', 'tl_iso_payment', 'tl_iso_shipping'])) {
            return false;
        }

        foreach (['tl_iso_shipping', 'tl_iso_payment'] as $name) {
            $table = $schemaManager->introspectTableByUnquotedName($name);

            if (
                !$table->hasColumn('rule')
                || !$table->hasColumn('minimum_total')
                || !$table->hasColumn('maximum_total')
                || !$table->hasColumn('quantity_mode')
                || !$table->hasColumn('minimum_quantity')
                || !$table->hasColumn('maximum_quantity')
                || !$table->hasColumn('config_ids')
                || !$table->hasColumn('product_types')
                || !$table->hasColumn('product_types_condition')
            ) {
                return false;
            }
        }

        return true;
    }

    public function run(): MigrationResult
    {
        $this->migrateTable('tl_iso_payment', 'Payment "%s" (ID %s)');
        $this->migrateTable('tl_iso_shipping', 'Shipping "%s" (ID %s)');

        return $this->createResult(true);
    }

    private function migrateTable(string $table, string $name): void
    {
        $rows = $this->connection->fetchAllAssociative("SELECT * FROM $table WHERE minimum_total > 0 OR maximum_total > 0 OR minimum_quantity > 0 OR maximum_quantity > 0 OR config_ids IS NOT NULL OR product_types IS NOT NULL");

        foreach ($rows as $row) {
            $conditions = [];
            $conditions[] = $this->createRangeCondition('cart_total', 'amount', (float) ($row['minimum_total'] ?? 0), (float) ($row['maximum_total'] ?? 0));

            if ('cart_items' === ($row['quantity_mode'] ?? null)) {
                $conditions[] = $this->createRangeCondition('cart_quantity', 'quantity', (float) ($row['minimum_quantity'] ?? 0), (float) ($row['maximum_quantity'] ?? 0));
            } elseif ('cart_products' === ($row['quantity_mode'] ?? null)) {
                $conditions[] = $this->createRangeCondition('cart_products', 'quantity', (float) ($row['minimum_quantity'] ?? 0), (float) ($row['maximum_quantity'] ?? 0));
            }

            if (!empty($row['config_ids'] ?? null)) {
                $conditions[] = $this->createHasCondition('store_config', 'has_one', 'config_ids', $row['config_ids']);
            }

            if (!empty($row['product_types'] ?? null)) {
                $operator = match ($row['product_types_condition']) {
                    'onlyAvailable' => 'has_only',
                    'notAvailable' => 'has_none',
                    'oneAvailable' => 'has_one',
                    'allAvailable' => 'has_all',
                    default => null,
                };

                if ($operator) {
                    $conditions[] = $this->createHasCondition('cart_producttypes', $operator, 'product_types', $row['product_types']);
                }
            }

            $conditions = array_values(array_filter($conditions));

            if ([] === $conditions) {
                continue;
            }

            $this->connection->insert('tl_iso_rule', [
                'tstamp' => time(),
                'name' => \sprintf($name, $row['name'], $row['id']),
                'enabled' => 1,
            ]);

            $ruleId = $this->connection->lastInsertId();

            foreach ($conditions as $i => $condition) {
                $this->connection->insert('tl_iso_rule_condition', [
                    'pid' => $ruleId,
                    'sorting' => $i * 128,
                    'tstamp' => time(),
                    ...$condition,
                ]);
            }

            $this->connection->executeStatement("UPDATE $table SET rule=? WHERE id=?", [$ruleId, $row['id']]);
        }

        $this->connection->executeStatement(<<<SQL
                ALTER TABLE $table
                    DROP COLUMN minimum_total,
                    DROP COLUMN maximum_total,
                    DROP COLUMN quantity_mode,
                    DROP COLUMN minimum_quantity,
                    DROP COLUMN maximum_quantity,
                    DROP COLUMN config_ids,
                    DROP COLUMN product_types,
                    DROP COLUMN product_types_condition
            SQL);
    }

    private function createRangeCondition(string $type, string $field, float $from, float $to): array|null
    {
        if ($from > 0 && $to > 0) {
            return [
                'type' => $type,
                'operator' => 'range',
                "{$field}_from" => $from,
                "{$field}_to" => $to,
            ];
        }

        if ($from > 0) {
            return [
                'type' => $type,
                'operator' => 'gte',
                $field => $from,
            ];
        }

        if ($to > 0) {
            return [
                'type' => $type,
                'operator' => 'lte',
                $field => $to,
            ];
        }

        return null;
    }

    private function createHasCondition(string $type, string $operator, string $field, string $value): array|null
    {
        $ids = StringUtil::deserialize($value);

        if (empty($ids) || !\is_array($ids)) {
            return null;
        }

        return [
            'type' => $type,
            'operator' => $operator,
            $field => implode(',', $ids),
        ];
    }
}
