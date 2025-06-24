<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Attribute;

use Isotope\Interfaces\IsotopeAttribute;

class CoreAttributeFactory implements AttributeFactoryInterface
{
    public function create(string $fieldName, array $config, array $dca): AttributeInterface|null
    {
        /** @var class-string<IsotopeAttribute> $class */
        $class = match ($class = ($config['type'] ?? $dca['inputType'] ?? null)) {
            'text' => TextAttribute::class,
            'textarea' => TextareaAttribute::class,
            'select' => SelectAttribute::class,
            'radio' => RadioAttribute::class,
            'checkbox' => CheckboxAttribute::class,
            'media_manager' => MediaManagerAttribute::class,
            default => $class,
        };

        if (!$class || !is_a($class, AbstractAttribute::class, true)) {
            return null;
        }

        $attribute = $this->createInstance($class, $fieldName, $config, $dca)
            ->setName($config['name'] ?? $dca['label'][0] ?? null)
            ->setDescription($config['description'] ?? $dca['label'][1] ?? null)
            ->setPlaceholder($config['placeholder'] ?? $dca['eval']['placeholder'] ?? '')
            ->setCustomerDefined((bool) ($config['customer_defined'] ?? $dca['attributes']['customer_defined'] ?? false))
            ->setMandatory((bool) ($config['mandatory'] ?? $dca['eval']['mandatory'] ?? false))
            ->setBackendSearchable((bool) ($config['be_search'] ?? $dca['search'] ?? false))
            ->setBackendFilterable((bool) ($config['be_filter'] ?? $dca['filter'] ?? false))
            ->setFrontendSearchable((bool) ($config['fe_search'] ?? $dca['attributes']['fe_search'] ?? false))
            ->setFrontendFilterable((bool) ($config['fe_filter'] ?? $dca['attributes']['fe_filter'] ?? false))
            ->setFrontendSortable((bool) ($config['fe_sorting'] ?? $dca['attributes']['fe_sorting'] ?? false))
            ->setCustomTemplate($config['customTpl'] ?? $dca['attributes']['customTpl'] ?? null)
        ;

        if ($attribute instanceof TextAttribute) {
            $rgxp = $config['rgxp'] ?? $dca['eval']['rgxp'] ?? null;

            $attribute
                ->setMinlength($config['minlength'] ?? $dca['eval']['minlength'] ?? null)
                ->setMaxLength($config['maxlength'] ?? $dca['eval']['maxlength'] ?? null)
                ->setRgxp($rgxp)
                ->setDatepicker(($config['datepicker'] ?? $dca['eval']['datepicker'] ?? false) && \in_array($rgxp, ['date', 'time', 'datim'], true))
                ->setTranslatable((bool) ($config['multilingual'] ?? $dca['attributes']['multilingual'] ?? false))
            ;
        } elseif ($attribute instanceof TextareaAttribute) {
            $attribute
                ->setRte($config['rte'] ?? $dca['eval']['rte'] ?? null)
                ->setTranslatable((bool) ($config['multilingual'] ?? $dca['attributes']['multilingual'] ?? false))
            ;
        } elseif ($attribute instanceof SelectAttribute) {
            $attribute
                ->setVariantOption((bool) ($config['variant_option'] ?? false))
                ->setIncludeBlankOption((bool) ($config['includeBlankOption'] ?? $dca['eval']['includeBlankOption'] ?? false))
                ->setBlankOptionLabel($config['blankOptionLabel'] ?? $dca['eval']['blankOptionLabel'] ?? null)
                ->setMultiple((bool) ($config['multiple'] ?? $dca['eval']['multiple'] ?? false))
                ->setSize((int) ($config['size'] ?? $dca['eval']['size'] ?? 1))
                ->setChosen((bool) ($config['chosen'] ?? $dca['eval']['chosen'] ?? false))
            ;
        } elseif ($attribute instanceof RadioAttribute) {
            $attribute
                ->setVariantOption((bool) ($config['variant_option'] ?? false))
            ;
        } elseif ($attribute instanceof MediaManagerAttribute) {
            $attribute
                ->setExtensions(explode(',', $config['extensions'] ?? $dca['eval']['extensions'] ?? ''))
            ;
        }

        return $attribute;
    }

    /**
     * @template T of AbstractAttribute
     *
     * @param class-string<T> $class
     *
     * @return AbstractAttribute<T>
     */
    private function createInstance(string $class, string $fieldName, array $config, array $dca): AbstractAttribute
    {
        $optionsSource = match (true) {
            isset($config['optionsSource']) => OptionsSource::tryFrom($config['optionsSource']),
            CheckboxAttribute::class === $class && !($config['multiple'] ?? $dca['eval']['multiple'] ?? false) => OptionsSource::Name,
            default => OptionsSource::Attribute,
        };

        return match ($class) {
            RadioAttribute::class,
            SelectAttribute::class,
            CheckboxAttribute::class => new $class($fieldName, $optionsSource),
            default => new $class($fieldName),
        };
    }
}
