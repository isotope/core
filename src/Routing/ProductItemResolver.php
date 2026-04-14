<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Routing;

use Contao\CoreBundle\Routing\Content\ContentUrlResolverInterface;
use Contao\CoreBundle\Routing\Content\ContentUrlResult;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\PageModel;
use Isotope\CoreBundle\Model\ProductCollectionItem;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\DataCollector\ProductDto;
use Isotope\CoreBundle\Product\ProductBuilder;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\Store\StoreManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProductItemResolver implements ContentUrlResolverInterface
{
    public function __construct(
        private readonly ProductBuilder $productBuilder,
        private readonly StoreManager $storeManager,
        private readonly ContentUrlGenerator $urlGenerator,
    ) {
    }

    public function resolve(object $content): ContentUrlResult|null
    {
        $id = null;
        $product = null;
        $referencePage = null;

        if ($content instanceof LineItem) {
            $id = $content->getId();
            $referencePage = $content->getReferencePage();
            $product = $content->getProduct();
        }

        if ($content instanceof ProductCollectionItem) {
            $id = $content->id;
            $referencePage = $content->referencePage;
            $product = $this->productBuilder->buildOne(
                new ProductDto($content->identifier),
                $this->storeManager->getCurrentContext(),
            );
        }

        if (!$product instanceof CoreProduct) {
            return null;
        }

        $content = new ProductReference($product, $referencePage ?: null);

        return ContentUrlResult::url($this->urlGenerator->generate(
            $content,
            $id ? ['collection_item' => $id] : [],
            UrlGeneratorInterface::ABSOLUTE_URL,
        ));
    }

    public function getParametersForContent(object $content, PageModel $pageModel): array
    {
        return [];
    }
}
