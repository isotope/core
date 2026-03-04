<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Contao\StringUtil;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Table;

/**
 * @internal
 */
class ModuleToElementMigration extends AbstractMigration
{
    private const array MAPPING = [
        'iso_productlist' => [],
        'iso_productreader' => [],
        'iso_cart' => [],
        'iso_configswitcher' => [],
        'iso_orderdetails' => [],
        'iso_productfilter' => [],
        'iso_categoryfilter' => [],
        'iso_relatedproducts' => [],
        'iso_checkout' => [],
    ];

    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (
            !$schemaManager->tablesExist(['tl_module', 'tl_content'])
            || !$schemaManager->introspectTableByUnquotedName('tl_content')->hasColumn('jsonData')
        ) {
            return false;
        }

        $moduleCount = $this->connection->fetchOne(
            'SELECT COUNT(*) FROM tl_module WHERE type IN (?)',
            [array_keys(self::MAPPING)],
            [ArrayParameterType::STRING],
        );

        return $moduleCount > 0;
    }

    public function run(): MigrationResult
    {
        $table = $this->connection->createSchemaManager()->introspectTableByUnquotedName('tl_content');

        $modules = $this->connection->fetchAllAssociative(
            'SELECT * FROM tl_module WHERE type IN (?)',
            [array_keys(self::MAPPING)],
            [ArrayParameterType::STRING],
        );

        $mapping = [];

        foreach ($modules as $module) {
            $mapping[$module['id']] = $this->createContentElement($module, $table);
        }

        $this->updateLayouts($mapping);

        return $this->createResult(true);
    }

    private function createContentElement(array $data, Table $table): int
    {
        $tableData = [];
        $jsonData = [];

        foreach ($data as $key => $value) {
            if ($table->hasColumn($key)) {
                $tableData[$key] = $value;
            } else {
                $jsonData[$key] = $value;
            }
        }

        unset($tableData['id']);
        $tableData['ptable'] = 'tl_theme';
        $tableData['title'] = $data['name'];

        $this->connection->insert('tl_content', [
            ...$tableData,
            'jsonData' => json_encode($jsonData, JSON_THROW_ON_ERROR),
        ]);

        $contentId = $this->connection->lastInsertId();

        $this->connection->executeStatement(
            "UPDATE tl_content SET type='alias', cteAlias=? WHERE type='module' AND module=?",
            [$contentId, $data['id']],
        );

        $this->connection->delete('tl_module', ['id' => $data['id']]);

        return (int) $contentId;
    }

    private function updateLayouts(array $mapping): void
    {
        if (!$this->connection->createSchemaManager()->tableExists('tl_layout')) {
            return;
        }

        $layouts = $this->connection->fetchAllKeyValue('SELECT id, modules FROM tl_layout');

        foreach ($layouts as $id => $modules) {
            $changed = false;
            $modules = StringUtil::deserialize($modules, true);

            foreach ($modules as $k => $module) {
                if (!isset($mapping[$module['mod']])) {
                    continue;
                }

                $modules[$k]['mod'] = 'content-'.$mapping[$module['mod']];
                $changed = true;
            }

            if ($changed) {
                $this->connection->update('tl_layout', ['modules' => serialize($modules)], ['id' => $id]);
            }
        }
    }
}
