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
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\DataCollector\ProductDto;
use Isotope\CoreBundle\Product\DataCollector\ProductDtoCollection;
use Isotope\CoreBundle\Product\ProductBuilder;
use Isotope\CoreBundle\Product\ProductCollection;
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
        private readonly Connection $connection,
        private readonly AttributeRegistry $attributeRegistry,
        private readonly ProductQueryFactory $productQueryFactory,
        private readonly TokenChecker $tokenChecker,
        private readonly ProductBuilder $productBuilder,
        private readonly StoreManager $storeManager,
        private readonly PaginationFactoryInterface $paginationFactory,
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        if ($request->attributes->get('_content') instanceof CoreProduct && $model->iso_hide_list) {
            return new Response();
        }

        if ($model->iso_emptyFilter && null === $this->getProductQuery($request)) {
            $template->message = $model->iso_noFilter;
            $template->products = [];

            return $template->getResponse();
        }

        $filterKeys = [$model->iso_filterKey ?: ''];
        $products = $this->findProducts($model, $request, $filterKeys, $template);

        if ($model->iso_hide_empty && $products->isEmpty()) {
            return new Response('');
        }

        $this->tagResponse($products);

        $template->products = $products;
        $template->view = ListView::createFromContent($model);
        $template->filters = $filterKeys;

        return $template->getResponse();
    }

    /**
     * @return ProductCollection
     */
    private function findProducts(ContentModel $model, Request $request, array $filterKeys, FragmentTemplate $template): CollectionInterface
    {
        $categories = $this->getCategories($model, $request);

        // Cannot find products without having categories
        if ([] === $categories) {
            return new ProductCollection();
        }

        $qb = $this->prepareQueryBuilder($categories, $request->getLocale());

        $productQuery = $this->getProductQuery($request);

        if ($model->iso_filterQuery) {
            $productQuery = $productQuery ? clone $productQuery : $this->productQueryFactory->create();
            $productQuery->setFilters('cte'.$model->id, $this->productQueryFactory->parseFilters($model->iso_filterQuery, $this->attributeRegistry->getNames()));
            $filterKeys[] = 'cte'.$model->id;
        }

        if ($model->iso_listingSortField) {
            $productQuery = $productQuery ? clone $productQuery : $this->productQueryFactory->create();
            $productQuery->setSorting('cte'.$model->id, [new Sorting($model->iso_listingSortField, 'DESC' === $model->iso_listingSortDirection)]);
            $filterKeys[] = 'cte'.$model->id;
        }

        $productQuery?->applyFilterAndSorting($qb, $filterKeys);

        if ($searchFields = StringUtil::csv($model->iso_searchFields)) {
            $productQuery?->applySearch($qb, $searchFields, $filterKeys);
        }

        $template->pagination = $this->applyPagination($model, $request, $qb, $productQuery?->getLimit($filterKeys));

        $ids = $qb->fetchFirstColumn();

        $dtoCollection = new ProductDtoCollection(array_map(static fn (int|string $id) => new ProductDto('tl_iso_product.'.$id), $ids));

        $products = $this->productBuilder->build($dtoCollection, $this->storeManager->getCurrentContext());

        return $products->filter(static fn (CoreProduct $product) => $product->isAvailable());
    }

    private function prepareQueryBuilder(array $categories, string $locale): QueryBuilder
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select('IFNULL(parent.id, tl_iso_product.id)')
            ->from('tl_iso_product')
            ->where($qb->expr()->in('c.pid', $qb->createNamedParameter($categories, ArrayParameterType::INTEGER)))
            ->andWhere("tl_iso_product.language=''")
            ->leftJoin('tl_iso_product', 'tl_iso_product', 'translation', "tl_iso_product.id=translation.pid AND translation.language='$locale'")
            ->leftJoin('tl_iso_product', 'tl_iso_product', 'parent', 'tl_iso_product.pid=parent.id')
            ->leftJoin('parent', 'tl_iso_product', 'parent_translation', "parent.id=parent_translation.pid AND parent_translation.language='$locale'")
            ->leftJoin('tl_iso_product', 'tl_iso_product_category', 'c', 'IFNULL(parent.id, tl_iso_product.id)=c.product_id')
            ->groupBy('IFNULL(parent.id, tl_iso_product.id)')
        ;

        if (!$this->tokenChecker->isPreviewMode()) {
            $qb
                ->setParameter('time', time())
                ->andWhere('tl_iso_product.published=1')
                ->andWhere("tl_iso_product.start='' OR tl_iso_product.start < :time")
                ->andWhere("tl_iso_product.stop='' OR tl_iso_product.stop > :time")
                ->andWhere("tl_iso_product.pid=0 OR (parent.published=1 AND (parent.start='' OR parent.start < :time) AND (parent.stop='' OR parent.stop > :time))")
            ;
        }

        return $qb;
    }

    private function applyPagination(ContentModel $model, Request $request, QueryBuilder $qb, int|null $perPage): PaginationInterface|null
    {
        $limit = null;
        $offset = 0;

        if ($model->numberOfItems > 0) {
            $limit = (int) $model->numberOfItems;
        }

        $perPage ??= (int) $model->perPage;

        $total = (clone $qb)
            ->select('COUNT(DISTINCT IFNULL(parent.id, tl_iso_product.id))')
            ->resetGroupBy()
            ->resetOrderBy()
            ->fetchOne()
        ;

        if ($total < 1) {
            return null;
        }

        // Split the results
        if ($perPage > 0 && (!isset($limit) || $model->numberOfItems > $perPage)) {
            // Adjust the overall limit
            if (isset($limit)) {
                $total = min($limit, $total);
            }

            $param = '_p'.$model->id;

            try {
                $pagination = $this->paginationFactory->create(new PaginationConfig($param, $total, $perPage));
            } catch (PageOutOfRangeException $e) {
                throw new PageNotFoundException('Page not found: '.$request->getRequestUri(), previous: $e);
            }

            // Set limit and offset
            $limit = $pagination->getPerPage();
            $offset = $pagination->getOffset();

            // Overall limit
            if ($offset + $limit > $total) {
                $limit = $total - $offset;
            }
        }

        $qb
            ->setFirstResult($offset)
            ->setMaxResults($limit)
        ;

        return $pagination ?? null;
    }
}
