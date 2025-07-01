<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener;

use Contao\BackendUser;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\Security\IsotopeCorePermissions;
use Symfony\Bundle\SecurityBundle\Security;

#[AsHook('getSystemMessages')]
class NewOrderMessagesListener
{
    public function __construct(
        private readonly Connection $connection,
        private readonly Security $security,
    ) {
    }

    public function __invoke(): string
    {
        if (!$this->security->isGranted(IsotopeCorePermissions::USER_CAN_ACCESS_ORDERS)) {
            return '';
        }

        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select(['COUNT(*) AS total', 's.name'])
            ->from('tl_iso_product_collection', 'c')
            ->leftJoin('c', 'tl_iso_orderstatus', 's', 'c.order_status=s.id')
            ->where("c.type='order'")
            ->andWhere('s.welcomescreen=1')
            ->groupBy('s.id')
        ;

        if (!$this->security->isGranted('ROLE_ADMIN')) {
            $user = $this->security->getUser();

            if (
                !$user instanceof BackendUser
                || empty($user->iso_configs)
                || !\is_array($user->iso_configs)
            ) {
                return '';
            }

            $qb
                ->andWhere('c.config_id IN (:configs)')
                ->setParameter('configs', $user->iso_configs, ArrayParameterType::INTEGER)
            ;
        }

        $messages = [];

        foreach ($qb->fetchAllAssociative() as $result) {
            $messages[] = '<p class="tl_new">'.\sprintf($GLOBALS['TL_LANG']['MSC']['newOrders'], $result['total'], $result['name']).'</p>';
        }

        return implode("\n", $messages);
    }
}
