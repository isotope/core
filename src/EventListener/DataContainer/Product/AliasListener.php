<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Product;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Slug\Slug;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;
use Symfony\Contracts\Translation\TranslatorInterface;

class AliasListener
{
    public function __construct(
        private readonly Slug $slug,
        private readonly TranslatorInterface $translator,
        private readonly Connection $connection,
    ) {
    }

    #[AsCallback(table: 'tl_iso_product', target: 'fields.alias.save')]
    public function validateInput(string $value, DataContainer $dc): string
    {
        if ('' === $value) {
            return $value;
        }

        if (preg_match('/^[1-9]\d*$/', $value))
        {
            throw new \RuntimeException($this->translator->trans('ERR.aliasNumeric', [$value], 'contao_default'));
        }

        if ($this->aliasExists($value, (int) $dc->id))
        {
            throw new \RuntimeException($this->translator->trans('ERR.aliasExists', [$value], 'contao_default'));
        }

        return $value;
    }

    #[AsCallback(table: 'tl_iso_product', target: 'config.onbeforesubmit')]
    public function generateAlias(array $values, DataContainer $dc): array
    {
        if (!isset($values['alias'])) {
            return $values;
        }

        $currentRecord = array_merge($dc->getCurrentRecord(), $values);

        if ('' !== $currentRecord['alias']) {
            return $values;
        }

        $slugOptions = [];
        $validChars = $this->connection->fetchOne(
            'SELECT validAliasCharacters FROM tl_iso_producttype WHERE id=?',
            [$currentRecord['type']],
        );

        if ($validChars) {
            $slugOptions['validChars'] = $validChars;
        }

        $values['alias'] = $this->slug->generate(
            $currentRecord['name'] ?: $currentRecord['sku'] ?: $currentRecord['id'],
            $slugOptions,
            fn ($v) => $this->aliasExists($v, (int) $currentRecord['id'])
        );

        return $values;
    }

    private function aliasExists(string $alias, int $currentId): bool
    {
        return $this->connection->fetchOne(
            'SELECT COUNT(*) FROM tl_iso_product WHERE alias=? AND id!=?',
            [$alias, $currentId],
        ) > 0;
    }
}
