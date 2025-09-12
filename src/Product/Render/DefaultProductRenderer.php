<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Render;

use Contao\CoreBundle\Exception\ResponseException;
use Contao\StringUtil;
use Contao\UploadableWidgetInterface;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\ModelType\ProductRegistry;
use Isotope\CoreBundle\Product\ProductInterface;
use Isotope\CoreBundle\Product\ProductOptions;
use Isotope\Model\ProductCollectionItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class DefaultProductRenderer implements ProductRendererInterface
{
    public function __construct(
        private readonly Environment $twig,
        private readonly AttributeRegistry $attributeRegistry,
        private readonly ProductRegistry $productRegistry,
        private readonly Connection $connection,
    ) {
    }

    public function render(ProductInterface $product, RenderConfig $config, Request $request): string
    {
        $this->strFormId = $config->id;
        $defaults = $this->getDefaults($product, $request);
        $productOptions = new ProductOptions($this->attributeRegistry, $this->connection, $product, $config, $request, $defaults, (bool) $product->getProductType()->force_variant_options);

        if ($config->withOptions) {
            $variantOptions = $productOptions->getVariantOptions();

            if (null !== $variantOptions && null !== ($variant = $product->getVariant($variantOptions))) {
                $productOptions = new ProductOptions($this->attributeRegistry, $this->connection, $variant, $config, $request, $defaults, (bool) $variant->getProductType()->force_variant_options);
                $product = $variant;
            } elseif (null === $variantOptions && $config->loadFallback && null !== ($variant = $product->getDefaultVariant())) {
                $productOptions = new ProductOptions($this->attributeRegistry, $this->connection, $variant, $config, $request, $defaults, (bool) $variant->getProductType()->force_variant_options);
                $product = $variant;
            }
        }

        $type = $product->getProductType();
        $attributes = array_unique(array_merge($type->getAttributes(), $type->getVariantAttributes()));
        $cssID = StringUtil::deserialize($product['cssID'], true);

        $context = [
            'product_html_id' => $cssID[0] ?? null,
            'product_css_classes' => $cssID[1] ?? null,
            'product' => $product,
            'type' => $type,
            'config' => $config,
            'attributes' => $attributes,
            'options' => [],
            'hasUploads' => false,
            'isActive' => $request->attributes->get('_content') instanceof ProductInterface && $request->attributes->get('_content')?->getProductId() === $product->getProductId(),
        ];

        if ($config->withOptions) {
            $context['options'] = $productOptions->generateVariantOptionWidgets();
            $context['hasUploads'] = array_reduce($context['options'], static fn ($v, $o) => $v || $o['widget'] instanceof UploadableWidgetInterface, false);
        }

        $content = $this->twig->render($config->template, $context);

        if (
            $config->withOptions
            && $request->isXmlHttpRequest()
            && $request->request->get('FORM_SUBMIT') === $config->id
        ) {
            throw new ResponseException(new Response($content));
        }

        return $content;
    }

    private function getDefaults(ProductInterface $product, Request $request): array
    {
        if ($request->query->has('collection_item')) {
            $item = ProductCollectionItem::findById($request->query->get('collection_item'));
            // TODO: check this item belongs to a collection that belongs to the current user

            if ($item && $item->isProduct($product)) {
                return $item->getConfiguration();
            }
        }

        return $request->query->all();
    }
}
