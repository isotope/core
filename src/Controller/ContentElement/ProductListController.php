<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\PageModel;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Product\Category\CategoryFinder;
use Isotope\CoreBundle\Product\Category\CategoryScope;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\DataCollector\ProductDto;
use Isotope\CoreBundle\Product\DataCollector\ProductDtoCollection;
use Isotope\CoreBundle\Product\Exception\InvalidCategoryScopeException;
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
        private readonly CategoryFinder $categoryFinder,
        private readonly ProductQueryFactory $productQueryFactory,
        private readonly TokenChecker $tokenChecker,
        private readonly ProductBuilder $productBuilder,
        private readonly StoreManager $storeManager,
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
        $products = $this->findProducts($model, $request, $filterKeys);

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
    private function findProducts(ContentModel $model, Request $request, array $filterKeys): CollectionInterface
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

        $productQuery?->apply($qb, $filterKeys);

        if ($searchFields = StringUtil::csv($model->iso_searchFields)) {
            $productQuery?->applySearch($qb, $searchFields, $filterKeys);
        }

        if ($model->numberOfItems > 0) {
            $qb->setMaxResults((int) $model->numberOfItems);
        }

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
            ->leftJoin('tl_iso_product', 'tl_iso_product', 'translation', "tl_iso_product.id=translation.pid AND translation.language='$locale'")
            ->leftJoin('tl_iso_product', 'tl_iso_product', 'parent', 'tl_iso_product.pid=parent.id')
            ->leftJoin('parent', 'tl_iso_product', 'parent_translation', "parent.pid=parent_translation.id AND parent_translation.language='$locale'")
            ->leftJoin('tl_iso_product', 'tl_iso_product_category', 'c', 'IFNULL(parent.id, tl_iso_product.id)=c.product_id')
            ->groupBy('tl_iso_product.id', 'translation.id')
        ;

        foreach ($this->attributeRegistry->getTranslatableFields() as $attribute) {
            $qb->addSelect("IFNULL(translation.$attribute, tl_iso_product.$attribute) AS $attribute");
        }

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

    /**
     * @return array<int>
     */
    private function getCategories(ContentModel $model, Request $request): array
    {
        $pageModel = null;
        $product = $request->attributes->get('_content');

        if ($model->defineRoot && $model->rootPage > 0) {
            $pageModel = PageModel::findWithDetails($model->rootPage);
        }

        if (
            null === $pageModel
            && $this->isBackendScope($request)
            && 'article' === $request->query->get('do')
            && 'tl_content' === $request->query->get('table')
        ) {
            // TODO: this won't work for nested elements
            $pageId = $this->connection->fetchOne('SELECT pid FROM tl_article WHERE id=?', [$request->query->getInt('id')]);
            $pageModel = PageModel::findWithDetails($pageId);

            if (!$pageModel) {
                return [];
            }
        }

        return $this->categoryFinder->getIds(
            CategoryScope::tryFrom($model->iso_category_scope) ?? $model->iso_category_scope,
            $pageModel ?: $this->getPageModel(),
            $product instanceof CoreProduct ? $product : null,
        );
    }
}
