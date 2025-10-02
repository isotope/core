<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Render;

use Contao\CoreBundle\Exception\ResponseException;
use Contao\StringUtil;
use Contao\UploadableWidgetInterface;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
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
        private readonly Connection $connection,
    ) {
    }

    public function render(ProductInterface $product, ViewInterface $view, Request $request): string
    {
        $defaults = $this->getDefaults($product, $request);
        $useOptions = $view->useOptions($product);

        if ($useOptions) {
            $productOptions = $this->createProductOptions($product, $view, $request, $defaults);
            $variantOptions = $productOptions->getVariantOptions();

            if ($variant = $this->getVariant($product, $variantOptions, $view->useFallbackVariant($product))) {
                $product = $variant;
                $productOptions = $this->createProductOptions($product, $view, $request, $defaults);
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
            'view' => $view,
            'attributes' => $attributes,
            'options' => [],
            'hasUploads' => false,
            'isActive' => $request->attributes->get('_content') instanceof ProductInterface && $request->attributes->get('_content')?->getProductId() === $product->getProductId(),
        ];

        if ($useOptions) {
            $context['options'] = $productOptions->generateVariantOptionWidgets();
            $context['hasUploads'] = array_reduce($context['options'], static fn ($v, $o) => $v || $o['widget'] instanceof UploadableWidgetInterface, false);
        }

        $content = $this->twig->render($view->getTemplate($product), $context);

        if (
            $useOptions
            && $request->isXmlHttpRequest()
            && $request->request->get('FORM_SUBMIT') === $view->getFormId($product)
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

    private function createProductOptions(ProductInterface $product, ViewInterface $view, Request $request, array $defaults): ProductOptions
    {
        return new ProductOptions(
            $this->attributeRegistry,
            $this->connection,
            $product,
            $view,
            $request,
            $defaults,
            (bool) $product->getProductType()->force_variant_options
        );
    }

    private function getVariant(ProductInterface $product, array|null $variantOptions, bool $loadFallback): ProductInterface|null
    {
        if (null !== $variantOptions) {
            return $product->getVariant($variantOptions);
        }

        if ($loadFallback) {
            return $product->getDefaultVariant();
        }

        return null;
    }
}
