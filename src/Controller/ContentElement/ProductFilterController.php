<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\ContentElement;

use Codefog\HasteBundle\Formatter;
use Contao\ContentModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Exception\BadRequestException;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\Widget;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Attribute\Filter\FilterOptionsProvider;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\Finder\ProductFinder;
use Isotope\CoreBundle\Product\Finder\ProductFinderFactory;
use Isotope\CoreBundle\Product\Query\Filter\EqualFilter;
use Isotope\CoreBundle\Product\Query\ProductQuery;
use Isotope\CoreBundle\Product\Query\ProductQueryFactory;
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
        private readonly ProductFinderFactory $finderFactory,
        private readonly ProductQueryFactory $queryFactory,
        private readonly FilterOptionsProvider $optionsProvider,
        private readonly TranslatorInterface $translator,
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        if ($request->attributes->get('_content') instanceof CoreProduct && $model->iso_hide_list) {
            return new Response('');
        }

        $template->filterWidgets = [];
        $template->sortingOptions = [];
        $template->limitOptions = [];

        $filterKey = $model->iso_filterKey ?: '';
        $productQuery = $this->getProductQuery($request);

        if ($filterFields = StringUtil::csv($model->iso_filterFields)) {
            $baseFilters = $model->iso_filterQuery ? $this->queryFactory->parseFilters($model->iso_filterQuery, $this->attributeRegistry->getNames()) : [];
            $finder = $this->finderFactory->create($this->getCategories($model, $request), $request->getLocale(), $baseFilters);

            $template->filterWidgets = $this->getFilterWidgets(
                $filterFields,
                $this->getCurrentFilterValues($productQuery, $filterKey, $filterFields),
                $finder,
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

    private function getFilterWidgets(array $fields, array $current, ProductFinder $finder, bool $hideSingle): array
    {
        $widgets = [];

        foreach ($this->optionsProvider->getAttributes($fields, $finder) as $attribute) {
            $name = $attribute->getName();
            $field = $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$name];

            // Options have already been resolved by the provider.
            unset($field['options_callback'], $field['foreignKey']);
            $field['options'] = [];
            $widget = Widget::getAttributesFromDca($field, $name, $current[$name] ?? null, $name, 'tl_iso_product');
            $widget['options'] = [];

            foreach ($attribute->getOptions() as $option) {
                if ('' === $option->getValue()) {
                    $widget['blankOptionLabel'] = $option->getLabel();
                    continue;
                }

                $widget['options'][] = [
                    'value' => $option->getValue(),
                    'label' => $option->getLabel(),
                    'cssClass' => $option->getCssClass(),
                    'default' => ($current[$name] ?? null) === $option->getValue(),
                ];
            }

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
                'default' => $isDefault && !$current->desc,
            ];

            $options[] = [
                'label' => $this->formatter->dcaLabel('tl_iso_product', $field).', '.$desc,
                'value' => $field.':DESC',
                'default' => $isDefault && $current->desc,
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
