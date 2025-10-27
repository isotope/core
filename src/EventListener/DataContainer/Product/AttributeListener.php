<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Product;

use Codefog\HasteBundle\Formatter;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\DataContainer;
use Contao\Input;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Attribute\OptionsAttributeInterface;
use Isotope\CoreBundle\Attribute\OptionsSource;
use Isotope\CoreBundle\Attribute\VariantAttributeInterface;
use Isotope\CoreBundle\DataContainer\ProductData;
use Isotope\CoreBundle\Model\ProductType;
use Isotope\CoreBundle\Product\ProductInterface;
use Isotope\Model\RelatedCategory;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Loads attributes and configuration from product type into the tl_iso_product table.
 */
class AttributeListener
{
    public function __construct(
        private readonly AttributeRegistry $attributeRegistry,
        private readonly RequestStack $requestStack,
        private readonly Connection $connection,
        private readonly Formatter $formatter,
    ) {
    }

    #[AsHook('loadDataContainer', priority: 250)]
    public function loadAttributes(string $table): void
    {
        if ('tl_iso_product' !== $table) {
            return;
        }

        $this->attributeRegistry->load();
    }

    #[AsHook('getAttributesFromDca')]
    public function addOptionsFromAttribute(array $config, mixed $dc): array
    {
        if ('tl_iso_product' !== ($config['strTable'] ?? null) || !isset($config['strField'])) {
            return $config;
        }

        $attribute = $this->attributeRegistry->get($config['strField']);

        if (
            !$attribute instanceof OptionsAttributeInterface
            || OptionsSource::ForeignKey === $attribute->getOptionsSource()
        ) {
            return $config;
        }

        $config['options'] = ($dc instanceof ProductInterface) ? $attribute->getOptionsForWidget($dc) : $attribute->getOptionsForWidget();

        if ($config['includeBlankOption'] ?? null) {
            array_unshift($config['options'], ['value' => '', 'label' => ($config['blankOptionLabel'] ?? '-')]);
        }

        if (null !== ($config['default'] ?? null)) {
            $defaults = array_filter($config['options'], static fn ($option) => (bool) $option['default']);

            if ([] !== $defaults) {
                $defaults = array_map(static fn ($option) => $option['value'], $defaults);
                $config['value'] = $attribute->hasMultiple() ? $defaults : reset($defaults);
            }
        }

        return $config;
    }

