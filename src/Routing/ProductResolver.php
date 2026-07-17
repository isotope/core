<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Routing;

use Contao\CoreBundle\Routing\Content\ContentUrlResolverInterface;
use Contao\CoreBundle\Routing\Content\ContentUrlResult;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\CoreBundle\Routing\PageFinder;
use Contao\PageModel;
use Isotope\CoreBundle\Model\ProductModel;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\DataCollector\ProductDto;
use Isotope\CoreBundle\Product\ProductBuilder;
use Isotope\CoreBundle\Store\StoreManager;
use Isotope\CoreBundle\Util\ProductCategoryUtil;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProductResolver implements ContentUrlResolverInterface
{
    public function __construct(
        private readonly ProductCategoryUtil $productCategoryUtil,
        private readonly PageFinder $pageFinder,
        private readonly ProductBuilder $productBuilder,
        private readonly StoreManager $storeManager,
        private readonly ContentUrlGenerator $urlGenerator,
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

        $resolveVariant = $content instanceof ProductReference ? $content->resolveVariant : true;

        if ($content instanceof ProductReference && $content->referencePage) {
            $referencePage = $content->referencePage instanceof PageModel ? $content->referencePage : PageModel::findById($content->referencePage);

            if (self::canLinkToPage($referencePage)) {
                return $this->resolveProduct($content->product, $referencePage, $resolveVariant);
            }
        }

        if (
            (!$content instanceof ProductReference || !$content->linkToPrimary)
            && ($currentPage = $this->pageFinder->getCurrentPage())
            && self::canLinkToPage($currentPage)
        ) {
            return $this->resolveProduct($product, $currentPage, $resolveVariant);
        }

        $categories = $this->productCategoryUtil->getPublishedForProductInCurrentRoot($product);

        if ([] === $categories) {
            return null;
        }

        foreach ($categories as $category) {
            $pageModel = PageModel::findById($category);

            if (self::canLinkToPage($pageModel)) {
                return $this->resolveProduct($product, $pageModel, $resolveVariant);
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

        if ($content instanceof ProductModel) {
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

    private function resolveProduct(CoreProduct $product, PageModel $referencePage, bool $resolveVariant = true): ContentUrlResult
    {
        if (!$resolveVariant || !$product->isVariant()) {
            return ContentUrlResult::resolve($referencePage);
        }

        $content = new ProductReference($product, $referencePage ?: null);
        $content->resolveVariant = false;

        return ContentUrlResult::url($this->urlGenerator->generate(
            $content,
            $product->getVariantConfiguration() ?? [],
            UrlGeneratorInterface::ABSOLUTE_URL,
        ));
    }
}
