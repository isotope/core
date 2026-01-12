<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Product;

use Contao\BackendUser;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Symfony\Bundle\SecurityBundle\Security;

#[AsCallback('tl_iso_product', 'config.onload')]
class PermissionListener
{
    public function __construct(private readonly Security $security)
    {
    }

    public function __invoke(DataContainer $dc): void
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        $user = $this->security->getUser();

        if (
            !$user instanceof BackendUser
            || $dc->currentPid > 0
        ) {
            return;
        }

        if ([] === $user->iso_producttypes) {
            $GLOBALS['TL_DCA']['tl_iso_product']['config']['closed'] = true;
            $GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['filter'][] = sprintf('type IN (0)');
            return;
        }

        $GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['filter'][] = sprintf('type IN (0, %s)', implode(',', $user->iso_producttypes));
    }
}
