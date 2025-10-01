<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\FrontendModule;

use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\Database;
use Contao\ModuleModel;
use Contao\StringUtil;
use Isotope\CoreBundle\ModelType\ProductRegistry;
use Isotope\CoreBundle\Product\ProductInterface;
use Isotope\CoreBundle\Product\Render\DefaultProductRenderer;
use Isotope\CoreBundle\Product\Render\RenderConfig;
use Isotope\Model\Product;
use Isotope\Model\RelatedProduct;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule('iso_relatedproducts', category: 'isotope', template: 'frontend_module/iso_productlist')]
class RelatedProductsController extends AbstractFrontendModuleController
{
    public function __construct(
        private readonly ProductRegistry $productRegistry,
        private readonly DefaultProductRenderer $productRenderer,
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        $categories = StringUtil::deserialize($model->iso_related_categories, true);
        $product = $request->attributes->get('_content');

        if (!$product instanceof ProductInterface || [] === $categories) {
            return new Response();
        }

        $products = [];
        $models = $this->findProducts($product, $categories, (string) $model->iso_listingSortField, (string) $model->iso_listingSortDirection);

        if ($model->iso_hide_empty && [] === $models) {
            return new Response('');
        }

        foreach ($models as $productModel) {
            $product = $this->productRegistry->get($productModel);
            $products[] = $this->productRenderer->render(
                $product,
                RenderConfig::createList($model, $product),
                $request
            );
        }

        $template->products = $products;

        return $template->getResponse();
    }

    private function findProducts(ProductInterface $product, array $categories, string $sortingField, string $sortingDirection)
    {
        $productIds = [];

        /** @var RelatedProduct[] $relatedProducts */
        $relatedProducts = RelatedProduct::findByProductAndCategories($product, $categories);

        if (null !== $relatedProducts) {
            foreach ($relatedProducts as $category) {
                $ids = StringUtil::trimsplit(',', $category->products);

                if (\is_array($ids) && 0 !== \count($ids)) {
                    $productIds = array_unique(array_merge($productIds, $ids));
                }
            }
        }

        if (0 === \count($productIds)) {
            return [];
        }

        $columns = [Product::getTable() . '.id IN (' . implode(',', array_map('intval', $productIds)) . ')'];
        $options = ['order' => Database::getInstance()->findInSet(Product::getTable() . '.id', $productIds)];

        if ($sortingField != '') {
            $options['sorting'] = [$sortingField => 'DESC' === $sortingDirection ? 'desc' : 'asc'];
        }

        $objProducts = Product::findAvailableBy($columns, [], $options);

        return (null === $objProducts) ? [] : $objProducts->getModels();
    }
}
