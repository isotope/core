<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope;

use Contao\Database;
use Contao\Database\Installer;
use Contao\System;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

/**
 * DatabaseUpdater automatically performs safe or necessary database updates on config changes.
 * Safe changes include adding new fields, altering field config and adding and dropping indexes.
 */
class DatabaseUpdater extends Installer
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct()
    {
        parent::__construct();

        $this->connection = System::getContainer()->get('database_connection');
    }

    /**
     * Automatically add and update columns and keys.
     */
    public function autoUpdateTables(array $arrTables): void
    {
        foreach (System::getContainer()->get('contao.installer')->getCommands() as $arrCommands) {
            foreach ($arrCommands as $strCommand) {
                foreach ($arrTables as $strTable) {
                    $this->runQuery($strCommand, $strTable);
                }
            }
        }
    }

    private function runQuery(string $strCommand, string $strTable): void
    {
        if (preg_match("/^(CREATE|DROP) INDEX [\w`]+ ON $strTable/i", $strCommand)) {
            $this->connection->executeStatement($strCommand);
            return;
        }

        if (str_starts_with($strCommand, "ALTER TABLE $strTable ")) {
            $this->fixStringToInt($strCommand, $strTable);
            $this->connection->executeStatement($strCommand);
        }
    }

    /**
     * Try to fix string to int field conversion.
     */
    private function fixStringToInt(string $strCommand, string $strTable): void
    {
        // New field type is not integer
        if (!preg_match('/ `?(\w+)`? (INT DEFAULT 0 NOT NULL|int\(10\) NOT NULL default 0)$/i', $strCommand, $match)) {
            return;
        }

        $columns = $this->connection->createSchemaManager()->listTableColumns($strTable);

        // Current field type is not string
        if (!isset($columns[$match[1]]) || !$this->isStringType($columns[$match[1]]->getType())) {
            return;
        }

        Database::getInstance()->query("UPDATE `$strTable` SET `$match[1]`='0' WHERE `$match[1]`=''");
    }

    private function isStringType(Type $type): bool
    {
        return \in_array($type->getName(), [Types::STRING, Types::TEXT], true);
    }
}
