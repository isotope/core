<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Render;

use Contao\CoreBundle\Exception\ResponseException;
use Contao\StringUtil;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\ProductPriceCalculator;
use Isotope\CoreBundle\Store\Context\ProductContext;
use Isotope\CoreBundle\Twig\TemplateMarkup;
use Isotope\CoreBundle\Twig\TemplateMarkupFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultProductRenderer implements ProductRendererInterface
{
    public function __construct(
        private readonly ProductConfigurationFactory $configurationFactory,
        private readonly ProductPriceCalculator $priceCalculator,
        private readonly TemplateMarkupFactory $markupFactory,
    ) {
    }

    public function render(ProductContext $productContext, ViewInterface $view, Request $request): TemplateMarkup
    {
        $product = $productContext->getProduct();
        $type = $product->getProductType();

        $attributes = array_unique(array_merge($type->getAttributes(), $type->getVariantAttributes()));
        $useOptions = $view->useOptions($product);
        $configuration = null;

        if ($useOptions) {
            $configuration = $this->configurationFactory->create($productContext, $view, $request);
            $product = $configuration->getProduct();
        }

        // Keep option surcharges local to this render so rendering the product again does not apply them twice.
        $product = clone $product;
        $configuration?->setProduct($product);

        if ($configuration && [] !== $configuration->getData()) {
            $product->setPrice($this->priceCalculator->calculate(
                $product,
                $product->getPrice()?->getQuantity() ?? 1,
                true,
                $productContext->getContext(),
                $configuration->getData(),
            ));
        }

        $cssID = StringUtil::deserialize($product->cssID, true);

        $context = [
            'product_html_id' => $cssID[0] ?? null,
            'product_css_classes' => $cssID[1] ?? null,
            'product' => $product,
            'type' => $type,
            'view' => $view,
            'attributes' => $attributes,
            'configuration' => $configuration,
            'isActive' => $request->attributes->get('_content') instanceof CoreProduct && $request->attributes->get('_content')?->getProductId() === $product->getProductId(),
        ];

        $template = $this->markupFactory->create($view->getTemplate($product), $context);

        if (
            $useOptions
            && $request->isXmlHttpRequest()
            && $request->request->get('FORM_SUBMIT') === $view->getFormId($product)
        ) {
            throw new ResponseException(new Response((string) $template));
        }

        return $template;
    }
}
