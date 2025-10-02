<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product;

use Contao\Input;
use Contao\System;
use Contao\UploadableWidgetInterface;
use Contao\Widget;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\Attribute\AttributeInterface;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Attribute\OptionsAttributeInterface;
use Isotope\CoreBundle\Attribute\VariantAttributeInterface;
use Isotope\CoreBundle\Model\Attribute;
use Isotope\CoreBundle\Product\Render\ViewInterface;
use Symfony\Component\HttpFoundation\Request;

class ProductOptions
{
    private array|null|false $variantOptions = false;
    private bool $hasErrors = false;

    /**
     * @var array<string, array{
     *     widget: Widget,
     *     options: list<mixed>,
     *     value: mixed,
     *     default: mixed,
     *     isVariant: bool,
     * }>|null
     */
    private array|null $attributes = null;

    public function __construct(
        private readonly AttributeRegistry $attributeRegistry,
        private readonly Connection $connection,
        private readonly ProductInterface $product,
        private readonly ViewInterface $view,
        private readonly Request $request,
        private readonly array $defaults = [],
        private readonly bool $forceVariantOptions = false,
    ) {
    }

    public function hasErrors(): bool
    {
        return $this->hasErrors;
    }

    public function getVariantOptions(): array|null
    {
        if (false !== $this->variantOptions) {
            return $this->variantOptions;
        }

        if (!$this->product->hasVariants()) {
            return $this->variantOptions = null;
        }

        $options = array();
        $variantAttributes = array_intersect($this->product->getProductType()->getVariantAttributes(), $this->attributeRegistry->getVariantOptionFields());
        $variantIds = $this->product->getVariantIds();

        // We don't need to validate VariantAttributeInterface here, because AttributeRegistry::getVariantOptionFields will check it
        foreach ($variantAttributes as $attributeName) {
            $attribute = $this->attributeRegistry->get($attributeName);

            if (!$attribute instanceof VariantAttributeInterface || !$attribute->isVariantOption()) {
                continue;
            }

            $values = $this->getOptionsForVariants($attributeName, $variantIds, $options);

            if ($this->request->isMethod(Request::METHOD_POST) && \in_array($this->request->request->get($attributeName), $values, false)) {
                $options[$attributeName] = Input::post($attributeName);
            } elseif ($this->request->isMethod(Request::METHOD_GET) && isset($this->defaults[$attributeName]) && \in_array($this->defaults[$attributeName], $values)) {
                $options[$attributeName] = $this->defaults[$attributeName];
            } elseif (1 === \count($values)) {
                $options[$attributeName] = $values[0];
            } else {

                // Abort if any attribute does not have a value, we only return a full set of variant options
                return $this->variantOptions = null;
            }

            if (
                $this->request->isMethod(Request::METHOD_POST)
                && $this->request->request->get('FORM_SUBMIT') === $this->view->getFormId($this->product)
            ) {

            }
            //if (Input::post('FORM_SUBMIT') === $product->getFormId() && Input::post($attributeName) === '') {
            //    Input::setPost($attributeName, $options[$attributeName]);
            //}
        }

        return $this->variantOptions = empty($options) ? null : $options;
    }

    public function generateVariantOptionWidgets(): array
    {
        $type = $this->product->getProductType();
        $attributes = array_unique(array_merge($type->getAttributes(), $type->getVariantAttributes()));

        $arrVariantOptions = array();
        $arrProductConfiguration = array();
        $arrProductOptions = array();
        $arrAjaxOptions    = array();

        foreach ($attributes as $attributeName) {
            $attribute = $this->attributeRegistry->get($attributeName);

            if (
                !$attribute || (
                    !$attribute->isCustomerDefined()
                    && (!$attribute instanceof VariantAttributeInterface || !$attribute->isVariantOption())
                )
            ) {
                continue;
            }

            $widget = $this->generateProductOptionWidget($attributeName, $arrVariantOptions, $arrProductConfiguration, $arrAjaxOptions);

            if (null !== $widget) {
                $arrProductOptions[$attributeName] = [
                    'name' => $attributeName,
                    'value' => $arrProductConfiguration[$attributeName] ?? null,
                    'attribute' => $attribute,
                    'widget' => $widget,
                ];
            }

            unset($widget);
        }

        return $arrProductOptions;
    }

    /**
     * Return a widget object based on a product attribute's properties
     */
    private function generateProductOptionWidget(string $attributeName, array &$arrVariantOptions, array &$arrProductConfiguration, array &$arrAjaxOptions): Widget|null
    {
        /** @var AttributeInterface|OptionsAttributeInterface|VariantAttributeInterface|Attribute $objAttribute */
        $objAttribute = $this->attributeRegistry->get($attributeName);
        $arrData = $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$attributeName] ?? [];

        $strClass = $objAttribute->getFrontendWidget();

        $arrData['eval']['required'] = $arrData['eval']['mandatory'] ?? false;

        // Value can be predefined in the URL, e.g. to preselect a variant
        if (!empty($this->defaults[$attributeName])) {
            $arrData['default'] = $this->defaults[$attributeName];
        }

