<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Content;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCallback('tl_content', 'fields.iso_filterModules.options')]
class FilterModuleOptionsListener
{
    public function __construct(
        private readonly array $types = [],
        private readonly Connection $connection,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function __invoke(): array
    {
        $options = [];
        $modules = $this->connection->fetchAllAssociative(
            'SELECT id, type, title FROM tl_content WHERE type IN (?)',
            [$this->types],
            [ArrayParameterType::STRING],
        );

        foreach ($modules as $module) {
            $transId = "CTE.$module[type].0";
            $label = $this->translator->trans($transId, [], 'contao_default');

            if ($transId === $label) {
                $label = $module['type'];
            }

            if ($module['title'] ?? null) {
                $label = $module['title'].' <span class="tl_gray">['.$label.']</span>';
            }

            $options[$module['id']] = $label;
        }

        return $options;
    }
}
