<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Product;

use Codefog\HasteBundle\Formatter;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\DataContainer;
use Contao\Input;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Attribute\VariantAttributeInterface;
use Isotope\CoreBundle\DataContainer\ProductData;
use Isotope\Model\ProductType;
use Isotope\Model\RelatedCategory;

/**
 * Loads attributes and configuration from product type into the tl_iso_product table.
 */
class AttributeListener
{
    public function __construct(
        private readonly AttributeRegistry $attributeRegistry,
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

    /**
     * Optimize the DCA by removing operations that are not used, so button_callbacks are not executed for no reason.
     * Also removes search and filter fields and viewable columns if they do not exist in any product type.
     */
    #[AsCallback('tl_iso_product', 'config.onload')]
    public function optimizeDca(DataContainer $dc): void
    {
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
                $GLOBALS['TL_DCA']['tl_iso_product']['list']['operations']['variants'],
                $GLOBALS['TL_DCA']['tl_iso_product']['list']['operations']['related'],
            );
        }

        $hasDownloads = false;
        $hasVariants = (bool) Input::get('id'); // Only check for variants if we are in product view
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
            }
        }

        $attributes = array_merge(...$attributes);

        // If no downloads are enabled in any product type, we do not need the operation
        if (!$hasDownloads) {
            unset($GLOBALS['TL_DCA']['tl_iso_product']['list']['operations']['downloads']);
        }

        // Disable variant button if there are no variants at all
        if (!$hasVariants) {
            unset($GLOBALS['TL_DCA']['tl_iso_product']['list']['operations']['variants']);
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
    public function registerAttributesCallback(ProductData $dc): void
    {
        $GLOBALS['TL_DCA']['tl_iso_product']['fields']['prices']['exclude'] = &$GLOBALS['TL_DCA']['tl_iso_product']['fields']['price']['exclude'];

        foreach ($GLOBALS['TL_DCA']['tl_iso_product']['fields'] as $name => $config) {
            $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$name]['attributes_callback'][] = $this->getAttributesCallback(...);
        }

        if ('show' === Input::get('act')) {
            [,$enabled] = $this->getCurrent($dc);

            foreach ($GLOBALS['TL_DCA']['tl_iso_product']['fields'] as $k => $field) {
                if (!($field['attributes']['systemColumn'] ?? false) && !\in_array($k, $enabled, true)) {
                    unset($GLOBALS['TL_DCA']['tl_iso_product']['fields'][$k]);
                }
            }
        }

        // TODO: Remove non-active fields from multi-selection
        if (\in_array(Input::get('act'), array('editAll', 'overrideAll'), true)) {
            //if ($blnVariants && !$blnSingleRecord) {
            //    $arrInclude = empty($palettes) ? array() : array_merge(...array_values($palettes));
            //
            //    foreach ($dcaFields as $name => $config) {
            //        if (($dcaFields[$name]['attributes']['legend'] ?? '') != '' && !\in_array($name, $arrInclude)) {
            //            $dcaFields[$name]['exclude'] = true;
            //        }
            //    }
            //}
        }
    }

    #[AsCallback('tl_iso_product', 'config.onpalette')]
    public function buildPaletteForProductType(string $palette, ProductData $dc): string
    {
        [$type, $enabled, $isVariant] = $this->getCurrent($dc);

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

        // Go through each enabled field and build palette
        foreach ($dcaFields as $name => $field) {
            if (\in_array($name, $enabled)) {
                $legendOrder[$attributeConfig[$name]['position']] = $attributeConfig[$name]['legend'];
                $palettes[$attributeConfig[$name]['legend']][$attributeConfig[$name]['position']] = $name;
            }
        }

        ksort($legendOrder);
        $legendOrder = array_unique($legendOrder);

        // Build
        foreach ($legendOrder as $legend) {
            $fields = $palettes[$legend];
            ksort($fields);
            $legends[] = '{' . $legend . '},' . implode(',', $fields);
        }

        return ($isVariant ? 'inherit,' : '') . implode(';', $legends);
    }

    #[AsCallback('tl_iso_product', 'fields.inherit.options')]
    public function getInheritOptions(ProductData $dc): array
    {
        [$type, $enabled, $isVariant] = $this->getCurrent($dc);

        if (!$type || !$isVariant) {
            return [];
        }

        $inherit = [];
        $canInherit = $type->getAttributes();

        foreach ($enabled as $name) {
            $attribute = $this->attributeRegistry->get($name);

            if ($attribute instanceof VariantAttributeInterface
                && !$attribute->isVariantOption()
                && \in_array($name, $canInherit, true)
                && !\in_array($name, ['price', 'published', 'start', 'stop'], true)
            ) {
                $inherit[$name] = $this->formatter->dcaLabel('tl_iso_product', $name);
            }
        }

        return $inherit;
    }

    private function getAttributesCallback(array $attributes, ProductData $dc): array
    {
        [$type,, $isVariant] = $this->getCurrent($dc);

        if (!$type) {
            return $attributes;
        }

        if ($isVariant) {
            $attributeConfig = $type->variant_attributes;
        } else {
            $attributeConfig = $type->attributes;
        }

        // Apply product type attribute config
        if (!empty($attributeConfig[$dc->field]['tl_class'])) {
            $attributes['tl_class'] = $attributeConfig[$dc->field]['tl_class'];
        }

        $mandatory = $attributeConfig[$dc->field]['mandatory'] ?? null;

        if ('yes' === $mandatory) {
            $attributes['mandatory'] = true;
        } elseif ('no' === $mandatory) {
            $attributes['mandatory'] = false;
        }

        return $attributes;
    }

    /**
     * @return array{
     *     0: ProductType|null,
     *     1: array<string>,
     *     2: bool
     * }
     */
    private function getCurrent(ProductData $dc): array
    {
        $variant = null;
        $product = $dc->getCurrentRecord();

        if ($product['pid'] > 0) {
            $variant = $product;
            $product = $dc->getCurrentRecord($product['pid']);
        }

        $type = ProductType::findById($product['type']);

        if (!$type) {
            return [null, [], false];
        }

        $dcaFields = &$GLOBALS['TL_DCA']['tl_iso_product']['fields'];
        $selected = null !== $variant ? $type->getVariantAttributes() : $type->getAttributes();
        $available = [];

        foreach ($dcaFields as $name => $field) {
            if (!\in_array($name, $selected, true)) {
                continue;
            }

            if (empty($field['inputType']) && empty($field['input_field_callback'])) {
                continue;
            }

            $objAttribute = $this->attributeRegistry->get($name);

            // Variant fields can only be edited in variant mode
            if (null === $variant
                && $objAttribute instanceof VariantAttributeInterface
                && $objAttribute->isVariantOption()
            ) {
                continue;
            }

            // Field cannot be edited in variant
            if (null !== $variant && ($field['attributes']['inherit'] ?? false)) {
                continue;
            }

            $available[] = $name;
        }

        return [$type, $available, null !== $variant];
    }
}
