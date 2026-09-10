<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Document;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Twig\Finder\FinderFactory;
use Contao\DC_Table;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsCallback('tl_iso_document', 'fields.customTpl.options')]
class TemplateOptionsListener
{
    public function __construct(
        private readonly FinderFactory $finderFactory,
        private readonly Connection $connection,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function __invoke(DC_Table $dc): array
    {
        $overrideAll = $this->isOverrideAll();

        $type = $overrideAll
            ? $this->getCommonOverrideAllType($dc->table)
            : $dc->getActiveRecord()['type'] ?? null;

        if (null === $type) {
            // Add a blank option that allows to reset all custom templates to the default
            // one when in "overrideAll" mode
            return $overrideAll ? ['' => '-'] : [];
        }

        return $this->finderFactory
            ->create()
            ->identifier('isotope/document')
            ->extension('html.twig')
            ->withVariants()
            ->excludePartials()
            ->asTemplateOptions()
        ;
    }

    private function isOverrideAll(): bool
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request || !$request->query->has('act')) {
            return false;
        }

        return 'overrideAll' === $request->query->get('act');
    }

    /**
     * Returns the type that all currently edited items are sharing or null if there
     * is no common type.
     */
    private function getCommonOverrideAllType(string $table): string|null
    {
        $affectedIds = $this->requestStack->getSession()->all()['CURRENT']['IDS'] ?? [];
        $table = $this->connection->quoteSingleIdentifier($table);

        $result = $this->connection->executeQuery(
            "SELECT type FROM $table WHERE id IN (?) GROUP BY type LIMIT 2",
            [$affectedIds],
            [ArrayParameterType::STRING],
        );

        if (1 !== $result->rowCount()) {
            return null;
        }

        return $result->fetchOne();
    }
}
