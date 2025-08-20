<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Routing;

use Contao\CoreBundle\Routing\Content\ContentUrlResolverInterface;
use Contao\CoreBundle\Routing\Content\ContentUrlResult;
use Contao\PageModel;
use Isotope\CoreBundle\Product\ProductInterface;
use Isotope\CoreBundle\Product\VariantProductInterface;
use Isotope\CoreBundle\Util\ProductCategoryUtil;
use Isotope\Model\Product;
use Symfony\Component\HttpFoundation\RequestStack;

class ProductResolver implements ContentUrlResolverInterface
{
    public function __construct(
        private readonly ProductCategoryUtil $productCategoryUtil,
        private readonly RequestStack $requestStack,
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

        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            return null;
        }

        if (
            (!$content instanceof ProductReference || !$content->linkToPrimary)
            && ($currentPage = $request->attributes->get('pageModel')) instanceof PageModel
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

        if ($product instanceof VariantProductInterface && $product->isVariant()) {
            $product = $product->getMainProduct();
        }

        return [
            'parameters' => '/'.$product->getAlias(),
        ];
    }

    private function getProduct(object $content): ProductInterface|null
    {
        if ($content instanceof ProductReference) {
            return $content->product;
        }

        if ($content instanceof ProductInterface) {
            return $content;
        }

        return null;
    }
}
