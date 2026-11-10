<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Attribute\Filter;

use Contao\Controller;
use Contao\Date;
use Contao\StringUtil;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Attribute\Option\Option;
use Isotope\CoreBundle\Attribute\Option\OptionCollection;
use Isotope\CoreBundle\Attribute\Option\OptionsSource;
use Isotope\CoreBundle\Attribute\OptionsAttributeInterface;
use Isotope\CoreBundle\Product\Finder\ProductFinder;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @internal
 */
class FilterOptionsProvider
{
    public function __construct(
        private readonly AttributeRegistry $attributeRegistry,
        private readonly Connection $connection,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * @return list<FilterAttribute>
     */
    public function getAttributes(array $fields, ProductFinder $finder): array
    {
        $result = [];

        $attributeValues = $this->getAttributeValues($fields, $finder->getQueryBuilder());

        foreach ($fields as $fieldName) {
            $values = $attributeValues[$fieldName];

            if ([] === $values) {
                continue;
            }

            $field = $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$fieldName];
            $rgxp = $field['eval']['rgxp'] ?? null;
            $attribute = $this->attributeRegistry->get($fieldName);
            $options = [];

            if ($attribute instanceof OptionsAttributeInterface) {
                if (OptionsSource::Name === $attribute->getOptionsSource()) {
                    $stringValues = array_map(strval(...), $values);

                    foreach (['1' => 'MSC.yes', '0' => 'MSC.no'] as $value => $label) {
                        if (\in_array($value, $stringValues, true)) {
                            $options[] = new Option($value, $this->translator->trans($label, [], 'contao_default'));
                        }
                    }
                } else {
                    foreach ($attribute->getOptions() as $option) {
                        if (!$option->isGroup() && ('' === $option->getValue() || \in_array($option->getValue(), $values, false))) {
                            $options[] = $this->formatOption(clone $option, $rgxp);
                        }
                    }
                }
            } else {
                $options = array_map(fn ($value) => $this->formatOption(new Option((string) $value, (string) $value), $rgxp), $values);
            }

            $result[] = new FilterAttribute(
                $fieldName,
                (\is_array($field['label'] ?? null) ? $field['label'][0] : ($field['label'] ?? null)) ?? $fieldName,
                new OptionCollection(array_values($options)),
            );
        }

        return $result;
    }

    private function formatOption(Option $option, string|null $rgxp): Option
    {
        if ('' === $option->getValue() || !\in_array($rgxp, ['date', 'time', 'datim'], true)) {
            return $option;
        }

        try {
            $date = new Date((int) $option->getLabel(), Date::getFormatFromRgxp($rgxp));
            $option->setLabel($date->{$rgxp});
        } catch (\OutOfBoundsException) {
            // Ignore if date could not be converted.
        }

        return $option;
    }

    /**
     * @param list<string> $fields
     *
     * @return array<string, array<string>>
     */
    private function getAttributeValues(array $fields, QueryBuilder $baseQuery): array
    {
        Controller::loadDataContainer('tl_iso_product');
        [$attributeTypes, $variantTypes] = $this->getProductTypeIdsByAttribute();
        $values = [];

        foreach ($fields as $field) {
            $values[$field] = [];
            $eval = $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$field]['eval'] ?? [];

            if (($eval['multiple'] ?? false) && ',' !== ($eval['csv'] ?? null)) {
                continue;
            }

            $qb = clone $baseQuery;
            $types = [];

            if ($attributeTypes[$field] ?? []) {
                $types[] = $qb->expr()->in('tl_iso_product.type', $qb->createNamedParameter($attributeTypes[$field], ArrayParameterType::INTEGER));
            }

            if ($variantTypes[$field] ?? []) {
                $types[] = $qb->expr()->in('parent.type', $qb->createNamedParameter($variantTypes[$field], ArrayParameterType::INTEGER));
            }

            if ([] === $types) {
                continue;
            }

            $options = $qb
                ->select('DISTINCT tl_iso_product.'.$field)
                ->andWhere($qb->expr()->or(...$types))
                ->andWhere("CAST(tl_iso_product.$field AS CHAR)!=''")
                ->resetGroupBy()
                ->resetOrderBy()
                ->fetchFirstColumn()
            ;

            $values[$field] = $this->splitValues($field, $options);
        }

        return $values;
    }

    private function splitValues(string $field, array $options): array
    {
        if (!($GLOBALS['TL_DCA']['tl_iso_product']['fields'][$field]['eval']['multiple'] ?? false)) {
            return $options;
        }

        return array_values(array_unique(array_merge(...array_map(
            static fn ($value) => explode(',', (string) $value),
            $options,
        ))));
    }

    /**
     * Get product type IDs with attribute enabled.
     *
     * @return array{0: array<string, array<int>>, 1: array<string, array<int>>}
     */
    private function getProductTypeIdsByAttribute(): array
    {
        $attributes = [];
        $variantAttributes = [];

        $productTypes = $this->connection->fetchAllAssociative('SELECT id, attributes, variants, variant_attributes FROM tl_iso_producttype');

        foreach ($productTypes as $type) {
            foreach (StringUtil::deserialize($type['attributes'], true) as $attribute => $config) {
                if ($config['enabled'] ?? false) {
                    $attributes[$attribute][] = $type['id'];
                }
            }

            if ($type['variants']) {
                foreach (StringUtil::deserialize($type['variant_attributes'], true) as $attribute => $config) {
                    if ($config['enabled'] ?? false) {
                        $variantAttributes[$attribute][] = $type['id'];
                    }
                }
            }
        }

        return [$attributes, $variantAttributes];
    }
}
