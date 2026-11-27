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
class AttributeOptionsMigration extends AbstractMigration
{
    /**
     * @var array<string, array<int|string, string>>
     */
    private array $valueMap = [];

    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        if (!$this->connection->createSchemaManager()->tablesExist(['tl_iso_attribute', 'tl_iso_product'])) {
            return false;
        }

        return [] !== $this->getUpdates();
    }

    public function run(): MigrationResult
    {
        $updates = $this->getUpdates();

        $this->connection->transactional(
            function () use ($updates): void {
                foreach ($updates as [$table, $id, $data]) {
                    $this->connection->update($table, $data, ['id' => $id]);
                }
            },
        );

        return $this->createResult(true);
    }

    /**
     * Read the stored attribute definitions directly so this also works before
     * the Isotope 2 schema and product DCA have been updated.
     *
     * @return list<array{string, int|string, array<string, string>}>
     */
    private function getUpdates(): array
    {
        $this->valueMap = [];

        $attributes = $this->getAttributes();

        $updates = $this->getAttributeUpdates($attributes);
        array_push($updates, ...$this->getProductUpdates($attributes));
        array_push($updates, ...$this->getProductCollectionUpdates($attributes));

        return $updates;
    }

    private function getAttributes(): array
    {
        $attributes = [];
        $rows = $this->connection->fetchAllAssociative("SELECT * FROM tl_iso_attribute WHERE type IN ('checkbox', 'select', 'radio') ORDER BY id");

        foreach ($rows as $attribute) {
            $multiple = match ($attribute['type']) {
                'checkbox' => 'name' !== $attribute['optionsSource'],
                'select' => ($attribute['multiple'] ?? false) && !($attribute['variant_option'] ?? false),
                default => false,
            };

            if (!$multiple && 'attribute' !== $attribute['optionsSource']) {
                continue;
            }

            $attributes[$attribute['field_name']] = [
                'id' => $attribute['id'],
                'options' => 'attribute' === $attribute['optionsSource'] ? StringUtil::deserialize($attribute['options'], true) : null,
                'multiple' => $multiple,
            ];
        }

        return $attributes;
    }

    private function getAttributeUpdates(array $attributes): array
    {
        $updates = [];

        foreach ($attributes as $field => $attribute) {
            if (null === $attribute['options']) {
                continue;
            }

            $options = $attribute['options'];
            $this->valueMap[$field] = [];

            // Store existing valid options first, so we know what exists when generating replacements
            foreach ($options as $option) {
                $value = (string) $option['value'];

                if ($value === $this->normalize($value)) {
                    $this->valueMap[$field][$value] = $value;
                }
            }

            foreach ($options as &$option) {
                $option['value'] = $this->getUniqueValue($field, (string) $option['value']);
            }

            unset($option);

            if ($options !== $attribute['options']) {
                $updates[] = ['tl_iso_attribute', $attribute['id'], ['options' => serialize($options)]];
            }
        }

        return $updates;
    }

    private function getProductUpdates(array $attributes): array
    {
        $productTable = $this->connection->createSchemaManager()->introspectTableByUnquotedName('tl_iso_product');
        $updates = [];

        foreach ($attributes as $field => $attribute) {
            if (!$productTable->hasColumn($field)) {
                continue;
            }

            $column = $this->connection->quoteIdentifier($field);
            $products = $this->connection->fetchAllKeyValue('SELECT id, '.$column.' FROM tl_iso_product ORDER BY id');

            foreach ($products as $id => $value) {
                if (null === $value || '' === $value) {
                    continue;
                }

                $values = $this->getValues($value, $attribute['multiple']);

                // Only convert unique values if there are options, otherwise we only convert serialized to CSV values
                if (null !== $attribute['options']) {
                    $values = array_map(fn ($entry) => $this->getUniqueValue($field, (string) $entry), $values);
                }

                $newValue = implode(',', $values);

                if ($newValue !== (string) $value) {
                    $updates[] = ['tl_iso_product', $id, [$field => $newValue]];
                }
            }
        }

        return $updates;
    }

    private function getProductCollectionUpdates(array $attributes): array
    {
        $schema = $this->connection->createSchemaManager();

        if (
            !$schema->tableExists('tl_iso_product_collection_item')
            || !$schema->introspectTableByUnquotedName('tl_iso_product_collection_item')->hasColumn('configuration')
        ) {
            return [];
        }

        $configurations = $this->connection->fetchAllKeyValue('SELECT id, configuration FROM tl_iso_product_collection_item ORDER BY id');
        $updates = [];

        foreach ($configurations as $id => $configuration) {
            $configuration = StringUtil::deserialize($configuration);

            if (!\is_array($configuration)) {
                continue;
            }

            $original = $configuration;

            foreach ($attributes as $field => $attribute) {
                if (null === $attribute['options'] || !isset($configuration[$field]) || '' === $configuration[$field]) {
                    continue;
                }

                $value = $configuration[$field];
                $values = array_map(fn ($entry) => $this->getUniqueValue($field, (string) $entry), (array) $value);
                $configuration[$field] = \is_array($value) ? $values : $values[0];
            }

            if ($configuration !== $original) {
                $updates[] = ['tl_iso_product_collection_item', $id, ['configuration' => serialize($configuration)]];
            }
        }

        return $updates;
    }

    private function getValues(mixed $value, bool $multiple): array
    {
        if (null === $value || '' === $value) {
            return [];
        }

        if (!$multiple) {
            return [(string) $value];
        }

        $decoded = StringUtil::deserialize($value);

        // Isotope 2 also serialized selections containing numeric option IDs.
        return \is_array($decoded) ? $decoded : explode(',', (string) $value);
    }

    private function getUniqueValue(string $field, string $value): string
    {
        if (isset($this->valueMap[$field][$value])) {
            return $this->valueMap[$field][$value];
        }

        $base = $this->normalize($value);
        $replacement = $base;

        for ($i = 2; \in_array($replacement, $this->valueMap[$field], true); ++$i) {
            $suffix = '_'.$i;
            $replacement = substr($base, 0, 64 - \strlen($suffix)).$suffix;
        }

        return $this->valueMap[$field][$value] = $replacement;
    }

    private function normalize(string $value): string
    {
        $normalized = preg_replace('/[^A-Za-z0-9_]/u', '_', trim($value));

        if (null === $normalized) {
            throw new \UnexpectedValueException('Cannot migrate an option value containing invalid UTF-8.');
        }

        return '' === $normalized ? 'option' : substr($normalized, 0, 64);
    }
}
