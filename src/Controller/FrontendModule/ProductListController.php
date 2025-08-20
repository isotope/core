<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\FrontendModule;

use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\ModelType\ProductRegistry;
use Isotope\CoreBundle\Product\ProductInterface;
use Isotope\CoreBundle\Product\Category\CategoryFinder;
use Isotope\CoreBundle\Product\Category\CategoryScope;
use Isotope\CoreBundle\Product\Render\ProductRendererInterface;
use Isotope\CoreBundle\Product\Render\RenderConfig;
use Isotope\Model\Product;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule('iso_productlist', category: 'isotope')]
class ProductListController extends AbstractFrontendModuleController
{
    public function __construct(
        private readonly Connection $connection,
        private readonly ProductRegistry $productRegistry,
        private readonly ProductRendererInterface $productRenderer,
        private readonly AttributeRegistry $attributeRegistry,
        private readonly CategoryFinder $categoryFinder,
        private readonly TokenChecker $tokenChecker,
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        if ($request->attributes->get('_content') instanceof ProductInterface && $model->iso_hide_list) {
            return new Response();
        }

        $products = [];
        $filterModules = array_map(static fn ($id) => 'fmd'.$id, StringUtil::deserialize($model->iso_filterModules, true));

        foreach ($this->findProducts($model, $request, $filterModules) as $productModel) {
            $product = $this->productRegistry->get($productModel);
            $products[] = $this->productRenderer->render(
                $product,
                RenderConfig::createList($model, $product),
                $request
            );
        }

        $template->products = $products;
        $template->filters = $filterModules;

        return $template->getResponse();
    }

    /**
     * @return array<Product>
     */
    private function findProducts(ModuleModel $model, Request $request, array $filterModules): array
    {
        $categories = $this->getCategories($model, $request);

        // Cannot find products without having categories
        if ([] === $categories) {
            return [];
        }

        $qb = $this->prepareQueryBuilder($categories, $request->getLocale());
        $this->getProductQuery($request)?->apply($qb, $filterModules);

        if ($model->numberOfItems > 0) {
            $qb->setMaxResults((int) $model->numberOfItems);
        }

        $ids = $qb->fetchFirstColumn();

        return Product::findMultipleByIds($ids)?->getModels() ?? [];
    }

    private function prepareQueryBuilder(array $categories, string $locale): QueryBuilder
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select(['IFNULL(parent.id, tl_iso_product.id)'])
            ->from('tl_iso_product')
            ->where($qb->expr()->in('c.pid', $qb->createNamedParameter($categories, ArrayParameterType::INTEGER)))
            ->leftJoin('tl_iso_product', 'tl_iso_product', 'translation', "tl_iso_product.id=translation.pid AND translation.language='$locale'")
            ->leftJoin('tl_iso_product', 'tl_iso_product', 'parent', 'tl_iso_product.pid=parent.id')
            ->leftJoin('parent', 'tl_iso_product', 'parent_translation', "parent.pid=parent_translation.id AND parent_translation.language='$locale'")
            ->leftJoin('tl_iso_product', 'tl_iso_product_category', 'c', 'IFNULL(parent.id, tl_iso_product.id)=c.product_id')
            ->groupBy(['tl_iso_product.id', 'translation.id'])
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
    private function getCategories(ModuleModel $model, Request $request): array
    {
        $pageModel = null;
        $product = $request->attributes->get('_content');

        if ($model->defineRoot && $model->rootPage > 0) {
            $pageModel = PageModel::findWithDetails($model->rootPage);
        }

        return $this->categoryFinder->getIds(
            CategoryScope::tryFrom($model->iso_category_scope) ?? $model->iso_category_scope,
            $pageModel ?: $this->getPageModel(),
            $product instanceof ProductInterface ? $product : null,
        );
    }
}
