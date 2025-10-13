<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Twig;

use Contao\StringUtil;
use Isotope\CoreBundle\Model\BasePrice;
use Isotope\CoreBundle\Product\ProductInterface;
use Isotope\CoreBundle\Product\Render\ViewInterface;
use Isotope\CoreBundle\Store\StoreManager;
use Isotope\Isotope;
use Twig\Extension\RuntimeExtensionInterface;

class ProductRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly StoreManager $storeManager)
    {
    }

    public function generateProduct(array $context, ProductInterface $product, ViewInterface|null $view = null): string
    {
        $view = $view ?? $context['view'] ?? null;

        if (!$view instanceof ViewInterface) {
            throw new \RuntimeException('Cannot render product without a render view.');
        }

        return $product->getRenderer()->render($product, $view, $context['app']->getRequest());
    }

    public function generatePrice(array $context): string
    {
        $product = $context['product'] ?? null;

        if (!$product instanceof ProductInterface) {
            return '';
        }

        $price = $product->getPrice($this->storeManager->getCurrentContext());
        $type = $product->getProductType();

        if (null === $price) {
            return '';
        }

        $configuration = array_filter(array_map(static fn ($o) => $o['value'] ?? null, $context['options'] ?? []));

        return $price->generate($type->showPriceTiers(), 1, $configuration);
    }

    public function generateBasePrice(array $context): string
    {
        $product = $context['product'] ?? null;

        if (!$product instanceof ProductInterface) {
            return '';
        }

        $data = StringUtil::deserialize($product['baseprice']);

        if (\is_array($data) && $data['unit'] > 0 && '' !== $data['value']) {
            $basePrice = BasePrice::findByPk((int) $data['unit']);

            if (null !== $basePrice && null !== $product->getPrice($this->storeManager->getCurrentContext())) {
                return sprintf($basePrice->label, Isotope::formatPriceWithCurrency($product->getPrice($this->storeManager->getCurrentContext())->getAmount() / $data['value'] * $basePrice->amount), $data['value']);
            }
        }

        return '';
    }
}
