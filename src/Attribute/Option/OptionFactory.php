<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Attribute\Option;

use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\Model\Collection;
use Contao\StringUtil;
use Contao\Widget;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\Attribute\OptionsAttributeInterface;
use Isotope\CoreBundle\Attribute\VariantAttributeInterface;
use Isotope\CoreBundle\Exception\InvalidArgumentException;
use Isotope\CoreBundle\Exception\LogicException;
use Isotope\CoreBundle\Exception\UnderflowException;
use Isotope\CoreBundle\Exception\UnexpectedValueException;
use Isotope\CoreBundle\Model\AttributeOption;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Store\Context\ProductContext;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use Isotope\CoreBundle\Store\Price\PriceCalculator;
use Isotope\CoreBundle\Store\Price\PriceDefinition;
use Isotope\CoreBundle\Store\Price\PriceDisplay;
use Isotope\CoreBundle\Store\Tax\TaxCalculator;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class OptionFactory
{
    public function __construct(
        private readonly Connection $connection,
        private readonly TranslatorInterface $translator,
        private readonly TaxCalculator $taxCalculator,
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
                return new OptionCollection([new Option('1', $attribute->getName())]);

            case OptionsSource::Attribute:
                return $this->createFromArray($attributeOptions ?? [], !$isVariant);

            case OptionsSource::ForeignKey:
                return $this->createFromTable($foreignKey, $productContext?->getContext());

            case OptionsSource::Table:
                return $this->createFromModels(AttributeOption::findByAttribute($attribute), !$isVariant, $productContext);

            case OptionsSource::Product:
                $product = $productContext?->getProduct();

                if (!$product instanceof CoreProduct) {
                    throw new LogicException('Must pass product to '.__METHOD__.' if optionsSource is "product"');
                }

                return $this->createFromModels(AttributeOption::findByProductAndAttribute($product, $attribute), !$isVariant, $productContext);

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
                $option['value'],
                $this->translator->trans($option['label'], [], 'isotope'),
                (bool) ($option['group'] ?? null),
                $canHaveDefault && ($option['default'] ?? null),
            );
        }

        return new OptionCollection($data);
    }

    /**
     * @param Collection<AttributeOption>|null $models
     */
    private function createFromModels(Collection|null $models, bool $canHaveDefault, ProductContext|null $productContext): OptionCollection
    {
        if (null === $models) {
            return new OptionCollection();
        }

        $data = [];

        /** @var AttributeOption $model */
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
                $option->setLabel($this->twig->render('@Contao/isotope/option_price.html.twig', [
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
            $data[] = new Option($result['value'], $result['label']);
        }

        return new OptionCollection($data);
    }

    /**
     * @return array{0: string|null, 1: string|null}
     */
    private function parseForeignKey(string $config, string $locale): array
    {
        $fallback = null;
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

            // Check for a language1
            if (preg_match('/^([a-z]{2}(-[A-Z]{2})?)=(.+)$/', $line, $matches)) {
                $foreignKey = $matches[3];

                if ($matches[1] === $locale) {
                    return explode('.', $foreignKey, 2);
                }

                if (null === $fallback) {
                    $fallback = $foreignKey;
                }
            } elseif (null === $fallback) {
                // The row without language is the fallback
                $fallback = $line;
            }
        }

        return explode('.', $fallback, 2);
    }

    private function formatPrice(float $amount, StoreContext $context): string
    {
        $price = $this->twig->render('@Contao/component/isotope_price.html.twig', [
            'amount' => abs($amount),
            'html' => false,
            'currency' => $context->getCurrency(),
            'money' => $context->getMoneyFormat(),
        ]);

        if ($amount > 0) {
            return '+ '.$price;
        }

        if ($amount < 0) {
            return '- '.$price;
        }

        return $price;
    }
}
