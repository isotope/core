<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\ContentElement;

use Codefog\HasteBundle\Formatter;
use Contao\ContentModel;
use Contao\Controller;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Exception\BadRequestException;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\Date;
use Contao\Widget;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Attribute\Option\OptionsSource;
use Isotope\CoreBundle\Attribute\OptionsAttributeInterface;
use Isotope\CoreBundle\Model\ProductModel;
use Isotope\CoreBundle\Product\Category\CategoryFinder;
use Isotope\CoreBundle\Product\Query\Filter\EqualFilter;
use Isotope\CoreBundle\Product\Query\ProductQuery;
use Isotope\CoreBundle\Product\Query\Sorting;
use Isotope\CoreBundle\Util\StringUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsContentElement('iso_productfilter', category: 'isotope')]
class ProductFilterController extends AbstractContentElementController
{
    public function __construct(
        private readonly AttributeRegistry $attributeRegistry,
        private readonly Formatter $formatter,
        private readonly Connection $connection,
        private readonly TokenChecker $tokenChecker,
        private readonly TranslatorInterface $translator,
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        if ($request->attributes->get('_content') instanceof ProductModel && $model->iso_hide_list) {
            return new Response('');
        }

        $template->filterWidgets = [];
        $template->sortingOptions = [];
        $template->limitOptions = [];

        $filterKey = $model->iso_filterKey ?: '';
        $productQuery = $this->getProductQuery($request);

        if ($filterFields = StringUtil::csv($model->iso_filterFields)) {
            $template->filterWidgets = $this->getFilterWidgets(
                $filterFields,
                $this->getCurrentFilterValues($productQuery, $filterKey, $filterFields),
                $this->getCategories($model, $request),
                (bool) $model->iso_filterHideSingle,
            );
        }

        if ($sortingFields = StringUtil::csv($model->iso_sortingFields)) {
            $template->sortingOptions = $this->getSortingOptions(
                $sortingFields,
                $this->getCurrentSortingValue($productQuery, $filterKey, $sortingFields),
            );
        }

        if ($model->iso_enableLimit) {
            $template->limitOptions = $this->getLimitOptions($model->iso_perPage, $productQuery, $filterKey);
        }

        $this->applySearch($template, $model, $productQuery, $filterKey);

        $template->filter_key = $filterKey;

        return $template->getResponse();
    }

    private function getFilterWidgets(array $fields, array $current, array $categories, bool $hideSingle): array
    {
        $widgets = [];

        [$attributeTypes, $variantTypes] = $this->getProductTypeIdsByAttribute();
        Controller::loadDataContainer('tl_iso_product');

        foreach ($fields as $fieldName) {
            $widget = Widget::getAttributesFromDca($GLOBALS['TL_DCA']['tl_iso_product']['fields'][$fieldName], $fieldName, $current[$fieldName] ?? null, $fieldName, 'tl_iso_product');
            $values = $this->getUsedValuesForField($fieldName, $categories, $attributeTypes[$fieldName] ?? [], $variantTypes[$fieldName] ?? []);

            if ([] === $values) {
                continue;
            }

            $attribute = $this->attributeRegistry->get($fieldName);

            if ($attribute instanceof OptionsAttributeInterface) {
                if (OptionsSource::Name === $attribute->getOptionsSource()) {
                    $options = [];

                    if (\array_key_exists('1', $values)) {
                        $options['1'] = $GLOBALS['TL_LANG']['MSC']['yes'];
                    }

                    if (\array_key_exists('', $values)) {
                        $options[''] = $GLOBALS['TL_LANG']['MSC']['no'];
                    }

                    $widget['options'] = $options;
                } elseif (\is_array($widget['options'])) {
                    $widget['options'] = array_filter($widget['options'], static fn (array $option) => empty($option['value']) || \in_array($option['value'], $values, false));
                }
            }

            // Generate options from database values (e.g. for text fields)
            if (!\is_array($widget['options'] ?? null)) {
                $widget['options'] = array_map(static fn ($v) => ['value' => $v, 'label' => $v], $values);
            }

            foreach ($widget['options'] as $k => $option) {
                if ('' === $option['value']) {
                    $widget['blankOptionLabel'] = $option['label'];
                    unset($widget['options'][$k]);
                    continue;
                }

                if (\in_array($widget['rgxp'] ?? null, ['date', 'time', 'datim'], true)) {
                    try {
                        $objDate = new Date($option['label'], Date::getFormatFromRgxp($widget['rgxp']));
                        $widget['options'][$k]['label'] = $objDate->{$widget['rgxp']};
                    } catch (\OutOfBoundsException) {
                        // ignore if date could not be converted
                    }
                }

                $widget['options'][$k]['default'] = ($current[$fieldName] ?? null) === (string) $option['value'];
            }

            // Hide fields with just one option (if enabled)
            if ($hideSingle && \count($widget['options']) < 2) {
                continue;
            }

            $widgets[] = $widget;
        }

        return $widgets;
    }

