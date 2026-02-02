<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Twig;

use Hashids\Hashids;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

#[Autoconfigure(bind: [
    '$hashids' => '@isotope.hashids',
])]
class IsotopeExtension extends AbstractExtension
{
    public function __construct(
        private readonly Hashids $hashids,
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('hashid', $this->hashids->encode(...)),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('isotope_product', [ProductRuntime::class, 'generateProduct'], ['is_safe' => ['html']]),
            new TwigFunction('isotope_price', [ProductRuntime::class, 'generatePrice'], ['is_safe' => ['html']]),
            new TwigFunction('isotope_baseprice', [ProductRuntime::class, 'generateBasePrice'], ['needs_context' => true]),
            new TwigFunction('isotope_product_action', [ActionRuntime::class, 'renderProductAction'], ['is_safe' => ['html']]),
            new TwigFunction('isotope_product_collection_action', [ActionRuntime::class, 'renderProductCollectionAction'], ['needs_context' => true, 'is_safe' => ['html']]),
            new TwigFunction('isotope_product_collection_item_action', [ActionRuntime::class, 'renderProductCollectionItemAction'], ['needs_context' => true, 'is_safe' => ['html']]),
            new TwigFunction('isotope_attribute_label', [AttributeRuntime::class, 'getLabel']),
            new TwigFunction('isotope_attribute_value', [AttributeRuntime::class, 'getValue']),
            new TwigFunction('isotope_gallery', [GalleryRuntime::class, 'get'], ['needs_context' => true]),
        ];
    }
}
