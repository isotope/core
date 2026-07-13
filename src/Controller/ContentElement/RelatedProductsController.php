<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Isotope\CoreBundle\Model\RelatedProductModel;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\DataCollector\ProductDto;
use Isotope\CoreBundle\Product\DataCollector\ProductDtoCollection;
use Isotope\CoreBundle\Product\ProductBuilder;
use Isotope\CoreBundle\Product\ProductCollection;
use Isotope\CoreBundle\Product\Render\ListView;
use Isotope\CoreBundle\Store\StoreManager;
use Isotope\CoreBundle\Util\StringUtil;
use Ramsey\Collection\Sort;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement('iso_relatedproducts', category: 'isotope')]
class RelatedProductsController extends AbstractContentElementController
{
    public function __construct(
        private readonly ProductBuilder $productBuilder,
        private readonly StoreManager $storeManager,
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $categories = StringUtil::csv($model->iso_related_categories, intval(...));
        $product = $request->attributes->get('_content');

        if (!$product instanceof CoreProduct || !$categories) {
            return new Response();
        }

        $products = $this->findProducts($product, $categories, (string) $model->iso_listingSortField, (string) $model->iso_listingSortDirection);

        if ($model->iso_hide_empty && $products->isEmpty()) {
            return new Response('');
        }

        $this->tagResponse($products);

        $template->products = $products;
        $template->view = ListView::createFromContent($model);

        return $template->getResponse();
    }

    private function findProducts(CoreProduct $product, array $categories, string $sortingField, string $sortingDirection): ProductCollection
    {
        $productIds = [];

        $relatedProducts = RelatedProductModel::findByProductAndCategories($product, $categories);

        if (null !== $relatedProducts) {
            foreach ($relatedProducts as $category) {
                $ids = StringUtil::csv($category->products, intval(...));

                if (\is_array($ids) && 0 !== \count($ids)) {
                    $productIds = array_unique(array_merge($productIds, $ids));
                }
            }
        }

        $dtoCollection = new ProductDtoCollection(array_map(static fn (int|string $id) => new ProductDto('tl_iso_product.'.$id), $productIds));

        $products = $this->productBuilder->build($dtoCollection, $this->storeManager->getCurrentContext());
        $products = $products->filter(static fn (CoreProduct $product) => $product->isAvailable());

        if ($sortingField) {
            $products = $products->sort($sortingField, 'DESC' === $sortingDirection ? Sort::Descending : Sort::Ascending);
        }

        return $products;
    }
}
