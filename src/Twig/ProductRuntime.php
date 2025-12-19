<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Twig;

use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\Product;
use Isotope\CoreBundle\Product\Render\ViewInterface;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\Store\Context\ProductContext;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use Isotope\CoreBundle\Store\Price\Currency;
use Isotope\CoreBundle\Store\Price\MoneyFormat;
use Isotope\CoreBundle\Store\StoreManager;
use Isotope\Isotope;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\RuntimeExtensionInterface;

class ProductRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly StoreManager $storeManager,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function generateProduct(CoreProduct $product, ViewInterface $view): string
    {
        return $product->getRenderer()->render(
            new ProductContext($product, $this->storeManager->getCurrentContext()),
            $view,
            $this->requestStack->getCurrentRequest(),
        );
    }

    /**
     * @param array{product?: CoreProduct} $context
     *
     * @return array{amount: float, money: MoneyFormat, currency: Currency}|null
     */
    public function generatePrice(array $context, CalculatedPrice|LineItem|Product|null $productOrItem = null): array|null
    {
        $productOrItem ??= $context['product'] ?? null;
        $price = $productOrItem instanceof CalculatedPrice ? $productOrItem : null;

        if ($productOrItem instanceof CoreProduct || $productOrItem instanceof LineItem) {
            $price = $productOrItem->getPrice();
        }

        if (!$price instanceof CalculatedPrice) {
            return null;
        }

        $storeContext = $this->storeManager->getCurrentContext();

        return [
            'unitPrice' => $price->unitPrice,
            'totalPrice' => $price->totalPrice,
            'isRange' => $price->isRange(),
            'money' => $storeContext->getMoneyFormat(),
            'currency' => $storeContext->getCurrency(),
        ];
    }

    public function generateBasePrice(array $context): string
    {
        $product = $context['product'] ?? null;

        if (!$product instanceof CoreProduct) {
            return '';
        }

        $price = $product->getPrice();
        $basePrice = $price?->getBasePrice();

        if (!$price || !$basePrice) {
            return '';
        }

        return \sprintf($basePrice->getLabel(), Isotope::formatPriceWithCurrency($price->unitPrice / $basePrice->getProductAmount() * $basePrice->getBaseAmount()), $basePrice->getProductAmount());
    }
}
