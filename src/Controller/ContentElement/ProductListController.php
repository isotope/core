<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Exception\PageOutOfRangeException;
use Contao\CoreBundle\Pagination\PaginationConfig;
use Contao\CoreBundle\Pagination\PaginationFactoryInterface;
use Contao\CoreBundle\Pagination\PaginationInterface;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\DataCollector\ProductDto;
use Isotope\CoreBundle\Product\DataCollector\ProductDtoCollection;
use Isotope\CoreBundle\Product\Finder\ProductFinder;
use Isotope\CoreBundle\Product\Finder\ProductFinderFactory;
use Isotope\CoreBundle\Product\ProductBuilder;
use Isotope\CoreBundle\Product\ProductCollection;
use Isotope\CoreBundle\Product\Query\ProductQuery;
use Isotope\CoreBundle\Product\Query\ProductQueryFactory;
use Isotope\CoreBundle\Product\Query\Sorting;
use Isotope\CoreBundle\Product\Render\ListView;
use Isotope\CoreBundle\Store\StoreManager;
use Isotope\CoreBundle\Util\StringUtil;
use Ramsey\Collection\CollectionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement('iso_productlist', category: 'isotope')]
class ProductListController extends AbstractContentElementController
{
    public function __construct(
        private readonly AttributeRegistry $attributeRegistry,
        private readonly ProductFinderFactory $finderFactory,
        private readonly ProductQueryFactory $productQueryFactory,
        private readonly ProductBuilder $productBuilder,
        private readonly StoreManager $storeManager,
        private readonly PaginationFactoryInterface $paginationFactory,
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        if ($model->iso_hide_list && $request->attributes->get('_content') instanceof CoreProduct) {
            return new Response();
        }

        $filterKeys = [$model->iso_filterKey ?: ''];
        $template->products = [];
        $template->pagination = null;
        $template->view = ListView::createFromContent($model);
        $template->filters = $filterKeys;

        $productQuery = $this->getProductQuery($request);

        if ($model->iso_emptyFilter && null === $productQuery) {
            $template->message = $model->iso_noFilter;

            return $template->getResponse();
        }

        $products = null;
        $pagination = null;

        if ($finder = $this->createProductFinder($model, $request, $productQuery, $filterKeys)) {
            $pagination = $this->getPagination($model, $request, $finder, $productQuery?->getLimit($filterKeys));
            $products = $this->findProducts($finder, $pagination);
        }

        if (!$products || $products->isEmpty()) {
            return $model->iso_hide_empty ? new Response('') : $template->getResponse();
        }

        $this->tagResponse($products);

        $template->pagination = $pagination;
        $template->products = $products;

        return $template->getResponse();
    }

    private function createProductFinder(ContentModel $model, Request $request, ProductQuery|null $productQuery, array $queryKeys): ProductFinder|null
    {
        $categories = $this->getCategories($model, $request);

        if ([] === $categories) {
            return null;
        }

        $baseFilters = $model->iso_filterQuery ? $this->productQueryFactory->parseFilters($model->iso_filterQuery, $this->attributeRegistry->getNames()) : [];
        $baseSorting = $model->iso_listingSortField ? [new Sorting($model->iso_listingSortField, 'DESC' === $model->iso_listingSortDirection)] : [];

        return $this->finderFactory->create($categories, $request->getLocale(), $baseFilters, $baseSorting)
            ->withFilters($productQuery?->getFilters($queryKeys) ?? [])
            ->withSorting($productQuery?->getSortings($queryKeys) ?? [])
            ->withSearch(StringUtil::csv($model->iso_searchFields) ?? [], $productQuery?->getSearches($queryKeys) ?? [])
        ;
    }

    /**
     * @return ProductCollection
     */
    private function findProducts(ProductFinder $finder, PaginationInterface $pagination): CollectionInterface
    {
        [$offset, $end] = $pagination->getIndexRange();
        $ids = $finder->findIds($end - $offset, $offset);

        $dtoCollection = new ProductDtoCollection(array_map(static fn (int|string $id) => new ProductDto('tl_iso_product.'.$id), $ids));

        $products = $this->productBuilder->build($dtoCollection, $this->storeManager->getCurrentContext());

        return $products->filter(static fn (CoreProduct $product) => $product->isAvailable());
    }

    private function getPagination(ContentModel $model, Request $request, ProductFinder $finder, int|null $perPage): PaginationInterface
    {
        $total = $finder->count();

        if ($model->numberOfItems > 0) {
            $total = min((int) $model->numberOfItems, $total);
        }

        $perPage ??= (int) $model->perPage;
        $paginate = $total > 0 && $perPage > 0 && ($model->numberOfItems <= 0 || $model->numberOfItems > $perPage);
        $config = new PaginationConfig('_p'.$model->id, $total, $paginate ? $perPage : $total);

        if (!$paginate) {
            $config = $config->withCurrentPage(1);
        }

        try {
            return $this->paginationFactory->create($config);
        } catch (PageOutOfRangeException $e) {
            throw new PageNotFoundException('Page not found: '.$request->getRequestUri(), previous: $e);
        }
    }
}
