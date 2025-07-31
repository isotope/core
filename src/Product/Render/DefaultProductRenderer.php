<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Render;

use Contao\CoreBundle\Exception\ResponseException;
use Contao\StringUtil;
use Contao\UploadableWidgetInterface;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Product\ProductOptions;
use Isotope\Frontend\ProductAction\ProductActionInterface;
use Isotope\Frontend\ProductAction\Registry;
use Isotope\Model\Product;
use Isotope\Model\ProductCollectionItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class DefaultProductRenderer implements ProductRendererInterface
{
    public function __construct(
        private readonly Environment $twig,
        private readonly AttributeRegistry $attributeRegistry,
        private readonly Connection $connection,
    ) {
    }

    public function render(Product $product, RenderConfig $config, Request $request): string
    {
        $this->strFormId = $config->id;
        $defaults = $this->getDefaults($product, $request);
        $productOptions = new ProductOptions($this->attributeRegistry, $this->connection, $product, $config, $request, $defaults, (bool) $product->getType()->force_variant_options);

        if ($config->withOptions) {
            $variantOptions = $productOptions->getVariantOptions();

            if (null !== $variantOptions && null !== ($variant = Product::findVariantOfProduct($product, $variantOptions))) {
                $productOptions = new ProductOptions($this->attributeRegistry, $this->connection, $variant, $config, $request, $defaults, (bool) $variant->getType()->force_variant_options);
                $product = $variant;
            } elseif (null === $variantOptions && $config->loadFallback && null !== ($variant = Product::findDefaultVariantOfProduct($product))) {
                $productOptions = new ProductOptions($this->attributeRegistry, $this->connection, $variant, $config, $request, $defaults, (bool) $variant->getType()->force_variant_options);
                $product = $variant;
            }
        }

        $type = $product->getType();
        $attributes = array_unique(array_merge($type->getAttributes(), $type->getVariantAttributes()));
        $cssID = StringUtil::deserialize($product->cssID, true);

        $context = [
            'data' => $product->row(),
            'product_html_id' => $cssID[0] ?? null,
            'product_css_classes' => $cssID[1] ?? null,
            'product' => $product,
            'type' => $type,
            'config' => $config,
            'attributes' => $attributes,
            'options' => [],
            'hasUploads' => false,
            'isActive' => $request->attributes->get('_content') instanceof Product && $request->attributes->get('_content')?->getProductId() === $product->getProductId(),
        ];

        if ($config->withOptions) {
            $context['options'] = $productOptions->generateVariantOptionWidgets();
            $context['hasUploads'] = array_reduce($context['options'], static fn ($v, $o) => $v || $o['widget'] instanceof UploadableWidgetInterface, false);
        }

        $actions = array_filter(
            Registry::all(true, $this),
            static fn (ProductActionInterface $action) => \in_array($action->getName(), $config->buttons) && $action->isAvailable($product, $config->toArray()),
        );

        // Sort actions by order in module configuration
        $buttonOrder = array_values($arrConfig['buttons'] ?? []);
        usort($actions, static fn (ProductActionInterface $a, ProductActionInterface $b) => array_search($a->getName(), $buttonOrder) <=> array_search($b->getName(), $buttonOrder));

        $context['actions'] = $actions;

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

    private function getDefaults(Product $product, Request $request): array
    {
        if ($request->query->has('collection_item')) {
            $item = ProductCollectionItem::findById($request->query->get('collection_item'));
            // TODO: check this item belongs to a collection that belongs to the current user

            if (null !== $item
                && $item->hasProduct()
                && $item->getProduct()->getProductId() === $product->getProductId()
            ) {
                return $item->getOptions();
            }
        }

        return $request->query->all();
    }
}
