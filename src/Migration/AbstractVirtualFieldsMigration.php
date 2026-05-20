<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\ProductCollection\Order\OrderFactory;
use Isotope\CoreBundle\Store\OrderManager;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

abstract class AbstractVirtualFieldsMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        return [] !== $this->getExistingFields();
    }

    public function run(): MigrationResult
    {
        $mapping = $this->getExistingFields();
        $schemaManager = $this->connection->createSchemaManager();

        foreach ($mapping as $table => $columns) {
            if (!$schemaManager->introspectTableByUnquotedName($table)->hasColumn('jsonData')) {
                $this->connection->executeStatement("ALTER TABLE $table ADD COLUMN `jsonData` text NULL");
            }

            $rows = $this->connection->fetchAllAssociative('SELECT id, jsonData, '.implode(', ', $columns)." FROM $table");

            foreach ($rows as $row) {
                $data = is_string($row['jsonData']) ? json_decode($row['jsonData'], true, flags: JSON_THROW_ON_ERROR) : [];

                if (!\is_array($data)) {
                    $data = [];
                }

                foreach ($row as $k => $v) {
                    if (isset($data[$k]) || 'id' === $k || 'jsonData' === $k) {
                        continue;
                    }

                    $data[$k] = StringUtil::ensureStringUuids($v);
                }

                $this->connection->update($table, ['jsonData' => json_encode($data, JSON_THROW_ON_ERROR)], ['id' => $row['id']]);
            }

            $this->connection->executeStatement("ALTER TABLE $table DROP COLUMN `".implode('`, DROP COLUMN `', $columns).'`');
        }

        return $this->createResult(true);
    }

    abstract protected function getMapping(): array;

    private function getExistingFields(): array
    {
        $mapping = [];
        $schemaManager = $this->connection->createSchemaManager();

        foreach ($this->getMapping() as $table => $fields) {
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
