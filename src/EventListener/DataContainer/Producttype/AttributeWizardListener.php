<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Producttype;

use Codefog\HasteBundle\Formatter;
use Contao\Backend;
use Contao\Controller;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Attribute\VariantAttributeInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class AttributeWizardListener
{
    public function __construct(
        private readonly AttributeRegistry $attributeRegistry,
        private readonly Connection $connection,
        private readonly Environment $twig,
        private readonly TranslatorInterface $translator,
        private readonly Formatter $formatter,
    ) {
    }

    #[AsCallback('tl_iso_producttype', 'fields.attributes.attributes')]
    #[AsCallback('tl_iso_producttype', 'fields.variant_attributes.attributes')]
    public function getColumns(array $attributes, DataContainer $dc): array
    {
        $currentRecord = $dc->getCurrentRecord();

        $values = $this->load($currentRecord[$dc->field], $dc);

        if (!\is_array($values)) {
            $values = [];
        }

        $fixed = [] !== $values ? $this->getFixedFields($this->isVariants($dc->field), $currentRecord['class'] ?? null) : [];
        $enabledConfig = $values;
        $mandatoryConfig = $values;

        $legends = $this->getLegends($values);

        $attributes['fields'] = [
            'enabled' => [
                'inputType' => 'checkbox',
                'eval' => ['cell_style' => 'white-space:nowrap;width:1%'],
                'attributes_callback' => [static function (array $attributes) use ($fixed, &$enabledConfig) {
                    $config = array_shift($enabledConfig);

                    if ($config && \in_array($config['name'], $fixed, true)) {
                        $attributes['disabled'] = true;
                    }

                    return $attributes;
                }],
            ],
            'name' => [
                'input_field_callback' => $this->getNameClosure($dc->field, $values),
            ],
            'legend' => [
                'label' => &$GLOBALS['TL_LANG']['tl_iso_producttype']['attributes']['legend'],
                'inputType' => 'select',
                'options' => $legends,
                'eval' => ['class' => 'extendable', 'data-controller' => 'isotope--extendable-select'],
            ],
            'tl_class' => [
                'label' => &$GLOBALS['TL_LANG']['tl_iso_producttype']['attributes']['tl_class'],
                'inputType' => 'text',
                'eval' => ['cell_style' => 'width:100px'],
            ],
            'mandatory' => [
                'label' => &$GLOBALS['TL_LANG']['tl_iso_producttype']['attributes']['mandatory'],
                'inputType' => 'select',
                'options' => ['yes', 'no'],
                'reference' => &$GLOBALS['TL_LANG']['MSC'],
                'eval' => [
                    'includeBlankOption' => true,
                    'blankOptionLabel' => &$GLOBALS['TL_LANG']['tl_iso_producttype']['attributes']['default'],
                    'cell_style' => 'width:100px'
                ],
                'attributes_callback' => [function (array $attributes) use (&$mandatoryConfig) {
                    $config = array_shift($mandatoryConfig);

                    if (!($config['name'] ?? null)) {
                        return $attributes;
                    }

                    $attribute = $this->attributeRegistry->get($config['name']);

                    if ($attribute instanceof VariantAttributeInterface && $attribute->isVariantOption()) {
                        $attributes['disabled'] = true;
                    }

                    return $attributes;
                }]
            ],
            'edit' => [
                'input_field_callback' => $this->getEditAttributeClosure($values),
                'eval' => ['cell_class' => 'tl_right'],
            ],
        ];

        return $attributes;
    }

    #[AsCallback('tl_iso_producttype', 'fields.attributes.load')]
    #[AsCallback('tl_iso_producttype', 'fields.variant_attributes.load')]
    public function load(mixed $value, DataContainer $dc): array
    {
        Controller::loadDataContainer('tl_iso_product');

        $dca = &$GLOBALS['TL_DCA']['tl_iso_product']['fields'];
        $fields = [];
        $isVariants = $this->isVariants($dc->field);
        $fixed = $this->getFixedFields($isVariants, $dc->getCurrentRecord()['class'] ?? null);
        $excluded = $this->getExcludedFields($isVariants);
        $value = StringUtil::deserialize($value);

        if (!\is_array($value)) {
            $value = [];
        }

        $shouldSkip = fn (string $name, array $config) => empty($config['attributes']['legend'])
            || ($isVariants && ($config['attributes']['inherit'] ?? false))
            || (!$isVariants && $this->isVariantAttribute($name))
            || \in_array($name, $excluded, true);

        // Add enabled fields from current widget value
        foreach ($value as $config) {
            $name = $config['name'] ?? null;

            if (
                !$name
                || !isset($dca[$name])
                || $shouldSkip($name, $dca[$name])
            ) {
                continue;
            }

            if (empty($config['legend'])) {
                $config['legend'] = $dca[$name]['attributes']['legend'] ?? '';
            }

            $fields[$name] = $config;
        }

        // Add fields from DCA that are not in widget value (= not enabled)
        foreach (array_diff_key($dca, $fields) as $name => $field) {
            if ($shouldSkip($name, $field)) {
                continue;
            }

            $fields[$name] = [
                'enabled' => \in_array($name, $fixed, true) ? '1' : '',
                'name' => $name,
                'legend' => $field['attributes']['legend'],
            ];
        }

        return array_values($fields);
    }

    #[AsCallback('tl_iso_producttype', 'fields.attributes.save')]
    #[AsCallback('tl_iso_producttype', 'fields.variant_attributes.save')]
    public function save(mixed $value, DataContainer $dc): string
    {
        $legends = [];
        $value = StringUtil::deserialize($value);
        $isVariants = $this->isVariants($dc->field);
        $hasVariantField = !$isVariants;

        if (empty($value) || !\is_array($value)) {
            return $value;
        }

        $fixed = $this->getFixedFields($isVariants, $dc->getCurrentRecord()['class'] ?? null);
        $input = Input::post($dc->field);

        foreach ($value as $k => $config) {
            $name = $config['name'] ?? $input[$k]['name'] ?? null;
            $value[$k]['name'] = $name;

            if (\in_array($name, $fixed, true)) {
                $value[$k]['enabled'] = '1';
            }

            $legends[] = $config['legend'];
            $hasVariantField = $hasVariantField || ($config['enabled'] && $this->isVariantAttribute($name));
        }

        // Make sure at least one variant attribute is enabled
        if (!$hasVariantField) {
            $variantLabels = implode(', ', array_map(fn (string $name) => $this->formatter->dcaLabel('tl_iso_product', $name), $this->attributeRegistry->getVariantOptionFields()));

            throw new \UnderflowException($this->translator->trans('tl_iso_producttype.noVariantAttributes', [$variantLabels], 'contao_tl_iso_producttype'));
        }

        usort(
            $value,
            static function ($a, $b) use ($legends) {
                if ($a['enabled'] && !$b['enabled']) {
                    return -1;
                }

                if ($b['enabled'] && !$a['enabled']) {
                    return 1;
                }

                if ($a['legend'] === $b['legend']) {
                    return 0;
                }

                $posA = array_search($a['legend'], $legends, true);
                $posB = array_search($b['legend'], $legends, true);

                return $posA <=> $posB;
            },
        );

        $values = [];

        foreach (array_values($value) as $pos => $config) {
            if (!$config['name']) {
                continue;
            }

            $config['position'] = $pos;
            $values[$config['name']] = $config;
        }

        return serialize($values);
    }

    #[AsCallback('tl_iso_producttype', 'config.onbeforesubmit')]
    public function validateSingularFields(array $values, DataContainer $dc): array
    {
        $singular = $this->getSingularFields();
        $data = array_merge($dc->getCurrentRecord(), $values);

        if (!$data['variants']) {
            return $values;
        }

        $duplicates = array_intersect(
            array_map(static fn ($v) => $v['name'], array_filter(StringUtil::deserialize($data['attributes'], true), static fn ($v) => $v['enabled'])),
            array_map(static fn ($v) => $v['name'], array_filter(StringUtil::deserialize($data['variant_attributes'], true), static fn ($v) => $v['enabled'])),
            $singular,
        );

        if ([] !== $duplicates) {
            throw new \LogicException($this->translator->trans('tl_iso_producttype.singularAttributes', [implode(', ', array_map(fn ($name) => $this->formatter->dcaLabel('tl_iso_product', $name), $duplicates))], 'contao_tl_iso_producttype'));
        }

        return $values;
    }

    /**
     * For each call, the closure returns the name of the next attribute in the wizard (for input_field_callback).
     */
    private function getNameClosure(string $widgetName, array $values): \Closure
    {
        System::loadLanguageFile('tl_iso_product');

        $i = 0;
        $current = [];

        return function () use ($widgetName, &$current, $values, &$i): string {
            if ([] === $current) {
                $current = $values;
            }

            $field = array_shift($current);

            if (null === $field) {
                return '';
            }

            $fieldName = $field['name'];
            $style = '';

            $attribute = $this->attributeRegistry->get($fieldName);

            if ($attribute instanceof VariantAttributeInterface && $attribute->isVariantOption()) {
                $style = ';font-style:italic';
            }

            return \sprintf(
                '<input type="hidden" name="%s[%s][name]" id="ctrl_%s_row%s_name" value="%s"><div style="width:300px%s">%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span></div>',
                $widgetName,
                $i,
                $widgetName,
                $i++,
                $fieldName,
                $style,
                $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$fieldName]['label'][0] ?? $fieldName,
                $fieldName,
            );
        };
    }

    private function getLegends(array $values): array
    {
        Controller::loadDataContainer('tl_iso_attribute');
        System::loadLanguageFile('tl_iso_product');

        $legends = $GLOBALS['TL_DCA']['tl_iso_attribute']['fields']['legend']['options'];
        $legends = array_intersect_key($GLOBALS['TL_LANG']['tl_iso_product'], array_flip($legends));

        foreach ($values as $field) {
            if ($field['legend'] && !isset($legends[$field['legend']])) {
                $legends[$field['legend']] = $field['legend'];
            }
        }

        return $legends;
    }

    private function getEditAttributeClosure(array $values): \Closure
    {
        System::loadLanguageFile('tl_iso_attribute');

        $attributes = $this->connection->fetchAllKeyValue('SELECT field_name, id FROM tl_iso_attribute');
        $current = [];

        return function () use (&$current, $values, $attributes) {
            if ([] === $current) {
                $current = $values;
            }

            $field = array_shift($current);

            if (null === $field) {
                return '';
            }

            $href = null;
            $value = null;

            if (isset($attributes[$field['name']])) {
                $href = Backend::addToUrl('mod=attributes&table=tl_iso_attribute&id='.$attributes[$field['name']]);
                $value = $attributes[$field['name']];
            }

            return $this->twig->render('@Contao/backend/isotope/edit-wizard.html.twig', [
                'href' => $href,
                'table' => 'tl_iso_attribute',
                'value' => $value,
            ]);
        };
    }

    /**
     * Returns whether we're currently handling variant attributes.
     */
    private function isVariants(string $fieldName): bool
    {
        return 'attributes' !== $fieldName;
    }

    /**
     * Return list of fixed fields that cannot be disabled in product type config.
     *
     * @return list<string>
     */
    private function getFixedFields(bool $variants, string|null $productType): array
    {
        Controller::loadDataContainer('tl_iso_product');

        $fields = [];
        $key = $variants ? 'variant_fixed' : 'fixed';
        $dca = &$GLOBALS['TL_DCA']['tl_iso_product']['fields'];

        foreach ($dca as $field => $config) {
            $fixed = ($config['attributes'][$key] ?? null);
            $isArray = \is_array($fixed);

            if (
                (!$isArray && $fixed)
                || (null !== $productType && $isArray && \in_array($productType, $fixed, true))
            ) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * Returns a list of attributes that cannot be used for products/variants.
     *
     * @return list<string>
     */
    private function getExcludedFields(bool $variants): array
    {
        Controller::loadDataContainer('tl_iso_product');

        $fields = [];
        $key = $variants ? 'variant_excluded' : 'excluded';
        $dca = &$GLOBALS['TL_DCA']['tl_iso_product']['fields'];

        foreach ($dca as $field => $config) {
            if ($config['attributes'][$key] ?? false) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * Returns a list fields that can only be enabled for product or variants, but not both.
     *
     * @return list<string>
     */
    private function getSingularFields(): array
    {
        Controller::loadDataContainer('tl_iso_product');

        $fields = [];
        $dca = &$GLOBALS['TL_DCA']['tl_iso_product']['fields'];

        foreach ($dca as $field => $config) {
            if ($config['attributes']['singular'] ?? false) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    private function isVariantAttribute(string $name): bool
    {
        return ($attribute = $this->attributeRegistry->get($name))
            && $attribute instanceof VariantAttributeInterface
            && $attribute->isVariantOption();
    }
}
