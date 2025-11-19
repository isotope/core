<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\TaxClass;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Isotope\CoreBundle\Controller\Backend\ShopConfigController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Adds an edit icon to the included tax rate select menu.
 */
#[AsCallback('tl_iso_tax_class', 'fields.includes.wizard')]
class IncludesEditWizardListener
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly Environment $twig,
    ) {
    }

    public function __invoke(DataContainer $dc)
    {
        $href = null;

        if ($dc->value) {
            $href = $this->urlGenerator->generate(ShopConfigController::class, [
                'table' => 'tl_iso_tax_rate',
                'act' => 'edit',
                'id' => $dc->value,
            ]);
        }

        return $this->twig->render('@Contao/backend/isotope/edit-wizard.html.twig', [
            'href' => $href,
            'table' => 'tl_iso_tax_rate',
            'value' => $dc->value,
        ]);
    }
}