    /**
     * @param array<string> $fields
     *
     * @return array<array{
     *     label: string,
     *     value: string,
     *     default: bool
     * }>
     */
    private function getSortingOptions(array $fields, Sorting|null $current): array
    {
        $options = [];

        foreach ($fields as $field) {
            [$asc, $desc] = $this->getSortingLabels($field);
            $isDefault = $current?->attribute === $field;

            $options[] = [
                'label' => $this->formatter->dcaLabel('tl_iso_product', $field).', '.$asc,
                'value' => $field.':ASC',
                'default' => $isDefault && !$current?->desc,
            ];

            $options[] = [
                'label' => $this->formatter->dcaLabel('tl_iso_product', $field).', '.$desc,
                'value' => $field.':DESC',
                'default' => $isDefault && $current?->desc,
            ];
        }

        return $options;
    }

    private function getLimitOptions(string $perPage, ProductQuery|null $productQuery, string $key): array
    {
        $options = [];
        $limits = array_unique(StringUtil::csv($perPage, intval(...), true));
        $current = $productQuery?->getLimit([$key]);

        if (null !== $current && !\in_array($current, $limits, true)) {
            throw new BadRequestException('Invalid limit configuration');
        }

        foreach ($limits as $limit) {
            $options[] = [
                'label' => 0 === $limit ? $this->translator->trans('MSC.filterAll', [], 'contao_default') : $limit,
                'value' => $limit,
                'default' => $limit === $current,
            ];
        }

        return $options;
    }

    private function applySearch(FragmentTemplate $template, ContentModel $model, ProductQuery|null $productQuery, string $key): void
    {
        $template->hasSearch = false;

        if (!$model->iso_enableSearch) {
            return;
        }

        $template->hasSearch = true;
        $template->keywords = implode(' ', $productQuery?->getSearches([$key]) ?? []);
    }