    /**
     * Optimize the DCA by removing operations that are not used, so button_callbacks are not executed for no reason.
     * Also removes search and filter fields and viewable columns if they do not exist in any product type.
     */
    #[AsCallback('tl_iso_product', 'config.onload')]
    public function optimizeGlobalView(DataContainer $dc): void
    {
        $GLOBALS['TL_DCA']['tl_iso_product']['fields']['prices']['exclude'] = &$GLOBALS['TL_DCA']['tl_iso_product']['fields']['price']['exclude'];

        if (null === $dc->currentPid) {
            unset($GLOBALS['TL_DCA']['tl_iso_product']['list']['operations']['fallback']);

            $GLOBALS['TL_DCA']['tl_iso_product']['fields']['pid']['eval']['doNotShow'] = true;
            $GLOBALS['TL_DCA']['tl_iso_product']['fields']['inherit']['eval']['doNotShow'] = true;
            $GLOBALS['TL_DCA']['tl_iso_product']['fields']['fallback']['eval']['doNotShow'] = true;

            if (0 === RelatedCategory::countAll()) {
                unset($GLOBALS['TL_DCA']['tl_iso_product']['list']['operations']['related']);
            }
        } else {
            unset(
                $GLOBALS['TL_DCA']['tl_iso_product']['list']['operations']['children'],
                $GLOBALS['TL_DCA']['tl_iso_product']['list']['operations']['related'],
            );
        }

        // Remove non-active fields from multi-selection
        if (\in_array(Input::get('act'), ['editAll', 'overrideAll'], true)) {
            $ids = $this->requestStack->getSession()->get('CURRENT')['IDS'] ?? null;
            $attributes = [];
            $hasRegularPrices = false;
            $hasAdvancedPrices = false;

            if (!empty($ids)) {
                $types = $this->connection->fetchFirstColumn(
                    <<<'SQL'
                            SELECT DISTINCT type FROM tl_iso_product WHERE id IN (?) AND pid=0
                            UNION
                            SELECT DISTINCT type FROM tl_iso_product WHERE id IN (SELECT pid FROM tl_iso_product WHERE id IN (?))
                        SQL,
                    [$ids, $ids],
                    [ArrayParameterType::INTEGER, ArrayParameterType::INTEGER],
                );

                foreach (ProductType::findMultipleByIds($types) as $type) {
                    $typeAttributes = $this->getAvailableFieldsForType($type, null !== $dc->currentPid);
                    $typeHasPrice = \in_array('price', $typeAttributes, true);
                    $hasAdvancedPrices = $hasAdvancedPrices || ($typeHasPrice && $type->hasAdvancedPrices());
                    $hasRegularPrices = $hasRegularPrices || ($typeHasPrice && !$type->hasAdvancedPrices());
                    $attributes[] = $typeAttributes;
                }

                $attributes = array_unique(array_merge(...$attributes));
            }

            if (false !== ($pos = array_search('price', $attributes, true))) {
                if ($hasAdvancedPrices) {
                    $attributes[] = 'prices';
                }

                if (!$hasRegularPrices) {
                    unset($attributes[$pos]);
                }
            }

            foreach (array_diff(array_keys($GLOBALS['TL_DCA']['tl_iso_product']['fields']), $attributes) as $field) {
                $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$field]['eval']['doNotShow'] = true;
            }

            return;
        }

        $hasDownloads = false;
        $hasVariants = null !== $dc->currentPid; // Only check for variants if we are in product view
        $attributes = [];

        if (null !== ($objProductTypes = ProductType::findAllUsed())) {
            /** @var ProductType $objType */
            foreach ($objProductTypes as $objType) {
                if (!$hasDownloads && $objType->hasDownloads()) {
                    $hasDownloads = true;
                }

                if (!$hasVariants && $objType->hasVariants()) {
                    $hasVariants = true;
                }

                $attributes[] = $objType->getAttributes();

                if ($objType->hasVariants()) {
                    $attributes[] = $objType->getVariantAttributes();
                }
            }
        }

        $attributes = array_merge(...$attributes);

        // If no downloads are enabled in any product type, we do not need the operation
        if (!$hasDownloads) {
            unset($GLOBALS['TL_DCA']['tl_iso_product']['list']['operations']['downloads']);
        }

        // Disable variant button if there are no variants at all
        if (!$hasVariants) {
            unset($GLOBALS['TL_DCA']['tl_iso_product']['list']['operations']['children']);
        }

        // Remove label fields that are not enabled in any product type
        foreach ($GLOBALS['TL_DCA']['tl_iso_product']['list']['label']['fields'] as $pos => $field) {
            if (!\in_array($field, $attributes, true)) {
                unset($GLOBALS['TL_DCA']['tl_iso_product']['list']['label']['fields'][$pos]);
            }
        }

