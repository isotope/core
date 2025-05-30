<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Database;

use Contao\CoreBundle\Migration\CommandCompiler;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * DatabaseUpdater automatically performs safe or necessary database updates on config changes.
 * Safe changes include adding new fields, altering field config and adding and dropping indexes.
 */
class DatabaseUpdater
{
    public function __construct(
        private readonly Connection $connection,
        #[Autowire('@contao.migration.command_compiler')] private readonly CommandCompiler $commandCompiler,
    ) {
    }

    /**
     * Automatically add and update columns and keys.
     */
    public function autoUpdateTables(array $tables): void
    {
        foreach ($this->commandCompiler->compileCommands(true) as $command) {
            foreach ($tables as $table) {
                $this->runQuery($command, $table);
            }
        }
    }

    private function runQuery(string $command, string $table): void
    {
        if (preg_match('/^(CREATE|DROP) INDEX [\w`]+ ON $table/i', $command)) {
            $this->connection->executeStatement($command);

            return;
        }

        if (str_starts_with($command, "ALTER TABLE $table ")) {
            foreach ($this->splitCommand($command, $table) as $change) {
                $this->fixStringToInt($change, $table);
                $this->fixNullValues($change, $table);
            }
            $this->connection->executeStatement($command);
        }
    }

    /**
     * Try to fix string to int field conversion.
     */
    private function fixStringToInt(string $command, string $table): void
    {
        // New field type is not integer
        if (!preg_match('/ `?(\w+)`? (INT DEFAULT 0 NOT NULL|int\(10\) NOT NULL default 0)$/i', $command, $match)) {
            return;
        }

        $columns = $this->connection->createSchemaManager()->listTableColumns($table);

        // Current field type is not string
        if (!isset($columns[$match[1]]) || !$this->isStringType($columns[$match[1]]->getType())) {
            return;
        }

        $this->connection->executeStatement("UPDATE `$table` SET `$match[1]`='0' WHERE `$match[1]`='' OR `$match[1]` IS NULL");
    }

    /**
     * Try to fix NULL values when field is changed to type that does not allow NULL.
     */
    private function fixNullValues(string $command, string $table): void
    {
        // New field type is not integer
        if (!preg_match('/^CHANGE `?(\w+)`? .+ NOT NULL/i', $command, $match)) {
            return;
        }

        $this->connection->executeStatement("UPDATE `$table` SET `$match[1]`='' WHERE `$match[1]` IS NULL");
    }

    private function isStringType(Type $type): bool
    {
        return \in_array($type->getName(), [Types::STRING, Types::TEXT], true);
    }

    /**
     * Splits the SQL into multiple commands (e.g. ALTER xxx ADD xx, ADD xx) but makes sure a comma separator within
     * default value (e.g. ALTER xxx ADD foo DEFAULT ',') is not split into separate commands.
     */
    private function splitCommand(string $command, string $table): array
    {
        $parts = explode("'", substr($command, \strlen("ALTER TABLE $table ")));
        $command = '';

        foreach ($parts as $i => $c) {
            $command .= $i % 2 ? "'".str_replace(',', '%comma%', $c)."'" : $c;
        }

        return array_map(static fn (string $sql) => trim(str_replace('%comma%', ',', $sql)), explode(',', $command));
    }
}
