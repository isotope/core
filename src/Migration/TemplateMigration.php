<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class TemplateMigration extends AbstractMigration
{
    private const RESET = [
        'tl_iso_producttype' => [
            'list_template' => 'iso_list_default',
            'reader_template' => 'iso_reader_default',
        ],
        'tl_module' => [
            'iso_list_layout' => 'iso_list_default',
            'iso_reader_layout' => 'iso_reader_default',
        ],
    ];

    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        foreach (self::RESET as $table => $fields) {
            if (!$schemaManager->tablesExist([$table])) {
                continue;
            }

            $qb = $this->connection->createQueryBuilder();
            $qb->select('COUNT(*)')->from($table);

            foreach ($fields as $k => $v) {
                $qb->orWhere("$k='$v'");
            }

            if ($qb->fetchOne() > 0) {
                return true;
            }
        }

        return false;
    }

    public function run(): MigrationResult
    {
        $schemaManager = $this->connection->createSchemaManager();

        foreach (self::RESET as $table => $fields) {
            if (!$schemaManager->tablesExist([$table])) {
                continue;
            }

            foreach ($fields as $k => $v) {
                $this->connection->executeStatement("UPDATE $table SET $k='' WHERE $k='$v'");
            }
        }

        return $this->createResult(true);
    }
}