    /**
     * Returns an array of attribute values found in the product table.
     *
     * @return array<string>
     */
    private function getUsedValuesForField(string $field, array $categories, array $attributeTypes, array $variantTypes): array
    {
        if ([] === $attributeTypes && [] === $variantTypes) {
            return [];
        }

        $typeConditions = [];
        $join = '';
        $categoryWhere = '';
        $published = '';
        $time = Date::floorToMinute();
        $isPreviewMode = $this->tokenChecker->isPreviewMode();

        if (!$isPreviewMode) {
            $published = "
                AND tl_iso_product.published='1'
                AND (tl_iso_product.start='' OR tl_iso_product.start<'$time')
                AND (tl_iso_product.stop='' OR tl_iso_product.stop>'".($time + 60)."')
            ";
        }

        if ([] !== $attributeTypes) {
            $typeConditions[] = 'tl_iso_product.type IN ('.implode(',', $attributeTypes).')';
        }

        if ([] !== $variantTypes) {
            $typeConditions[] = 'translation.type IN ('.implode(',', $variantTypes).')';
            $join = 'LEFT OUTER JOIN tl_iso_product translation ON tl_iso_product.pid=translation.id';
            $categoryWhere = 'OR tl_iso_product.pid IN (
                                    SELECT product_id
                                    FROM tl_iso_product_category
                                    #WHERE pid IN ('.implode(',', $categories).')
                                )';

            if (!$isPreviewMode) {
                $published .= " AND (
                    tl_iso_product.pid=0 OR (
                        translation.published='1'
                        AND (translation.start='' OR translation.start<'$time')
                        AND (translation.stop='' OR translation.stop>'".($time + 60)."')
                    )
                )";
            }
        }

        $options = $this->connection->fetchFirstColumn("
            SELECT DISTINCT tl_iso_product.$field AS options
            FROM tl_iso_product
            $join
            WHERE
                tl_iso_product.language=''
                AND tl_iso_product.$field!=''
                ".$published.'
                AND (
                    tl_iso_product.id IN (
                        SELECT product_id
                        FROM tl_iso_product_category
                        WHERE pid IN ('.implode(',', $categories).")
                    )
                    $categoryWhere
                )
                AND (
                    ".implode(' OR ', $typeConditions).'
                )
        ');

        $isCsv = true === ($GLOBALS['TL_DCA']['tl_iso_product']['fields'][$field]['eval']['multiple'] ?? null)
            && !empty($GLOBALS['TL_DCA']['tl_iso_product']['fields'][$field]['eval']['csv'] ?? null);

        return array_unique(array_merge(...array_map(
            static fn ($v) => $isCsv ? explode($GLOBALS['TL_DCA']['tl_iso_product']['fields'][$field]['eval']['csv'], (string) $v) : \Contao\StringUtil::deserialize($v, true),
            $options,
        )));
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
            foreach (\Contao\StringUtil::deserialize($type['attributes'], true) as $attribute => $config) {
                if ($config['enabled'] ?? false) {
                    $attributes[$attribute][] = $type['id'];
                }
            }

            if ($type['variants']) {
                foreach (\Contao\StringUtil::deserialize($type['variant_attributes'], true) as $attribute => $config) {
                    if ($config['enabled'] ?? false) {
                        $variantAttributes[$attribute][] = $type['id'];
                    }
                }
            }
        }

        return [$attributes, $variantAttributes];
    }

    private function getSortingLabels(string $field): array
    {
        return match ($GLOBALS['TL_DCA']['tl_iso_product']['fields'][$field]['eval']['rgxp'] ?? null) {
            'price',
            'digit' => [$GLOBALS['TL_LANG']['MSC']['low_to_high'], $GLOBALS['TL_LANG']['MSC']['high_to_low']],
            'date',
            'time',
            'datim' => [$GLOBALS['TL_LANG']['MSC']['old_to_new'], $GLOBALS['TL_LANG']['MSC']['new_to_old']],
            default => [$GLOBALS['TL_LANG']['MSC']['a_to_z'], $GLOBALS['TL_LANG']['MSC']['z_to_a']],
        };
    }

    private function getCurrentFilterValues(ProductQuery|null $productQuery, string $key, array $fields): array
    {
        $current = [];

        foreach ($productQuery?->getFilters([$key]) ?? [] as $filter) {
            if (!$filter instanceof EqualFilter || !\in_array($filter->getAttributeName(), $fields, true) || 1 !== \count($filter->getValue())) {
                continue;
            }

            $current[$filter->getAttributeName()] = $filter->getValue()[0];
        }

        return $current;
    }

    private function getCurrentSortingValue(ProductQuery|null $productQuery, string $key, array $fields): Sorting|null
    {
        $current = $productQuery?->getSortings([$key]) ?? [];

        if ([] === $current) {
            return null;
        }

        if (\count($current) > 1 || !\in_array($current[0]->attribute, $fields, true)) {
            return null;
        }

        return $current[0];
    }
}
