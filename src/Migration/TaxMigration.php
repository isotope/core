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
class TaxMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (
            !$schemaManager->tablesExist(['tl_iso_tax_class'])
            || !$schemaManager->tablesExist(['tl_iso_tax_rate'])
        ) {
            return false;
        }

        return $this->connection->fetchOne("SELECT COUNT(*) FROM tl_iso_tax_class WHERE rates LIKE 'a:%'") > 0
            || $this->connection->fetchOne("SELECT COUNT(*) FROM tl_iso_tax_rate WHERE rate LIKE 'a:%'") > 0;
    }

    public function run(): MigrationResult
    {
        $taxClasses = $this->connection->fetchAllKeyValue("SELECT id, rates FROM tl_iso_tax_class WHERE rates LIKE 'a:%'");

        foreach ($taxClasses as $id => $rates) {
            $this->connection->executeStatement(
                'UPDATE tl_iso_tax_class SET rates=? WHERE id=?',
                [
                    implode(',', StringUtil::deserialize($rates, true)),
                    $id,
                ],
            );
        }

        $taxRates = $this->connection->fetchAllAssociative("SELECT id, rate, address FROM tl_iso_tax_rate WHERE rate LIKE 'a:%' OR address LIKE 'a:%'");

        foreach ($taxRates as $tax) {
            $rate = StringUtil::deserialize($tax['rate']);

            if (\is_array($rate) && isset($rate['value'])) {
                $rate = $rate['value'];
            }

            $addresses = StringUtil::deserialize($tax['address'], true);
            $this->connection->executeStatement('UPDATE tl_iso_tax_rate SET rate=?, address=? WHERE id=?', [$rate, implode(',', $addresses), $tax['id']]);
        }

        return $this->createResult(true);
    }
}
