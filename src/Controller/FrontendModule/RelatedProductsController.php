<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\FrontendModule;

use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\ModuleModel;
use Contao\StringUtil;
use Isotope\CoreBundle\Model\RelatedProduct;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\DataCollector\ProductDto;
use Isotope\CoreBundle\Product\DataCollector\ProductDtoCollection;
use Isotope\CoreBundle\Product\ProductBuilder;
use Isotope\CoreBundle\Product\ProductCollection;
use Isotope\CoreBundle\Product\Render\ListView;
use Isotope\CoreBundle\Store\StoreManager;
use Ramsey\Collection\Sort;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule('iso_relatedproducts', category: 'isotope', template: 'frontend_module/iso_productlist')]
class RelatedProductsController extends AbstractFrontendModuleController
{
    public function __construct(
        private readonly ProductBuilder $productBuilder,
        private readonly StoreManager $storeManager,
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        $categories = StringUtil::deserialize($model->iso_related_categories, true);
        $product = $request->attributes->get('_content');

        if (!$product instanceof CoreProduct || [] === $categories) {
            return new Response();
        }

        $products = $this->findProducts($product, $categories, (string) $model->iso_listingSortField, (string) $model->iso_listingSortDirection);

        if ($model->iso_hide_empty && $products->isEmpty()) {
            return new Response('');
        }

        $template->products = $products;
        $template->view = ListView::createFromModule($model);

        return $template->getResponse();
    }

    private function findProducts(CoreProduct $product, array $categories, string $sortingField, string $sortingDirection): ProductCollection
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

        $dtoCollection = new ProductDtoCollection(array_map(static fn (string|int $id) => new ProductDto('tl_iso_product.'.$id), $productIds));

        $products = $this->productBuilder->build($dtoCollection, $this->storeManager->getCurrentContext());
        $products = $products->filter(static fn (CoreProduct $product) => $product->isAvailable());

        if ($sortingField) {
            $products = $products->sort($sortingField, 'DESC' === $sortingDirection ? Sort::Descending : Sort::Ascending);
        }

        return $products;

        //$columns = [Product::getTable() . '.id IN (' . implode(',', array_map('intval', $productIds)) . ')'];
        //$options = ['order' => Database::getInstance()->findInSet(Product::getTable() . '.id', $productIds)];
        //
        //if ($sortingField != '') {
        //    $options['sorting'] = [$sortingField => 'DESC' === $sortingDirection ? 'desc' : 'asc'];
        //}
        //
        //$objProducts = Product::findBy($columns, [], $options);
        //
        //return (null === $objProducts) ? [] : $objProducts->getModels();
    }
}
