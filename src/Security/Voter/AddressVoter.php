<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Security\Voter;

use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\CoreBundle\Security\DataContainer\CreateAction;
use Contao\CoreBundle\Security\DataContainer\DeleteAction;
use Contao\CoreBundle\Security\DataContainer\ReadAction;
use Contao\CoreBundle\Security\DataContainer\UpdateAction;
use Contao\CoreBundle\Security\Voter\DataContainer\AbstractDataContainerVoter;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

/**
 * @internal
 */
class AddressVoter extends AbstractDataContainerVoter
{
    public function __construct(
        private readonly Connection $connection,
        private readonly AccessDecisionManagerInterface $accessDecisionManager,
    ) {
    }

    protected function getTable(): string
    {
        return 'tl_iso_address';
    }

    protected function hasAccess(TokenInterface $token, CreateAction|DeleteAction|ReadAction|UpdateAction $action): bool
    {
        // Must not move address between ptables or records
        if (
            $action instanceof UpdateAction
            && (isset($action->getNew()['ptable']) || isset($action->getNew()['pid']))
        ) {
            return false;
        }

        $current = $action instanceof CreateAction ? $action->getNew() : $action->getCurrent();

        // Cannot create, update or delete addresses for product collection (for now).
        if (
            !$action instanceof ReadAction
            && 'tl_iso_product_collection' === ($current['ptable'] ?? null)
        ) {
            return false;
        }

        // Can read address of order when order can be read
        if ($action instanceof ReadAction && 'tl_iso_product_collection' === $current['ptable']) {
            $collection = $this->connection->fetchAssociative('SELECT * FROM tl_iso_product_collection WHERE id=?', [$current['pid']]);

            return $this->accessDecisionManager->decide(
                $token,
                [ContaoCorePermissions::DC_PREFIX.'tl_iso_product_collection'],
                new ReadAction('tl_iso_product_collection', $collection),
            );
        }

        // Address must belong to a member from here on

        $member = $this->connection->fetchAssociative('SELECT * FROM tl_member WHERE id=?', [$current['pid']]);

        return $this->accessDecisionManager->decide(
            $token,
            [ContaoCorePermissions::DC_PREFIX.'tl_member'],
            new ReadAction('tl_member', $member),
        );
    }
}
