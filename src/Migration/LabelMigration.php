<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Migration;

use Composer\InstalledVersions;
use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

/**
 * @internal
 */
class LabelMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        if (!InstalledVersions::isInstalled('terminal42/contao-backend-translator')) {
            return false;
        }

        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tableExists('tl_iso_label')) {
            return false;
        }

        return true;
    }

    public function run(): MigrationResult
    {
        if (!$this->connection->createSchemaManager()->tableExists('tl_translation')) {
            $this->connection->executeStatement("CREATE TABLE tl_translation (id INT UNSIGNED AUTO_INCREMENT NOT NULL, tstamp INT UNSIGNED DEFAULT 0 NOT NULL, domain VARCHAR(64) DEFAULT '' NOT NULL, message VARCHAR(255) DEFAULT '' NOT NULL, locale VARCHAR(5) DEFAULT '' NOT NULL, translation VARCHAR(255) DEFAULT '' NOT NULL, INDEX locale_domain (locale, domain), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB ROW_FORMAT = DYNAMIC");
        }

        $labels = $this->connection->fetchAllAssociative('SELECT * FROM tl_iso_label');

        foreach ($labels as $label) {
            $this->connection->insert('tl_translation', [
                'tstamp' => $label['tstamp'],
                'domain' => 'isotope',
                'message' => $label['label'],
                'locale' => $label['language'],
                'translation' => $label['replacement'],
            ]);
        }

        $this->connection->executeStatement('DROP TABLE tl_iso_label');

        return $this->createResult(true);
    }
}
