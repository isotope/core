<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Attribute;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;

/**
 * Validate language-dependent table and field of foreignKey.
 */
#[AsCallback('tl_iso_attribute', 'fields.foreignKey.save')]
class ForeignKeyValidationListener
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function __invoke(string $value): string
    {
        if ('' === $value) {
            return $value;
        }

        $lines = StringUtil::trimsplit('@\r\n|\n|\r@', $value);

        foreach ($lines as $foreignKey) {
            if (empty($foreignKey) || str_starts_with((string) $foreignKey, '#')) {
                continue;
            }

            if (preg_match('/^([a-z]{2}(-[A-Z]{2})?)=(.+)$/', (string) $foreignKey, $matches)) {
                $foreignKey = $matches[3];
            }

            [$table, $field] = explode('.', (string) $foreignKey, 2);
            $this->connection->executeStatement("SELECT $field FROM $table");
        }

        return $value;
    }
}
