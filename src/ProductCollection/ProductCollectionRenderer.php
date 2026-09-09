<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection;

use Contao\CoreBundle\Twig\FragmentTemplate;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Price\PriceDisplay;
use Isotope\CoreBundle\Store\Price\TaxCalculation;
use Twig\Environment;

class ProductCollectionRenderer
{
    public function __construct(private readonly Environment $twig)
    {
    }

    public function renderCollection(string $name, array $context, AbstractProductCollection $collection, StoreContext $storeContext, int|null $galleryId = null): string
    {
        return $this->twig->render($name, [
            ...$context,
            ...$this->getContext($collection, $storeContext, $galleryId),
        ]);
    }

    public function addToTemplate(FragmentTemplate $template, AbstractProductCollection $collection, StoreContext $context, int|null $galleryId = null): void
    {
        foreach ($this->getContext($collection, $context, $galleryId) as $key => $value) {
            $template->set($key, $value);
        }
    }

    private function getContext(AbstractProductCollection $collection, StoreContext $context, int|null $galleryId = null): array
    {
        return [
            'collection' => $collection,
            'gallery_id' => $galleryId,
            'show_taxes' => TaxCalculation::Horizontal === $context->getTaxCalculation(),
            'multiple_taxes' => $collection->getTotal()?->getCalculatedTaxes()->count() > 1,
            'is_gross' => PriceDisplay::Net !== $context->getPriceDisplay(),
        ];
    }
}
