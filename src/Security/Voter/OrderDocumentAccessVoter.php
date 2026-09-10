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
class OrderDocumentAccessVoter extends AbstractDataContainerVoter
{
    public function __construct(
        private readonly Connection $connection,
        private readonly AccessDecisionManagerInterface $accessDecisionManager,
    ) {
    }

    protected function getTable(): string
    {
        return 'tl_iso_order_document';
    }

    protected function hasAccess(TokenInterface $token, CreateAction|DeleteAction|ReadAction|UpdateAction $action): bool
    {
        return match (true) {
            $action instanceof CreateAction,
            $action instanceof UpdateAction => false,
            $action instanceof ReadAction,
            $action instanceof DeleteAction => $this->canAccessProductCollection($token, $action->getCurrentPid()),
        };
    }

    private function canAccessProductCollection(TokenInterface $token, string|null $id): bool
    {
        if (!$id) {
            return true;
        }

        $current = $this->connection->fetchAssociative('SELECT * FROM tl_iso_product_collection WHERE id=?', [$id]);

        return $this->accessDecisionManager->decide(
            $token,
            [ContaoCorePermissions::DC_PREFIX.'tl_iso_product_collection'],
            new ReadAction('tl_iso_product_collection', $current),
        );
    }
}
