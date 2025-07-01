<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\ProductCategory;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\Message;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsCallback('tl_iso_product_category', 'config.onload')]
class HintListener
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly Security $security,
    ) {
    }

    public function __invoke(): void
    {
        if (
            null !== $this->requestStack->getCurrentRequest()->query->get('act')
            || !$this->security->isGranted(ContaoCorePermissions::USER_CAN_ACCESS_FRONTEND_MODULES)
        ) {
            return;
        }

        Message::addInfo($GLOBALS['TL_LANG']['tl_iso_product_category']['hint'] ?? '');
    }
}
