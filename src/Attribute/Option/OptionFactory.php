<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Attribute\Option;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\Util\LocaleUtil;
use Contao\Model\Collection;
use Contao\StringUtil;
use Contao\Widget;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\Attribute\AbstractAttribute;
use Isotope\CoreBundle\Attribute\OptionsAttributeInterface;
use Isotope\CoreBundle\Attribute\VariantAttributeInterface;
use Isotope\CoreBundle\Exception\UnderflowException;
use Isotope\CoreBundle\Exception\UnexpectedValueException;
use Isotope\CoreBundle\Model\AttributeOptionModel;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Store\Context\ProductContext;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Price\PriceCalculator;
use Isotope\CoreBundle\Store\Price\PriceDefinition;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class OptionFactory
{
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly Connection $connection,
        private readonly TranslatorInterface $translator,
        private readonly PriceCalculator $priceCalculator,
        private readonly Environment $twig,
        private readonly ScopeMatcher $scopeMatcher,
    ) {
    }

    public function create(OptionsAttributeInterface $attribute, ProductContext|null $productContext, array|null $attributeOptions, string|null $foreignKey): OptionCollection
    {
        $isVariant = $attribute instanceof VariantAttributeInterface && $attribute->isVariantOption();
        $source = $attribute->getOptionsSource();

        switch ($source) {
            case OptionsSource::Name:
                // TODO: getName() is only implemented by AbstractAttribute
                if ($attribute instanceof AbstractAttribute) {
                    return new OptionCollection([new Option('1', $attribute->getName())]);
                }

                return new OptionCollection([new Option('1', $attribute->getFieldName())]);

            case OptionsSource::Attribute:
                return $this->createFromArray($attributeOptions ?? [], !$isVariant);

            case OptionsSource::ForeignKey:
                return $this->createFromTable($foreignKey, $productContext?->getContext());

            case OptionsSource::Table:
                return $this->createFromModels($this->framework->getAdapter(AttributeOptionModel::class)->findByAttribute($attribute), !$isVariant, $productContext);

            case OptionsSource::Product:
                $product = $productContext?->getProduct();

                if (!$product instanceof CoreProduct) {
                    return new OptionCollection();
                }

                return $this->createFromModels($this->framework->getAdapter(AttributeOptionModel::class)->findByProductAndAttribute($product, $attribute), !$isVariant, $productContext);

            default:
                $config = Widget::getAttributesFromDca($GLOBALS['TL_DCA']['tl_iso_product']['fields'][$attribute->getFieldName()], $attribute->getFieldName());

                if (!isset($config['options']) || !\is_array($config['options'])) {
                    throw new UnexpectedValueException('Invalid options source "'.$source?->value.'" for tl_iso_attribute.'.$attribute->getFieldName());
                }

                return $this->createFromArray($config['options'], !$isVariant);
        }
    }

    private function createFromArray(array $options, bool $canHaveDefault): OptionCollection
    {
        $data = [];

        foreach ($options as $option) {
            $data[] = new Option(
                (string) $option['value'],
                $this->translator->trans($option['label'], [], 'isotope'),
                (bool) ($option['group'] ?? null),
                $canHaveDefault && ($option['default'] ?? null),
            );
        }

        return new OptionCollection($data);
    }

    /**
     * @param Collection<AttributeOptionModel>|null $models
     */
    private function createFromModels(Collection|null $models, bool $canHaveDefault, ProductContext|null $productContext): OptionCollection
    {
        if (null === $models) {
            return new OptionCollection();
        }

        $data = [];

        /** @var AttributeOptionModel $model */
        foreach ($models as $model) {
            $option = new Option(
                (string) $model->getLanguageId(),
                $this->translator->trans($model->label, [], 'isotope'),
                'group' === $model->type,
                $canHaveDefault && $model->isDefault,
                $model->cssClass,
            );

            if (
                $productContext
                && ($priceDefinition = PriceDefinition::fromString($model->price, PriceDefinition::SPLIT_TAX))
                && ($productPrice = $productContext->getProduct()->getPrice())
            ) {
                $context = $productContext->getContext();
                $price = $this->priceCalculator->calculatePriceDefinition($priceDefinition, $productPrice, $context);

                $option->setPrice($price);
                $option->setLabel($this->twig->render('@Contao/component/_isotope_option_price.html.twig', [
                    'label' => $option->getLabel(),
                    'price' => $model->price,
                    'amount' => $price->getUnitPrice(),
                    'currency' => $context->getCurrency(),
                    'money' => $context->getMoneyFormat(),
                ]));
            } elseif ($model->price && $this->scopeMatcher->isBackendRequest()) {
                $option->setLabel(\sprintf('%s (%s)', $option->getLabel(), $model->price));
            }

            $data[] = $option;
        }

        return new OptionCollection($data);
    }

    private function createFromTable(string|null $foreignKey, StoreContext|null $context): OptionCollection
    {
        if (empty($foreignKey)) {
            return new OptionCollection();
        }

        $data = [];

        try {
            [$table, $field] = $this->parseForeignKey($foreignKey, $context?->getLocale() ?? $GLOBALS['TL_LANGUAGE']);
        } catch (\UnderflowException) {
            return new OptionCollection();
        }

        $results = $this->connection->iterateAssociative(
            "SELECT id AS value, $field AS label FROM $table ORDER BY label",
        );

        foreach ($results as $result) {
            $data[] = new Option((string) $result['value'], $result['label']);
        }

        return new OptionCollection($data);
    }

    /**
     * @return array{0: string|null, 1: string|null}
     */
    private function parseForeignKey(string $config, string $locale): array
    {
        $fallback = null;
        $languageFallback = null;
        $locale = LocaleUtil::formatAsLanguageTag($locale);
        $lines = StringUtil::trimsplit('@\r\n|\n|\r@', $config);

        if ('' === $config || !\is_array($lines) || empty($lines)) {
            throw new UnderflowException('foreignKey is empty');
        }

        // Loop over the lines
        foreach ($lines as $line) {
            // Ignore empty lines and comments
            if ('' === $line || str_starts_with($line, '#')) {
                continue;
            }

            // Check for a language
            if (preg_match('/^([a-z]{2}(-[A-Z]{2})?)=(.+)$/', $line, $matches)) {
                $foreignKey = $matches[3];

                if ($matches[1] === $locale) {
                    return explode('.', $foreignKey, 2);
                }

                $languageFallback ??= $foreignKey;
            } elseif (null === $fallback) {
                // The row without language is the fallback
                $fallback = $line;
            }
        }

        $fallback ??= $languageFallback;

        if (null === $fallback) {
            throw new UnderflowException('foreignKey contains no table and field');
        }

        return explode('.', $fallback, 2);
    }
}
