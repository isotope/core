<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Twig;

use Hashids\Hashids;
use Isotope\CoreBundle\Checkout\Step\CheckoutStepSummary;
use Isotope\CoreBundle\Twig\Global\IsotopeVariable;
use Isotope\CoreBundle\Twig\Runtime\ActionRuntime;
use Isotope\CoreBundle\Twig\Runtime\AddressRuntime;
use Isotope\CoreBundle\Twig\Runtime\AttributeRuntime;
use Isotope\CoreBundle\Twig\Runtime\GalleryRuntime;
use Isotope\CoreBundle\Twig\Runtime\ProductRuntime;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\Runtime\EscaperRuntime;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * @internal
 */
class IsotopeExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly Environment $twig,
        private readonly IsotopeVariable $isotopeVariable,
        #[Autowire(service: 'isotope.hashids')] private readonly Hashids $hashids,
    ) {
        $runtime = $this->twig->getRuntime(EscaperRuntime::class);
        $runtime->addSafeClass(TemplateMarkup::class, ['html', 'contao_html']);
        $runtime->addSafeClass(CheckoutStepSummary::class, ['html', 'contao_html']);
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
            new TwigFunction('isotope_baseprice', [ProductRuntime::class, 'generateBasePrice'], ['is_safe' => ['html']]),
            new TwigFunction('isotope_product_action', [ActionRuntime::class, 'renderProductAction'], ['is_safe' => ['html']]),
            new TwigFunction('isotope_product_collection_action', [ActionRuntime::class, 'renderProductCollectionAction'], ['needs_context' => true, 'is_safe' => ['html']]),
            new TwigFunction('isotope_product_collection_item_action', [ActionRuntime::class, 'renderProductCollectionItemAction'], ['needs_context' => true, 'is_safe' => ['html']]),
            new TwigFunction('isotope_attribute_label', [AttributeRuntime::class, 'getLabel']),
            new TwigFunction('isotope_attribute_value', [AttributeRuntime::class, 'getValue']),
            new TwigFunction('isotope_gallery', [GalleryRuntime::class, 'get'], ['needs_context' => true]),
            new TwigFunction('isotope_address', [AddressRuntime::class, 'get']),
        ];
    }

    public function getGlobals(): array
    {
        return ['isotope' => $this->isotopeVariable];
    }
}