        $arrField = $strClass::getAttributesFromDca($arrData, $attributeName, $arrData['default'] ?? null, $attributeName, 'tl_iso_product', $this->product);

        // Prepare variant selection field
        if ($objAttribute instanceof VariantAttributeInterface && $objAttribute->isVariantOption()) {
            $arrOptions = $this->getOptionsForVariants($attributeName, $this->product->getVariantIds(), $arrVariantOptions);

            // Hide selection if only one option is available (and "force_variant_options" is not set in product type)
            if (\count($arrOptions) == 1 && !$this->forceVariantOptions) {
                $arrVariantOptions[$attributeName] = $arrOptions[0];
                return null;
            }

            if ($arrField['value'] != '' && \in_array($arrField['value'], $arrOptions)) {
                $arrVariantOptions[$attributeName] = $arrField['value'];
            }

            // Remove options not available in any product variant
            if (\is_array($arrField['options'] ?? null)) {
                $blankOption = null;

                foreach ($arrField['options'] as $k => $option) {
                    // Keep groups and blankOptionLabels
                    if ($option['value'] == '') {
                        if (null !== $blankOption) {
                            // Last blank option wins
                            $arrField['options'][$blankOption] = $option;
                            unset($arrField['options'][$k]);
                        } else {
                            $blankOption = $k;
                        }
                    } elseif (!\in_array($option['value'], $arrOptions) && !($option['group'] ?? false)) {
                        unset($arrField['options'][$k]);
                    }
                }

                $arrField['options'] = array_values($arrField['options']);
            }

            $arrField['value'] = $this->product[$attributeName];

        } elseif ($objAttribute instanceof OptionsAttributeInterface && empty($arrField['options'])) {
            return null;
        }

        if ($objAttribute->isVariantOption()
            || ($objAttribute instanceof OptionsAttributeInterface && $objAttribute->canHavePrices())
            || ($arrData['attributes']['ajax_option'] ?? null)
            || ($arrField['attributes']['ajax_option'] ?? null) // see https://github.com/isotope/core/issues/2096
        ) {
            $arrAjaxOptions[] = $attributeName;
        }

        $arrField['id'] = $this->view->getFormId($this->product).'_'.$attributeName;

        $objWidget = new $strClass($arrField);
        $objWidget->storeValues = true;
        $objWidget->product = $this->product;

        if ($this->request->isMethod(Request::METHOD_GET) && $this->request->query->has($attributeName) && !$objAttribute->isVariantOption()) {
            /*$product->arrCustomerConfig[$strField] =*/ $objWidget->value = $this->request->query->get($attributeName);

            return $objWidget;
        }

        // Validate input
        if (
            $this->request->isMethod(Request::METHOD_POST)
            && $this->request->request->get('FORM_SUBMIT') === $this->view->getFormId($this->product)
        ) {
            $objWidget->validate();

            if (!$objWidget->hasErrors() && ($objWidget->submitInput() || $objWidget instanceof UploadableWidgetInterface)) {
                $varValue = $objWidget->value;

                // Convert date formats into timestamps
                //if ($varValue != '' && \in_array($arrData['eval']['rgxp'], ['date', 'time', 'datim'], true)) {
                //    try {
                //        /** @var Date|object $objDate */
                //        $objDate = new Date($varValue, $GLOBALS['TL_CONFIG'][$arrData['eval']['rgxp'] . 'Format']);
                //        $varValue = $objDate->tstamp;
                //    } catch (\OutOfBoundsException $e) {
                //        $objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR'][$arrData['eval']['rgxp']], $GLOBALS['TL_CONFIG'][$arrData['eval']['rgxp'] . 'Format']));
                //    }
                //}

                // Trigger the save_callback
                if (\is_array($arrData['save_callback'] ?? null)) {
                    foreach ($arrData['save_callback'] as $callback) {
                        try {
                            if (\is_array($callback)) {
                                $varValue = System::importStatic($callback[0])->{$callback[1]}($varValue, $this->product, $objWidget);
                            } else {
                                $varValue = $objAttribute->{$callback}($varValue, $this->product, $objWidget);
                            }
                        } catch (\Exception $e) {
                            $objWidget->class = 'error';
                            $objWidget->addError($e->getMessage());
                            $this->hasErrors = true;
                        }
                    }
                }

                if (!$objWidget->hasErrors() && $varValue != '') {
                    if ($objAttribute instanceof VariantAttributeInterface && $objAttribute->isVariantOption()) {
                        $arrVariantOptions[$attributeName] = $varValue;
                    } else {
                        $arrProductConfiguration[$attributeName] = $varValue;
                    }
                }
            }
        }

        return $objWidget;
    }

    private function getOptionsForVariants(string $attributeName, array $variantIds, array $variantOptions = []): array
    {
        if ([] === $variantIds) {
            return [];
        }

        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select('DISTINCT '.$attributeName)
            ->from('tl_iso_product')
            ->where($qb->expr()->in('id', $qb->createNamedParameter($variantIds, ArrayParameterType::INTEGER)))
        ;

        foreach ($variantOptions as $field => $value) {
            $qb->andWhere($qb->expr()->eq($field, $qb->createNamedParameter($value)));
        }

        return $qb->fetchFirstColumn();
    }
}
