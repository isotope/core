<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;

class UppercaseCountryMigration extends AbstractMigration
{
    private const FIELD_MAP = [
        'tl_iso_config' => ['country' => false, 'billing_country' => false, 'shipping_country' => false, 'billing_countries' => true, 'shipping_countries' => true],
        'tl_iso_payment' => ['countries' => true],
        'tl_iso_shipping' => ['countries' => true, 'subdivisions' => true],
        'tl_iso_tax_rate' => ['countries' => true],
        'tl_iso_address' => ['country' => false],
    ];

    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        foreach (self::FIELD_MAP as $table => $fields) {
            if (!$schemaManager->tablesExist([$table])) {
                continue;
            }

            $schema = $schemaManager->introspectTableByUnquotedName($table);

            $check = false;
            $qb = $this->connection->createQueryBuilder();
            $qb
                ->select('COUNT(*)')
                ->from($table)
            ;

            foreach ($fields as $field => $isArray) {
                if (!$schema->hasColumn($field)) {
                    continue;
                }

                $check = true;
                $qb->orWhere("BINARY `$field` != BINARY UPPER(`$field`)");
            }

            if ($check && $qb->fetchOne() > 0) {
                return true;
            }
        }

        return false;
    }

    public function run(): MigrationResult
    {
        $schemaManager = $this->connection->createSchemaManager();

        foreach (self::FIELD_MAP as $table => $fields) {
            if (!$schemaManager->tablesExist([$table])) {
                continue;
            }

            $schema = $schemaManager->introspectTableByUnquotedName($table);

            $update = false;
            $qb = $this->connection->createQueryBuilder();
            $qb
                ->select('id', ...array_keys($fields))
                ->from($table)
            ;

            foreach ($fields as $field => $isArray) {
                if (!$schema->hasColumn($field)) {
                    continue;
                }

                $update = true;
                $qb->orWhere("BINARY `$field` != BINARY UPPER(`$field`)");
            }

            if ($update) {
                foreach ($qb->fetchAllAssociative() as $result) {
                    $this->connection->update(
                        $table,
                        $this->getData($result, $fields),
                        ['id' => $result['id']],
                    );
                }
            }
        }

        return $this->createResult(true);
    }

    private function getData(array $result, array $fields): array
    {
        $data = [];

        foreach ($fields as $field => $isArray) {
            $value = (string) $result[$field];

            if ($isArray) {
                $value = str_starts_with($value, 'a:') ? StringUtil::deserialize($value, true) : explode(',', $value);
                $data[$field] = implode(',', array_map(strtoupper(...), $value));
            } else {
                $data[$field] = strtoupper($result[$field]);
            }
        }

        return $data;
    }
}
