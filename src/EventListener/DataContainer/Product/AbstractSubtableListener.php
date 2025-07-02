<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Product;

use Contao\StringUtil;
use Doctrine\DBAL\Connection;

abstract class AbstractSubtableListener
{
    public function __construct(protected readonly Connection $connection)
    {
    }

    public function createVersion(string $table, int $id, int|string $version, array &$data): void
    {
        $value = $this->getValue($id);

        if (null === $value) {
            return;
        }

        $data[$this->getField()] = serialize($value);

        $this->connection->executeStatement(
            'UPDATE tl_version SET data=? WHERE fromTable=? AND pid=? AND version=?',
            [serialize($data), $table, $id, $version],
        );
    }

    public function restoreVersion(string $table, int $pid, int|string $version, array $data): void
    {
        $field = $this->getField();

        $vData = $this->connection->fetchOne(
            'SELECT data FROM tl_version WHERE fromTable=? AND pid=? AND version=?',
            [$table, $pid, $version],
        );
        $vData = StringUtil::deserialize($vData);

        if (!\is_array($vData) || !isset($vData[$field])) {
            return;
        }

        $this->saveValue(StringUtil::deserialize($vData[$field], true), $pid);
    }

    abstract protected function getField(): string;

    abstract protected function getValue(int $pid): array|null;

    abstract protected function saveValue(array $value, int $pid): bool;
}