        foreach (array_diff(array_keys($GLOBALS['TL_DCA']['tl_iso_product']['fields']), array_unique($attributes)) as $field) {
            if ($GLOBALS['TL_DCA']['tl_iso_product']['fields'][$field]['attributes']['systemColumn'] ?? false) {
                continue;
            }

            // Disable filter, sorting and search on fields that are not used in any product type
            $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$field]['filter'] = false;
            $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$field]['sorting'] = false;
            $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$field]['search'] = false;
        }
    }

    #[AsCallback('tl_iso_product', 'config.onload')]
    public function optimizeCurrentRecordView(ProductData $dc): void
    {
        [$type, $isVariant] = $this->getCurrent($dc);

        if (!$type) {
            return;
        }

        if (!$isVariant && $type->hasVariants()) {
            $GLOBALS['TL_DCA']['tl_iso_product']['config']['switchToEdit'] = true;
        }

        if ('show' === Input::get('act')) {
            foreach (array_diff(array_keys($GLOBALS['TL_DCA']['tl_iso_product']['fields']), $this->getAvailableFieldsForType($type, $isVariant)) as $field) {
                if (
                    ($GLOBALS['TL_DCA']['tl_iso_product']['fields'][$field]['attributes']['systemColumn'] ?? false)
                    && (!$isVariant || !($GLOBALS['TL_DCA']['tl_iso_product']['fields'][$field]['attributes']['fixed'] ?? false))
                    && ($isVariant || !($GLOBALS['TL_DCA']['tl_iso_product']['fields'][$field]['attributes']['variant_fixed'] ?? false))
                    && !($GLOBALS['TL_DCA']['tl_iso_product']['fields'][$field]['attributes']['singular'] ?? false)
                ) {
                    continue;
                }

                $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$field]['eval']['doNotShow'] = true;
            }

            return;
        }

        // Adjust columns in backend view to show variant attributes
        if ($dc->currentPid && (!Input::get('act') || 'select' === Input::get('act'))) {
            $GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['fields'] = ['id'];
            $GLOBALS['TL_DCA']['tl_iso_product']['fields']['alias']['sorting'] = false;

            $columnFields = [];
            $variantAttributes = $type->getVariantAttributes();
            $variantOptionFields = array_intersect($variantAttributes, $this->attributeRegistry->getVariantOptionFields());

            $GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['headerFields'] = array_intersect(
                $GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['headerFields'],
                $type->getAttributes(),
            );

            if (\in_array('images', $variantAttributes, true)) {
                $columnFields[] = 'images';
            }

            if (\in_array('sku', $variantAttributes, true)) {
                $columnFields[] = 'sku';
                $GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['fields'] = ['sku'];
            }

            if (\in_array('name', $variantAttributes, true)) {
                $columnFields[] = 'name';
                $GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['fields'] = ['name'];
            }

            if (\in_array('price', $variantAttributes, true)) {
                $columnFields[] = 'price';
            }

            $variantOptionFields = array_diff($variantOptionFields, $columnFields);

            // Limit the number of columns if there are more than 2
            if (\count($variantOptionFields) > 4) {
                $columnFields[] = 'variantFields';
                $GLOBALS['TL_DCA']['tl_iso_product']['list']['label']['variantFields'] = $variantOptionFields;
            } else {
                foreach (array_merge($variantOptionFields) as $name) {
                    $attribute = $this->attributeRegistry->get($name);

                    if (
                        $attribute instanceof OptionsAttributeInterface
                        && OptionsSource::Table === $attribute->getOptionsSource()
                    ) {
                        $name .= ':tl_iso_attribute_option.label';
                    }

                    $columnFields[] = $name;
                }
            }

            $GLOBALS['TL_DCA']['tl_iso_product']['list']['label']['fields'] = $columnFields;

            // Make all column fields sortable
            foreach (array_keys($GLOBALS['TL_DCA']['tl_iso_product']['fields']) as $name) {
                $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$name]['sorting'] = \in_array($name, array_diff($columnFields, ['price', 'variantFields', 'images']), true);

                if (!\in_array($name, $variantAttributes, true)) {
                    unset(
                        $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$name]['filter'],
                        $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$name]['search'],
                    );
                }
            }

            return;
        }

        $config = $isVariant ? $type->getVariantAttributes() : $type->getAttributes();

        foreach (array_intersect(array_keys($GLOBALS['TL_DCA']['tl_iso_product']['fields']), $config) as $field) {
            $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$field]['attributes_callback'][] = static function (array $attributes) use ($field) {
                // Apply product type attribute config
                if (!empty($config[$field]['tl_class'])) {
                    $attributes['tl_class'] = $config[$field]['tl_class'];
                }

                $mandatory = $config[$field]['mandatory'] ?? null;

                if ('yes' === $mandatory) {
                    $attributes['mandatory'] = true;
                } elseif ('no' === $mandatory) {
                    $attributes['mandatory'] = false;
                }

                return $attributes;
            };
        }
    }

    #[AsCallback('tl_iso_product', 'config.onpalette')]
    public function buildPaletteForProductType(string $palette, ProductData $dc): string
    {
        [$type, $isVariant] = $this->getCurrent($dc);

        if (!$type) {
            return $palette;
        }

        $dcaFields = &$GLOBALS['TL_DCA']['tl_iso_product']['fields'];

        $palettes = [];
        $legends = [];
        $legendOrder = [];

        if ($isVariant) {
            $attributeConfig = $type->variant_attributes;
        } else {
            $attributeConfig = $type->attributes;
        }

        $enabled = $this->getAvailableFieldsForType($type, $isVariant);

        // Go through each enabled field and build palette
        foreach ($dcaFields as $name => $field) {
            if (\in_array($name, $enabled, true)) {
                $legendOrder[$attributeConfig[$name]['position']] = $attributeConfig[$name]['legend'];

                if ('price' === $name && $type->hasAdvancedPrices()) {
                    $palettes[$attributeConfig[$name]['legend']][$attributeConfig[$name]['position']] = 'prices';
                } else {
                    $palettes[$attributeConfig[$name]['legend']][$attributeConfig[$name]['position']] = $name;
                }
            }
        }

        ksort($legendOrder);
        $legendOrder = array_unique($legendOrder);

        // Build
        foreach ($legendOrder as $legend) {
            $fields = $palettes[$legend];
            ksort($fields);
            $legends[] = '{'.$legend.'},'.implode(',', $fields);
        }

        return ($isVariant ? 'inherit,' : '').implode(';', $legends);
    }

    #[AsCallback('tl_iso_product', 'fields.inherit.options')]
    public function getInheritOptions(ProductData $dc): array
    {
        [$type, $isVariant] = $this->getCurrent($dc);

        if (!$type || !$isVariant) {
            return [];
        }

        $inherit = [];
        $canInherit = $type->getAttributes();
        $enabled = $this->getAvailableFieldsForType($type, $isVariant);

        foreach ($enabled as $name) {
            $attribute = $this->attributeRegistry->get($name);

            if (
                $attribute instanceof VariantAttributeInterface
                && \in_array($name, $canInherit, true)
                && !\in_array($name, ['price', 'published', 'start', 'stop'], true)
                && !$attribute->isVariantOption()
            ) {
                $inherit[$name] = $this->formatter->dcaLabel('tl_iso_product', $name);
            }
        }

        return $inherit;
    }

    /**
     * @return array{
     *     0: ProductType|null,
     *     2: bool
     * }
     */
    private function getCurrent(ProductData $dc): array
    {
        $variant = null;
        $product = $dc->getCurrentRecord();

        if (!$product) {
            return [null, false];
        }

        if ($product['pid'] > 0) {
            $variant = $product;
            $product = $dc->getCurrentRecord($product['pid']);
        }

        $type = ProductType::findById($product['type']);

        if (!$type) {
            return [null, false];
        }

        return [$type, null !== $variant];
    }

    private function getAvailableFieldsForType(ProductType $type, bool $forVariant): array
    {
        $dcaFields = &$GLOBALS['TL_DCA']['tl_iso_product']['fields'];
        $selected = $forVariant ? $type->getVariantAttributes() : $type->getAttributes();
        $available = [];

        foreach ($selected as $name) {
            $field = $dcaFields[$name];

            if (empty($field['inputType']) && empty($field['input_field_callback'])) {
                continue;
            }

            $objAttribute = $this->attributeRegistry->get($name);

            // Variant fields can only be edited in variant mode
            if (
                !$forVariant
                && $objAttribute instanceof VariantAttributeInterface
                && $objAttribute->isVariantOption()
            ) {
                continue;
            }

            // Field cannot be edited in variant
            if ($forVariant && ($field['attributes']['inherit'] ?? false)) {
                continue;
            }

            $available[] = $name;
        }

        return $available;
    }
}
