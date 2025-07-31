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

    public function __construct(private readonly Hashids $hashids)
    {
    }

    public function getFilters()
    {
        return [
            new TwigFilter('hashid', [$this->hashids, 'encode']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('isotope_price', [ProductRuntime::class, 'generatePrice'], ['needs_context' => true]),
            new TwigFunction('isotope_gallery', [GalleryRuntime::class, 'get'], ['needs_context' => true]),
        ];
    }
}
