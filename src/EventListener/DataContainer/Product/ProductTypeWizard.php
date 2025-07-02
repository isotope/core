<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Product;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Isotope\CoreBundle\Controller\Backend\ShopConfigController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

#[AsCallback('tl_iso_product', 'fields.type.wizard')]
class ProductTypeWizard
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly Environment $twig,
    ) {
    }

    public function __invoke(DataContainer $dc): string
    {
        if (!$dc->value) {
            return '';
        }

        $href = $this->urlGenerator->generate(ShopConfigController::class, [
            'table' => 'tl_iso_producttype',
            'act' => 'edit',
            'id' => $dc->value,
        ]);

        return $this->twig->render('@Contao/backend/isotope/edit-wizard.html.twig', [
            'href' => $href,
            'title' => \sprintf($GLOBALS['TL_LANG']['tl_iso_producttype']['edit'][1], $dc->value),
        ]);
    }
}
