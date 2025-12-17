<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\EventListener;

use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\Event\ProductCreatedEvent;
use Isotope\CoreBundle\Product\Extension\MetadataExtension;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * @internal
 */
#[AsEventListener]
class MetadataListener
{
    public function __invoke(ProductCreatedEvent $event): void
    {
        $product = $event->product;

        if (!$product instanceof CoreProduct || $product->hasExtension(MetadataExtension::class)) {
            return;
        }

        $product->addExtension(new MetadataExtension(
            $product->meta_title ?: $product->name,
            $product->meta_description ?: $product->teaser ?: $product->description ?: '',
        ));
    }
}
