<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\TaxClass;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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

    public function __invoke(DataContainer $dc): string
    {
        $href = null;

        if ($dc->value) {
            $href = $this->urlGenerator->generate('contao_backend', [
                'do' => 'iso_setup',
                'table' => 'tl_iso_tax_rate',
                'act' => 'edit',
                'id' => $dc->value,
            ]);
        }

        return $this->twig->render('@Contao/backend/isotope/edit-wizard.html.twig', [
            'href' => $href,
            'value' => $dc->value,
        ]);
    }
}
