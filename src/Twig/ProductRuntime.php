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
use Isotope\CoreBundle\Store\Price\PriceDisplay;
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
        $format = $storeContext->getMoneyFormat();

        $data =  [
            'unit_price' => $price->getUnitPrice(),
            'unit_tax' => $format->mathRound($price->getCalculatedTaxes()->sum()),
            'total_price' => $price->getTotalPrice(),
            'is_range' => $price->isRange(),
            'tax_ids' => $price->getCalculatedTaxes()->getNumbers(),
            'money' => $storeContext->getMoneyFormat(),
            'currency' => $storeContext->getCurrency(),
            'tax_calculation' => $storeContext->getTaxCalculation()->value,
            'price_display' => $storeContext->getPriceDisplay()->value,
            'is_gross' => PriceDisplay::Net !== $storeContext->getPriceDisplay(),
        ];

        if (PriceDisplay::Net === $storeContext->getPriceDisplay()) {
            $data['net_unit_price'] = $price->getUnitPrice();
            $data['gross_unit_price'] = $price->getUnitPrice() + $data['unit_tax'];
        } else {
            $data['gross_unit_price'] = $price->getUnitPrice();
            $data['net_unit_price'] = $price->getUnitPrice() - $data['unit_tax'];
        }

        $data['quantity'] = $price->getQuantity();
        $data['total_tax'] = $data['unit_tax'] * $price->getQuantity();
        $data['net_total_price'] = $data['net_unit_price'] * $price->getQuantity();
        $data['gross_total_price'] = $data['gross_unit_price'] * $price->getQuantity();

        return $data;
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

        return \sprintf($basePrice->getLabel(), Isotope::formatPriceWithCurrency($price->getUnitPrice() / $basePrice->getProductAmount() * $basePrice->getBaseAmount()), $basePrice->getProductAmount());
    }
}
