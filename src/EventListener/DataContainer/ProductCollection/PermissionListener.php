<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\ProductCollection;

use Contao\BackendUser;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\SecurityBundle\Security;

#[AsCallback('tl_iso_product_collection', 'config.onload')]
class PermissionListener
{
    public function __construct(
        private readonly Security $security,
        private readonly Connection $connection,
    ) {
    }

    public function __invoke(DataContainer $dc): void
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        $user = $this->security->getUser();

        if (!$user instanceof BackendUser) {
            return;
        }

        // Only admins can delete orders. Improve performance by not triggering voter on every row.
        unset($GLOBALS['TL_DCA']['tl_iso_product_collection']['list']['operations']['delete']);

        if (\is_array($user->iso_configs) && [] !== $user->iso_configs) {
            $GLOBALS['TL_DCA']['tl_iso_product_collection']['list']['sorting']['filter'][] = 'config_id IN ('.implode(',', array_map(intval(...), $user->iso_configs)).')';
        }

        if (\is_array($user->iso_member_groups) && [] !== $user->iso_member_groups) {
            $GLOBALS['TL_DCA']['tl_iso_product_collection']['list']['sorting']['filter'][] = 'member IN ('.implode(',', $this->getMemberInGroups($user->iso_member_groups)).')';
        }
    }

    /**
     * @param array<int> $groupIds
     *
     * @return array<int>
     */
    private function getMemberInGroups(array $groupIds): array
    {
        $groupIds = array_map(intval(...), $groupIds);

        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select('id')
            ->from('tl_member')
        ;

        foreach ($groupIds as $id) {
            if (-1 === $id) {
                continue;
            }

            $qb->orWhere('groups LIKE '.$qb->createNamedParameter('%"'.$id.'"%'));
            $qb->orWhere('groups LIKE '.$qb->createNamedParameter('%i:'.$id.';%'));
        }

        $memberIds = $qb->fetchFirstColumn();

        if (\in_array(-1, $groupIds, true)) {
            array_unshift($memberIds, 0);
        }

        return $memberIds;
    }
}
