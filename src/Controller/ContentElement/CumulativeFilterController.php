<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\ContentElement;

use Codefog\HasteBundle\UrlParser;
use Contao\ContentModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\PageModel;
use Contao\StringUtil;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Attribute\Filter\FilterOptionsProvider;
use Isotope\CoreBundle\Attribute\Option\Option;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\Finder\ProductFinderFactory;
use Isotope\CoreBundle\Product\Query\Filter\EqualFilter;
use Isotope\CoreBundle\Product\Query\Filter\FilterInterface;
use Isotope\CoreBundle\Product\Query\ProductQueryFactory;
use Isotope\CoreBundle\Product\Query\Sorting;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement('iso_cumulativefilter', category: 'isotope')]
class CumulativeFilterController extends AbstractContentElementController
{
    public function __construct(
        private readonly FilterOptionsProvider $optionsProvider,
        private readonly ProductFinderFactory $finderFactory,
        private readonly ProductQueryFactory $queryFactory,
        private readonly AttributeRegistry $attributeRegistry,
        private readonly UrlParser $urlParser,
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $configuration = StringUtil::deserialize($model->iso_cumulativeFields, true);

        if ([] === $configuration || ($model->iso_hide_list && $request->attributes->get('_content') instanceof CoreProduct)) {
            return new Response('');
        }

        $categories = $this->getCategories($model, $request);
        $key = $model->iso_filterKey ?: '';
        $query = $this->getProductQuery($request) ?? $this->queryFactory->create();
        $filters = $query->getFilters([$key]);
        $configByAttribute = array_column($configuration, null, 'attribute');
        $baseFilters = $model->iso_filterQuery ? $this->queryFactory->parseFilters($model->iso_filterQuery, $this->attributeRegistry->getNames()) : [];
        $finder = $this->finderFactory->create($categories, $request->getLocale(), $baseFilters);
        $attributes = $this->optionsProvider->getAttributes(array_keys($configByAttribute), $finder);
        $finder = $finder->withFilters($filters);

        $canCount = !array_any(array_keys($configByAttribute), static fn (string $attribute) => $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$attribute]['attributes']['dynamic'] ?? false);
        $currentCount = null;
        $groups = [];
        $showClear = false;
        $target = $model->jumpTo ? PageModel::findById($model->jumpTo)?->getFrontendUrl() : null;
        $baseUrl = $this->urlParser->removeQueryStringCallback(
            static fn (mixed $value, int|string $name) => !preg_match('/^_p\d+$/', (string) $name),
            $target ?? $request->getUri(),
        );

        foreach ($attributes as $attributeOptions) {
            if ($model->iso_filterHideSingle && $attributeOptions->getOptions()->filter(static fn (Option $option) => '' !== $option->getValue())->count() < 2) {
                continue;
            }

            $attribute = $attributeOptions->getName();
            $config = $configByAttribute[$attribute];
            $queryType = $config['queryType'];
            $countType = $config['matchCount'];
            $multiple = (bool) ($GLOBALS['TL_DCA']['tl_iso_product']['fields'][$attribute]['eval']['multiple'] ?? false);
            $items = [];
            $groupActive = false;

            foreach ($attributeOptions->getOptions() as $option) {
                $value = $option->getValue();

                if ('' === $value || '-' === $value) {
                    continue;
                }

                $active = $this->isFilterActive($filters, $attribute, $value);
                $nextFilters = $this->toggleFilter($filters, $attribute, $value, $queryType, $multiple);
                $count = null;

                if (!$active && $canCount && \in_array($countType, ['all', 'new'], true)) {
                    $count = $finder->withFilters($nextFilters)->count();

                    if ('new' === $countType && [] !== $filters) {
                        $currentCount ??= $finder->count();
                        $count -= $currentCount;
                    }
                }

                $nextQuery = clone $query;
                $nextQuery->setFilters($key, $nextFilters);

                if ($model->iso_listingSortField) {
                    $sortings = $query->getSortings([$key]);

                    if ($active) {
                        $sortings = array_filter($sortings, static fn (Sorting $sorting) => $sorting->attribute !== $model->iso_listingSortField);
                    } elseif ([] === $sortings) {
                        $sortings = [new Sorting($model->iso_listingSortField, 'DESC' === $model->iso_listingSortDirection)];
                    }

                    $nextQuery->setSorting($key, $sortings);
                }

                $params = [];
                parse_str((string) $nextQuery, $params);
                $href = $this->urlParser->addQueryString(http_build_query($params), $baseUrl);
                $label = $option->getLabel();
                $class = trim($option->getCssClass().($active ? ' active' : '').(0 === $count ? ' empty' : ''));
                $items[] = [
                    'href' => $href,
                    'label' => $label,
                    'count' => $count,
                    'is_active' => $active,
                    'class' => $class,
                ];
                $groupActive = $groupActive || $active;
            }

            $showClear = $showClear || $groupActive;

            $groups[$attribute] = [
                'label' => $attributeOptions->getLabel(),
                'items' => $items,
                'is_active' => $groupActive,
                'class' => $attribute.' query_'.$queryType.' count_'.$countType.($multiple ? ' multiple' : '').($groupActive ? ' trail' : ''),
            ];
        }

        $template->filters = $groups;
        $template->filter_key = $key;
        $template->show_clear = $showClear;
        $template->link_clear_all = $this->urlParser->removeQueryString(['_f'.$key, '_s'.$key], $baseUrl);

        return $template->getResponse();
    }

    /**
     * Toggle one option, preserving filters on other attributes and operators.
     *
     * @param array<FilterInterface> $filters
     *
     * @return array<FilterInterface>
     */
    private function toggleFilter(array $filters, string $attribute, string $value, string $queryType, bool $multiple): array
    {
        $values = [];
        $remaining = [];

        foreach ($filters as $filter) {
            if ($filter instanceof EqualFilter && $filter->getAttributeName() === $attribute) {
                array_push($values, ...$filter->getValue());
            } else {
                $remaining[] = $filter;
            }
        }

        if (\in_array($value, $values, true)) {
            $values = array_values(array_diff($values, [$value]));
        } elseif ('and' === $queryType && !$multiple) {
            $values = [$value];
        } else {
            $values[] = $value;
        }

        if ([] !== $values) {
            $remaining[] = new EqualFilter($attribute, implode('or' === $queryType ? '|' : ',', array_unique($values)));
        }

        return $remaining;
    }

    private function isFilterActive(array $filters, string $attribute, string $value): bool
    {
        return array_any($filters, static fn (FilterInterface $filter) => $filter instanceof EqualFilter
            && $filter->getAttributeName() === $attribute
            && \in_array($value, $filter->getValue(), true));
    }
}
