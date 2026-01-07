<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Doctrine\DBAL\Connection;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCallback(table: 'tl_iso_payment', target: 'fields.groups.options')]
#[AsCallback(table: 'tl_iso_product', target: 'fields.groups.options')]
#[AsCallback(table: 'tl_iso_shipping', target: 'fields.groups.options')]
#[AsCallback(table: 'tl_user', target: 'fields.iso_member_groups.options')]
#[AsCallback(table: 'tl_user_group', target: 'fields.iso_member_groups.options')]
class MemberGroupsListener
{
    public function __construct(
        private readonly Connection $connection,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function __invoke(): array
    {
        $options = [-1 => $this->translator->trans('MSC.guests', [], 'contao_default')];
        $groups = $this->connection->fetchAllAssociative('SELECT id, name FROM tl_member_group WHERE tstamp>0 ORDER BY name');

        foreach ($groups as $group) {
            $options[$group['id']] = $group['name'];
        }

        return $options;
    }
}
