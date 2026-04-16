<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Render;

use Contao\Controller;
use Contao\StringUtil;
use Contao\System;
use Contao\UploadableWidgetInterface;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Attribute\OptionsAttributeInterface;
use Isotope\CoreBundle\Attribute\VariantAttributeInterface;
use Isotope\CoreBundle\Model\ProductCollectionItemModel;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\Event\ProductConfigurationEvent;
use Isotope\CoreBundle\Store\Context\ProductContext;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

class ProductConfigurationFactory
{
    public function __construct(
        private readonly AttributeRegistry $attributeRegistry,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function create(ProductContext $productContext, ViewInterface $view, Request $request): ProductConfiguration
    {
        Controller::loadDataContainer('tl_iso_product');

        $configuration = [];
        $widgets = [];
        $ajaxAttributes = [];

        $pricedFields = $this->attributeRegistry->getPricedFields();
        $product = $productContext->getProduct();
        $type = $product->getProductType();
        $attributes = array_unique(array_merge($type->getAttributes(), $type->getVariantAttributes()));
        $variants = $product->getVariants();
        $defaultVariant = $view->useFallbackVariant($product) ? $product->getDefaultVariant() : null;
        $defaults = $this->getDefaults($product, $request);

        foreach ($attributes as $attributeName) {
            $attribute = $this->attributeRegistry->get($attributeName);

            if (
                !$attribute
                || (
                    !$attribute->isCustomerDefined()
                    && (!$attribute instanceof VariantAttributeInterface || !$attribute->isVariantOption())
                )
                || !($widgetClass = $attribute->getFrontendWidget())
            ) {
                continue;
            }

            $dcaConfig = $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$attributeName] ?? [];
            $fieldConfig = $widgetClass::getAttributesFromDca($dcaConfig, $attributeName, $dcaConfig['default'] ?? null, $attributeName, 'tl_iso_product', $productContext);
            $value = null;

            if ($attribute instanceof VariantAttributeInterface && $attribute->isVariantOption()) {
                $values = array_unique($variants->column($attributeName));

                if ($request->isMethod(Request::METHOD_POST) && \in_array($request->request->get($attributeName), $values, false)) {
                    $value = $request->request->get($attributeName);
                } elseif (isset($defaults[$attributeName]) && $request->isMethod(Request::METHOD_GET) && \in_array($defaults[$attributeName], $values, false)) {
                    $value = $defaults[$attributeName];
                } elseif ($defaultVariant) {
                    $value = $defaultVariant->{$attributeName};
                } elseif (1 === \count($values)) {
                    $value = $values[0];
                }

                if ($value) {
                    $variants = $variants->where($attributeName, $value);
                    $configuration[$attributeName] = $value;
                }

                // Skip widget rendering if only one option is available (and "force_variant_options" is not set in product type)
                if (!$type->force_variant_options && 1 === \count($values)) {
                    continue;
                }

                if (\is_array($fieldConfig['options'] ?? null)) {
                    $fieldConfig['options'] = array_filter($fieldConfig['options'], static fn (array $option) => empty($option['value']) || \in_array($option['value'], $values, false));
                }
            } elseif ($attribute instanceof OptionsAttributeInterface && [] === ($fieldConfig['options'] ?? [])) {
                continue;
            }

            $fieldConfig['id'] = $view->getFormId($product).'_'.$attributeName;

            $widget = new $widgetClass($fieldConfig);
            $widget->storeValues = true;
            $widget->value = $value ?? $defaults[$attributeName] ?? null;

            if (
                $request->isMethod(Request::METHOD_POST)
                && $request->request->get('FORM_SUBMIT') === $view->getFormId($product)
            ) {
                $widget->validate();

                if (!$widget->hasErrors() && ($widget->submitInput() || $widget instanceof UploadableWidgetInterface)) {
                    $value = $widget->value;

                    // Trigger the save_callback
                    if (\is_array($arrData['save_callback'] ?? null)) {
                        foreach ($arrData['save_callback'] as $callback) {
                            try {
                                if (\is_array($callback)) {
                                    $varValue = System::importStatic($callback[0])->{$callback[1]}($varValue, $product, $widget);
                                } else {
                                    $varValue = $callback($varValue, $product, $widget);
                                }
                            } catch (\Exception $e) {
                                $widget->class = 'error';
                                $widget->addError($e->getMessage());
                            }
                        }
                    }

                    if ('' !== $value && !$widget->hasErrors()) {
                        $configuration[$attributeName] = $value;
                    }
                }
            }

            $widgets[$attributeName] = $widget;

            if (
                ($arrData['attributes']['ajax_option'] ?? null)
                || \in_array($attributeName, $pricedFields, true)
                || ($attribute instanceof VariantAttributeInterface && $attribute->isVariantOption())
            ) {
                $ajaxAttributes[] = $attributeName;
            }
        }

        $event = new ProductConfigurationEvent(
            1 === $variants->count() ? $variants->first() : $product,
            $productContext->getContext(),
            $configuration,
            $widgets,
            $ajaxAttributes,
        );

        $this->eventDispatcher->dispatch($event);

        return new ProductConfiguration(
            $event->getProduct(),
            $event->getData(),
            $event->getWidgets(),
            $event->getAjaxAttributes(),
        );
    }

    private function getDefaults(CoreProduct $product, Request $request): array
    {
        if ($request->query->has('collection_item')) {
            $item = ProductCollectionItemModel::findById($request->query->get('collection_item'));
            // TODO: check this item belongs to a collection that belongs to the current user

            if ($item && $item->identifier === $product->getIdentifier()) {
                return StringUtil::deserialize($item->configuration, true);
            }
        }

        return $request->query->all();
    }
}
