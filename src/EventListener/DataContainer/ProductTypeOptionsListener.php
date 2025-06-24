<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Group;

use Contao\BackendUser;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\SecurityBundle\Security;

#[AsCallback('tl_iso_product', 'fields.type.options')]
class ProductTypeOptionsListener
{
    public function __construct(
        private readonly Security $security,
        private readonly Connection $connection,
    ) {
    }

    public function __invoke(): array
    {
        $user = $this->security->getUser();

        if (
            $this->security->isGranted('ROLE_ADMIN')
            || !$user instanceof BackendUser
            || !\is_array($user->iso_product_types)
            || [] === $user->iso_product_types
        ) {
            return $this->connection->fetchAllKeyValue('SELECT id, name FROM tl_iso_producttype WHERE tstamp>0');
        }

        return $this->connection->fetchAllKeyValue(
            'SELECT id, name FROM tl_iso_producttype WHERE tstamp>0 AND id IN (?)',
            [$user->iso_product_types],
            [ArrayParameterType::INTEGER],
        );
    }
}
