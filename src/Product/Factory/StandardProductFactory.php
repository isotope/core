<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Factory;

use Contao\StringUtil;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsProductFactory;
use Isotope\CoreBundle\Model\ProductType;
use Isotope\CoreBundle\Product\AvailabilityChecker;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\DataCollector\ProductDto;
use Isotope\CoreBundle\Product\DefaultPriceFactory;
use Isotope\CoreBundle\Product\Event\ProductCreatedEvent;
use Isotope\CoreBundle\Product\Extension\QuantityExtension;
use Isotope\CoreBundle\Product\Extension\ShippingExtension;
use Isotope\CoreBundle\Product\ProductCollection;
use Isotope\CoreBundle\Product\Render\DefaultProductRenderer;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Price\PriceCalculator;
use Isotope\CoreBundle\Weight\Weight;
use Psr\EventDispatcher\EventDispatcherInterface;

#[AsProductFactory('standard')]
class StandardProductFactory implements ProductFactoryInterface
{
    public function __construct(
        private readonly AvailabilityChecker $availabilityChecker,
        private readonly DefaultProductRenderer $productRenderer,
        private readonly DefaultPriceFactory $priceFactory,
        private readonly AttributeRegistry $attributeRegistry,
        private readonly PriceCalculator $calculator,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function create(ProductDto $dto, StoreContext $context): CoreProduct
    {
        $type = ProductType::findById($dto['type'] ?: $dto['__parent']['type']);
        $data = $this->getProductData($dto, $type);

        $product = new CoreProduct($data, $type, $this->productRenderer);
        $product
            ->setIsVariant($dto['pid'] > 0)
            ->setVariantIds(array_values(array_map(static fn (array $variant) => $variant['id'], $dto['__variants'] ?? [])))
            ->setVariants(fn () => $this->getVariants($dto, $context))
            ->setDefaultVariant(fn () => $this->getDefaultVariant($dto, $context))
            ->setPrices(fn () => $this->priceFactory->create($dto, $type, $context))
            ->setPrice(fn () => $this->calculator->calculateProduct($product, $dto->getQuantity(), true, $context))
            ->setAvailable(fn () => $this->availabilityChecker->isAvailable($product, $context))
        ;

        $product->addExtension(new QuantityExtension(static fn () => $product->getPrices()?->sort('getQuantity')->first()->getQuantity() ?? 1));
        $product->addExtension(new ShippingExtension(
            $product->shipping_exempt || '1' === $product->getProductType()->shipping_exempt,
            $product->shipping_pickup || '2' === $product->getProductType()->shipping_exempt,
            $product->shipping_weight ? Weight::createFromTimePeriod($product->shipping_weight) : null,
            $product->shipping_price,
        ));

        $this->eventDispatcher->dispatch(new ProductCreatedEvent($product, $dto, $context));

        return $product;
    }

    private function getProductData(ProductDto $dto, ProductType $type): array
    {
        $originalData = $dto->getArrayCopy();

        $attributes = array_merge(
            $this->attributeRegistry->getSystemColumns(),
            $type->getAttributes(),
            $type->getVariantAttributes(),
        );

        $data = array_filter(
            $originalData,
            static fn (string $key) => \in_array($key, $attributes, true),
            ARRAY_FILTER_USE_KEY,
        );

        $data = $this->translate($data, $originalData);
        $data = $this->inherit($data, $originalData, $type);

        return $data;
    }

    private function translate(array $data, array $originalData): array
    {
        $fallbackFields = $this->attributeRegistry->getFetchTranslationFallbackFields();

        foreach ($fallbackFields as $field) {
            $data[$field.'_fallback'] = $originalData[$field];
        }

        if ($originalData['__translation'] ?? null) {
            foreach ($this->attributeRegistry->getTranslatableFields() as $field) {
                $data[$field] = $originalData['__translation'][$field];
            }
        }

        return $data;
    }

    private function inherit(array $data, array $originalData, ProductType $type): array
    {
        if (!$originalData['__parent']) {
            return $data;
        }

        $data['alias'] = $originalData['__parent']['alias'] ?? $originalData['alias'] ?? '';

        foreach (array_diff($type->getAttributes(), $type->getVariantAttributes()) as $field) {
            if (isset($originalData['__parent'][$field])) {
                $data[$field] = $originalData['__parent'][$field];
            }
        }

        $inherit = StringUtil::deserialize($originalData['inherit']);

        if (!empty($inherit) && \is_array($inherit)) {
            $parent = $this->translate($originalData['__parent'], $originalData);
            $fallbackFields = $this->attributeRegistry->getFetchTranslationFallbackFields();

            foreach ($inherit as $field) {
                $data[$field] = $parent[$field];

                if (\in_array($field, $fallbackFields, true)) {
                    $data[$field.'_fallback'] = $parent[$field.'_fallback'] ?? null;
                }
            }
        }

        return $data;
    }

    private function getVariants(ProductDto $dto, StoreContext $context): ProductCollection
    {
        $products = new ProductCollection();

        foreach (($dto['__variants'] ?? []) as $variant) {
            $variantDto = new ProductDto('tl_iso_product.'.$variant['id'], $dto->getQuantity(), $variant);
            $variantDto['__variants'] = $dto['__variants'];

            $product = $this->create($variantDto, $context);

            if ($product->isAvailable()) {
                $products->add($product);
            }
        }

        return $products;
    }

    private function getDefaultVariant(ProductDto $dto, StoreContext $context): CoreProduct|null
    {
        foreach (($dto['__variants'] ?? []) as $variant) {
            if ($variant['fallback'] ?? false) {
                $variantDto = new ProductDto('tl_iso_product.'.$variant['id'], $dto->getQuantity(), $variant);
                $variantDto['__variants'] = $dto['__variants'];

                return $this->create($variantDto, $context);
            }
        }

        return null;
    }
}
