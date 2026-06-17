<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\EventListener;

use Isotope\CoreBundle\Product\DataCollector\ProductDto;
use Isotope\CoreBundle\Product\DataCollector\ProductDtoCollection;
use Isotope\CoreBundle\Product\Extension\ShippingExtension;
use Isotope\CoreBundle\Product\ProductBuilder;
use Isotope\CoreBundle\ProductCollection\Event\LoadCollectionTransactionEvent;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Translation\TranslatableMessage;

#[AsEventListener(LoadCollectionTransactionEvent::NAME.'.post')]
class LoadProductsListener
{
    public function __construct(private readonly ProductBuilder $productBuilder)
    {
    }

    public function __invoke(LoadCollectionTransactionEvent $event): void
    {
        $lineItems = $event->getCollection()->getLineItems()->whereType(LineItem::TYPE_PRODUCT);
        $identifiers = array_unique($lineItems->column('getIdentifier'));
        $dtoCollection = new ProductDtoCollection(array_map(static fn (string $identifier) => new ProductDto($identifier), $identifiers));
        $products = $this->productBuilder->build($dtoCollection, $event->getStoreContext());

        foreach ($lineItems as $item) {
            $product = $products->findByIdentifier($item->getIdentifier());

            if (!$product) {
                $item->addError(new TranslatableMessage('ERR.collectionItemNotAvailable', [], 'contao_default'));
                continue;
            }

            $item->setProduct($product);
            $item->setSku($product->getSku());
            $item->setLabel($product->getName());
            $item->setWeight($product->getExtension(ShippingExtension::class)?->getWeight());
        }
    }
}
