<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer;

use Contao\BackendUser;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\StringUtil;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;

class CreatePermissionListener
{
    private const PERMISSIONS = [
        'tl_iso_config' => ['iso_configs', 'iso_configp'],
        'tl_iso_payment' => ['iso_payments', 'iso_paymentp'],
        'tl_iso_producttype' => ['iso_producttypes', 'iso_producttypep'],
        'tl_iso_shipping' => ['iso_shippings', 'iso_shippingp'],
        'tl_iso_tax_class' => ['iso_tax_classes', 'iso_tax_classp'],
        'tl_iso_tax_rate' => ['iso_tax_rates', 'iso_tax_ratep'],
    ];

    public function __construct(
        private readonly Security $security,
        private readonly RequestStack $requestStack,
        private readonly Connection $connection,
    ) {
    }

    #[AsCallback('tl_iso_config', 'config.oncreate')]
    #[AsCallback('tl_iso_payment', 'config.oncreate')]
    #[AsCallback('tl_iso_producttype', 'config.oncreate')]
    #[AsCallback('tl_iso_shipping', 'config.oncreate')]
    #[AsCallback('tl_iso_tax_class', 'config.oncreate')]
    #[AsCallback('tl_iso_tax_rate', 'config.oncreate')]
    public function onCreate(string $table, int $insertId): void
    {
        $this->handle($insertId, $table);
    }

    #[AsCallback('tl_iso_config', 'config.oncopy')]
    #[AsCallback('tl_iso_payment', 'config.oncopy')]
    #[AsCallback('tl_iso_producttype', 'config.oncopy')]
    #[AsCallback('tl_iso_shipping', 'config.oncopy')]
    #[AsCallback('tl_iso_tax_class', 'config.oncopy')]
    #[AsCallback('tl_iso_tax_rate', 'config.oncopy')]
    public function onCopy(int $insertId, DataContainer $dc): void
    {
        $this->handle($insertId, $dc->table);
    }

    private function handle(int $insertId, string $table): void
    {
        [$accessField, $permissionField] = self::PERMISSIONS[$table];

        $user = $this->security->getUser();

        if (!$user instanceof BackendUser || $this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        if (empty($user->$accessField) || !\is_array($user->$accessField)) {
            $root = [0];
        } else {
            $root = $user->$accessField;
        }

        if (\in_array($insertId, $root, false)) {
            return;
        }

        /** @var AttributeBagInterface $sessionBag */
        $sessionBag = $this->requestStack->getSession()->getBag('contao_backend');
        $newRecords = $sessionBag->get('new_records');

        if (!\is_array($newRecords[$table]) || !\in_array($insertId, $newRecords[$table], false)) {
            return;
        }

        // Add the permissions on group level
        if ('custom' !== $user->inherit) {
            $groups = $this->connection->fetchAllAssociative(
                "SELECT id, $accessField, $permissionField FROM tl_user_group WHERE id IN(?)",
                [$user->groups],
                [ArrayParameterType::INTEGER],
            );

            foreach ($groups as $group) {
                $permissions = StringUtil::deserialize($group[$permissionField]);

                if (\is_array($permissions) && \in_array('create', $permissions, true)) {
                    $ids = (array) StringUtil::deserialize($group[$accessField], true);
                    $ids[] = $insertId;

                    $this->connection->executeStatement(
                        "UPDATE tl_user_group SET $accessField=? WHERE id=?",
                        [serialize($ids), $group['id']],
                    );
                }
            }
        }

        // Add the permissions on user level
        if ('group' !== $user->inherit) {
            $permissions = StringUtil::deserialize($user->$permissionField);

            if (\is_array($permissions) && \in_array('create', $permissions, true)) {
                $ids = (array) $user->$accessField;
                $ids[] = $insertId;

                $this->connection->executeStatement(
                    "UPDATE tl_user SET $accessField=? WHERE id=?",
                    [serialize($ids), $user->id],
                );
            }
        }

        // Add the new element to the user object
        $root[] = $insertId;
        $user->$accessField = $root;
    }
}
