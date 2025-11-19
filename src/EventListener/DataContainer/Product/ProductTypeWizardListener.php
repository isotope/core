<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Product;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Isotope\CoreBundle\Controller\Backend\ShopConfigController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

#[AsCallback('tl_iso_product', 'fields.type.wizard')]
class ProductTypeWizardListener
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly Environment $twig,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function __invoke(DataContainer $dc): string
    {
        $href = null;

        if ($dc->value) {
            $href = $this->urlGenerator->generate(ShopConfigController::class, [
                'table' => 'tl_iso_producttype',
                'act' => 'edit',
                'id' => $dc->value,
            ]);
        }

        return $this->twig->render('@Contao/backend/isotope/edit-wizard.html.twig', [
            'href' => $href,
            'table' => 'tl_iso_producttype',
            'value' => $dc->value,
        ]);
    }
}
