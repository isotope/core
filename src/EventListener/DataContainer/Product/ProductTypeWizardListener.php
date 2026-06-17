<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Product;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\DataContainer;
use Isotope\CoreBundle\Security\IsotopeCorePermissions;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

#[AsCallback('tl_iso_product', 'fields.type.wizard')]
class ProductTypeWizardListener
{
    public function __construct(
        private readonly Security $security,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly Environment $twig,
    ) {
    }

    public function __invoke(DataContainer $dc): string
    {
        if (
            !$this->security->isGranted(IsotopeCorePermissions::USER_CAN_ACCESS_SETUP)
            || !$this->security->isGranted(IsotopeCorePermissions::USER_CAN_ACCESS_PRODUCTTYPES)
            || !$this->security->isGranted(ContaoCorePermissions::USER_CAN_OPERATE_ON_TABLE, 'tl_iso_product::update')
        ) {
            return '';
        }

        $href = null;

        if (
            $dc->value
            && $this->security->isGranted(IsotopeCorePermissions::USER_CAN_EDIT_PRODUCTTYPE, $dc->value)
        ) {
            $href = $this->urlGenerator->generate('contao_backend', [
                'do' => 'iso_setup',
                'table' => 'tl_iso_producttype',
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
