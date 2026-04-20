<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Twig;

use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\Product;
use Isotope\CoreBundle\Product\Render\ViewInterface;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\Store\Context\ProductContext;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use Isotope\CoreBundle\Store\Price\PriceFormatter;
use Isotope\CoreBundle\Store\StoreManager;
use Isotope\Isotope;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\RuntimeExtensionInterface;

class ProductRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly StoreManager $storeManager,
        private readonly RequestStack $requestStack,
        private readonly PriceFormatter $priceFormatter,
        private readonly TemplateMarkupFactory $markupFactory,
    ) {
    }

    public function generateProduct(CoreProduct $product, ViewInterface $view): TemplateMarkup
    {
        return $product->getRenderer()->render(
            new ProductContext($product, $this->storeManager->getCurrentContext()),
            $view,
            $this->requestStack->getCurrentRequest(),
        );
    }

    public function generatePrice(CalculatedPrice|LineItem|Product|null $productOrItem, string|null $key = null, bool $html = true): TemplateMarkup|null
    {
        $price = $productOrItem instanceof CalculatedPrice ? $productOrItem : null;

        if ($productOrItem instanceof CoreProduct || $productOrItem instanceof LineItem) {
            $price = $productOrItem->getPrice();
        }

        if (!$price instanceof CalculatedPrice) {
            return null;
        }

        $context = $this->storeManager->getCurrentContext();

        if (!$html) {
            return $this->priceFormatter->toText($price, $context, $key);
        }

        return $this->priceFormatter->toHtml($price, $context, $key);
    }

    public function generateBasePrice(CalculatedPrice|LineItem|Product|null $productOrItem, bool $html = true): TemplateMarkup|null
    {
        $productPrice = $productOrItem instanceof CalculatedPrice ? $productOrItem : null;

        if ($productOrItem instanceof CoreProduct || $productOrItem instanceof LineItem) {
            $productPrice = $productOrItem->getPrice();
        }

        if (!$productPrice instanceof CalculatedPrice) {
            return null;
        }

        return $this->priceFormatter->getBasePrice($productPrice, $this->storeManager->getCurrentContext());
    }
}
