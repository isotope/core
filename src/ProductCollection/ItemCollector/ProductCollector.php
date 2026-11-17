<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\ItemCollector;

use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\Extension\ShippingExtension;
use Isotope\CoreBundle\Product\ProductPriceCalculator;
use Isotope\CoreBundle\ProductCollection\AbstractProductCollection;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Translation\TranslatableMessage;

#[AutoconfigureTag('isotope.item_collector', ['priority' => 100])]
class ProductCollector implements ItemCollectorInterface
{
    public function __construct(
        private readonly ProductPriceCalculator $priceCalculator,
    ) {
    }

    public function process(AbstractProductCollection $collection, AbstractProductCollection $original, StoreContext $context): void
    {
        $items = $original->getLineItems()->products();

        foreach ($items as $item) {
            $product = $item->getProduct();

            if (!$product instanceof CoreProduct) {
                continue;
            }

            if (!$product->isAvailable() && !$item->hasErrors()) {
                $item->addError(new TranslatableMessage('ERR.collectionItemNotAvailable', [], 'contao_default'));
            }

            $item->setPrice($this->priceCalculator->calculate($product, $item->getQuantity(), false, $context, $item->getConfiguration() ?? []));
            $item->setWeight($product->getExtension(ShippingExtension::class)?->getWeight());

            $collection->add($item);
        }
    }
}
