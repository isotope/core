<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Render;

use Contao\CoreBundle\Exception\ResponseException;
use Contao\StringUtil;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\Event\ProductPriceEvent;
use Isotope\CoreBundle\Store\Context\ProductContext;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class DefaultProductRenderer implements ProductRendererInterface
{
    public function __construct(
        private readonly Environment $twig,
        private readonly ProductConfigurationFactory $configurationFactory,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function render(ProductContext $productContext, ViewInterface $view, Request $request): string
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

        $event = new ProductPriceEvent($product, $productContext->getContext(), $configuration?->getData() ?? [], $product->getPrice());
        $this->eventDispatcher->dispatch($event);
        $product->setPrice($event->getPrice());

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
}
