<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Contao\StringUtil;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Table;
use Isotope\CoreBundle\Migration\Event\ModuleToElementMigrationEvent;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @experimental
 */
#[AutoconfigureTag('contao.migration', ['priority' => -100])]
abstract class AbstractModuleToElementMigration extends AbstractMigration
{
    public function __construct(
        private readonly Connection $connection,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
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
            [array_keys($this->getMapping())],
            [ArrayParameterType::STRING],
        );

        return $moduleCount > 0;
    }

    public function run(): MigrationResult
    {
        $typeMap = $this->getMapping();
        $table = $this->connection->createSchemaManager()->introspectTableByUnquotedName('tl_content');

        $modules = $this->connection->fetchAllAssociative(
            'SELECT * FROM tl_module WHERE type IN (?)',
            [array_keys($typeMap)],
            [ArrayParameterType::STRING],
        );

        $idMap = [];

        foreach ($modules as $module) {
            $idMap[$module['id']] = $this->createContentElement($typeMap[$module['type']], $module, $table);
        }

        $this->updateLayouts($idMap);

        return $this->createResult(true);
    }

    abstract protected function getMapping(): array;

    private function createContentElement(string $type, array $data, Table $table): int
    {
        $tableData = [];
        $jsonData = [];

        // Remove template fields that cannot work after migration
        unset($data['customTpl'], $data['iso_list_layout'], $data['iso_reader_layout']);

        foreach ($data as $key => $value) {
            if ($table->hasColumn($key)) {
                $tableData[$key] = $value;
            } else {
                $jsonData[$key] = $value;
            }
        }

        unset($tableData['id']);
        $tableData['type'] = $type;
        $tableData['ptable'] = 'tl_theme';
        $tableData['title'] = $data['name'];

        $event = $this->eventDispatcher->dispatch(new ModuleToElementMigrationEvent($data, $tableData, $jsonData));

        $this->connection->insert('tl_content', [
            ...$event->tableData,
            'jsonData' => json_encode($event->jsonData, JSON_THROW_ON_ERROR),
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
