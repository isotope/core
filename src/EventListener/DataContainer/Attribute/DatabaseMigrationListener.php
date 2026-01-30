<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Attribute;

use Ausi\SlugGenerator\SlugOptions;
use Contao\Controller;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Slug\Slug;
use Contao\DataContainer;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\Database\DatabaseUpdater;
use Isotope\CoreBundle\Database\ProductDcaExtractor;
use Isotope\CoreBundle\Exception\InvalidArgumentException;

class DatabaseMigrationListener
{
    public function __construct(
        private readonly Connection $connection,
        private readonly Slug $slug,
        private readonly ProductDcaExtractor $productDcaExtractor,
        private readonly DatabaseUpdater $databaseUpdater,
    ) {
    }

    /**
     * @param DataContainer|mixed|null $dc
     */
    #[AsHook('getAttributesFromDca')]
    public function disableFieldName(array $attributes, mixed $dc): array
    {
        if (
            $dc instanceof DataContainer
            && 'tl_iso_attribute' === $dc->table
            && 'field_name' === $dc->field
            && '' !== (string) $dc->value
        ) {
            $attributes['disabled'] = true;
            $attributes['mandatory'] = false;
        }

        return $attributes;
    }

    /**
     * Prevent system columns or reserved keywords from being added as attribute.
     */
    #[AsCallback('tl_iso_attribute', 'fields.field_name.save')]
    public function validateFieldName(string $value): string
    {
        Controller::loadDataContainer('tl_iso_product');

        $options = (new SlugOptions())
            ->setValidChars('A-Za-z0-9_')
            ->setDelimiter('')
            ->setLocale($GLOBALS['TL_LANGUAGE'])
        ;

        $slugged = $this->slug->generate(StringUtil::prepareSlug($value), $options);

        if ($slugged !== $value) {
            throw new InvalidArgumentException(\sprintf($GLOBALS['TL_LANG']['ERR']['invalidColumn'], $value, $slugged));
        }

        if (
            ($GLOBALS['TL_DCA']['tl_iso_product']['fields'][$value]['attributes']['systemColumn'] ?? false)
            || $this->connection->getDatabasePlatform()?->getReservedKeywordsList()->isKeyword($value)
        ) {
            throw new InvalidArgumentException(\sprintf($GLOBALS['TL_LANG']['ERR']['systemColumn'], $value));
        }

        return $value;
    }

    #[AsCallback('tl_iso_attribute', 'config.onsubmit')]
    public function updateField(): void
    {
        $this->productDcaExtractor->updateCache();
        $this->databaseUpdater->autoUpdateTables(['tl_iso_product']);
    }

    #[AsCallback('tl_iso_attribute', 'config.ondelete')]
    public function deleteField(DataContainer $dc): void
    {
        $record = $dc->getCurrentRecord();

        if (empty($record['field_name'])) {
            return;
        }

        $this->productDcaExtractor->deleteFromCache($record['field_name']);
    }
}
