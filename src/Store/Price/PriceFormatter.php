<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Price;

use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Twig\TemplateMarkup;
use Isotope\CoreBundle\Twig\TemplateMarkupFactory;

class PriceFormatter
{
    public function __construct(
        private readonly TemplateMarkupFactory $markupFactory,
    ) {
    }

    public function toHtml(CalculatedPrice $price, StoreContext $context, string|null $key = null): TemplateMarkup
    {
        $data = $this->getData($price, $context, $key);
        $data['html'] = true;

        return $this->markupFactory->create('@Contao/component/_isotope_price.html.twig', $data);
    }

    public function toText(CalculatedPrice $price, StoreContext $context, string|null $key = null): TemplateMarkup
    {
        $data = $this->getData($price, $context, $key);
        $data['html'] = false;

        return $this->markupFactory->create('@Contao/component/_isotope_price.html.twig', $data);
    }

    private function getData(CalculatedPrice $price, StoreContext $context, string|null $key): array
    {
        $format = $context->getMoneyFormat();

        $data =  [
            'unit_price' => $price->getUnitPrice(),
            'unit_label' => $price->getUnitLabel(),
            'unit_tax' => $format->mathRound($price->getCalculatedTaxes()->sum()),
            'total_price' => $price->getTotalPrice(),
            'is_range' => $price->isRange(),
            'calculated_taxes' => $price->getCalculatedTaxes(),
            'tax_ids' => $price->getCalculatedTaxes()->getNumbers(),
            'money' => $context->getMoneyFormat(),
            'currency' => $context->getCurrency(),
            'tax_calculation' => $context->getTaxCalculation()->value,
            'price_display' => $context->getPriceDisplay()->value,
            'is_gross' => PriceDisplay::Net !== $context->getPriceDisplay(),
        ];

        if (PriceDisplay::Net === $context->getPriceDisplay()) {
            $data['net_unit_price'] = $price->getUnitPrice();
            $data['gross_unit_price'] = $price->getUnitPrice() + $data['unit_tax'];
        } else {
            $data['gross_unit_price'] = $price->getUnitPrice();
            $data['net_unit_price'] = $price->getUnitPrice() - $data['unit_tax'];
        }

        $data['quantity'] = $price->getQuantity();
        $data['total_tax'] = $data['unit_tax'] * $price->getQuantity();
        $data['net_total_price'] = $data['net_unit_price'] * $price->getQuantity();
        $data['gross_total_price'] = $data['gross_unit_price'] * $price->getQuantity();
        $data['amount'] = $key ? ($data[$key] ?? null) : null;

        return $data;
    }
}
