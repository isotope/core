<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * @internal
 */
#[AutoconfigureTag('contao.migration', ['priority' => 100])]
class CsvMigration extends AbstractMigration
{
    private const array MAPPING = [
        'tl_iso_tax_class' => ['rates'],
        'tl_iso_tax_rate' => ['rate', 'address'],
        'tl_module' => ['iso_config_ids'],
    ];

    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        $mapping = $this->getExistingFields();

        if ([] === $mapping) {
            return false;
        }

        foreach ($mapping as $table => $columns) {
            $needsMigration = $this->connection->fetchOne("SELECT TRUE FROM $table WHERE ".implode(" LIKE 'a:%' OR ", $columns)." LIKE 'a:%'");

            if (false !== $needsMigration) {
                return true;
            }
        }

        return false;
    }

    public function run(): MigrationResult
    {
        $mapping = $this->getExistingFields();

        foreach ($mapping as $table => $columns) {
            $rows = $this->connection->fetchAllAssociative('SELECT id, '.implode(', ', $columns)." FROM $table WHERE ".implode(" LIKE 'a:%' OR ", $columns)." LIKE 'a:%'");

            foreach ($rows as $row) {
                $data = [];

                foreach ($columns as $column) {
                    // Not actually a CSV migration, but we'll abuse the migration class for this
                    if ('tl_iso_tax_rate' === $table && 'rate' === $column) {
                        $rate = StringUtil::deserialize($row[$column]);

                        if (\is_array($rate) && isset($rate['value'])) {
                            $data[$column] = $rate['value'];
                        }
                        continue;
                    }

                    if (\is_array($value = StringUtil::deserialize($row[$column]))) {
                        $data[$column] = implode(',', $value);
                    }
                }

                if ([] !== $data) {
                    $this->connection->update($table, $data, ['id' => $row['id']]);
                }
            }
        }

        return $this->createResult(true);
    }

    private function getExistingFields(): array
    {
        $mapping = [];
        $schemaManager = $this->connection->createSchemaManager();

        foreach (self::MAPPING as $table => $fields) {
            if (!$schemaManager->tableExists($table)) {
                continue;
            }

            $tableDefinition = $schemaManager->introspectTableByUnquotedName($table);

            foreach ($fields as $field) {
                if ($tableDefinition->hasColumn($field)) {
                    $mapping[$table][] = $field;
                }
            }
        }

        return $mapping;
    }
}
