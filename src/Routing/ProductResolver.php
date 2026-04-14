<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Routing;

use Contao\CoreBundle\Routing\Content\ContentUrlResolverInterface;
use Contao\CoreBundle\Routing\Content\ContentUrlResult;
use Contao\CoreBundle\Routing\PageFinder;
use Contao\PageModel;
use Isotope\CoreBundle\Model\Product;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\DataCollector\ProductDto;
use Isotope\CoreBundle\Product\ProductBuilder;
use Isotope\CoreBundle\Store\StoreManager;
use Isotope\CoreBundle\Util\ProductCategoryUtil;

class ProductResolver implements ContentUrlResolverInterface
{
    public function __construct(
        private readonly ProductCategoryUtil $productCategoryUtil,
        private readonly PageFinder $pageFinder,
        private readonly ProductBuilder $productBuilder,
        private readonly StoreManager $storeManager,
    ) {
    }

    public static function canLinkToPage(PageModel|null $pageModel): bool
    {
        return 'iso_products' === $pageModel?->type && 'none' !== $pageModel?->iso_readerMode;
    }

    public function resolve(object $content): ContentUrlResult|null
    {
        if (!($product = $this->getProduct($content))) {
            return null;
        }

        if ($content instanceof ProductReference && $content->referencePage) {
            $referencePage = $content->referencePage instanceof PageModel ? $content->referencePage : PageModel::findById($content->referencePage);

            if (self::canLinkToPage($referencePage)) {
                return ContentUrlResult::resolve($referencePage);
            }
        }

        if (
            (!$content instanceof ProductReference || !$content->linkToPrimary)
            && ($currentPage = $this->pageFinder->getCurrentPage())
            && self::canLinkToPage($currentPage)
        ) {
            return ContentUrlResult::resolve($currentPage);
        }

        $categories = $this->productCategoryUtil->getPublishedForProductInCurrentRoot($product);

        if ([] === $categories) {
            return null;
        }

        foreach ($categories as $category) {
            $pageModel = PageModel::findById($category);

            if (self::canLinkToPage($pageModel)) {
                return ContentUrlResult::resolve($pageModel);
            }
        }

        return null;
    }

    public function getParametersForContent(object $content, PageModel $pageModel): array
    {
        $product = $this->getProduct($content);

        if (!$product) {
            return [];
        }

        return [
            'parameters' => '/'.$product->getAlias(),
        ];
    }

    private function getProduct(object $content): CoreProduct|null
    {
        if ($content instanceof ProductReference) {
            return $content->product;
        }

        if ($content instanceof Product) {
            $content = $this->productBuilder->buildOne(
                new ProductDto('tl_iso_product.'.$content->id),
                $this->storeManager->getCurrentContext(),
            );
        }

        if ($content instanceof CoreProduct) {
            return $content;
        }

        return null;
    }
}
