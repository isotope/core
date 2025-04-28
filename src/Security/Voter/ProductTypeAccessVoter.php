<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Security\Voter;

use Contao\CoreBundle\Security\DataContainer\CreateAction;
use Contao\CoreBundle\Security\DataContainer\DeleteAction;
use Contao\CoreBundle\Security\DataContainer\ReadAction;
use Contao\CoreBundle\Security\DataContainer\UpdateAction;
use Contao\CoreBundle\Security\Voter\DataContainer\AbstractDataContainerVoter;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\Security\IsotopeCorePermissions;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @internal
 */
class ProductTypeAccessVoter extends AbstractDataContainerVoter implements ResetInterface
{
    private array|null $used = null;

    public function __construct(
        private readonly AccessDecisionManagerInterface $accessDecisionManager,
        private readonly Connection $connection,
    ) {
    }

    public function reset(): void
    {
        $this->used = null;
    }

    protected function getTable(): string
    {
        return 'tl_iso_producttype';
    }

    protected function hasAccess(TokenInterface $token, CreateAction|DeleteAction|ReadAction|UpdateAction $action): bool
    {
        if (!$this->accessDecisionManager->decide($token, [IsotopeCorePermissions::USER_CAN_ACCESS_PRODUCT_TYPES])) {
            return false;
        }

        return match (true) {
            $action instanceof CreateAction => $this->accessDecisionManager->decide($token, [IsotopeCorePermissions::USER_CAN_CREATE_PRODUCT_TYPES]),
            $action instanceof ReadAction,
            $action instanceof UpdateAction => $this->accessDecisionManager->decide($token, [IsotopeCorePermissions::USER_CAN_EDIT_PRODUCT_TYPE], $action->getCurrentId()),
            $action instanceof DeleteAction => $this->canDelete($token, (int) $action->getCurrentId()),
        };
    }

    private function canDelete(TokenInterface $token, int $currentId): bool
    {
        if (null === $this->used) {
            $this->used = array_map(intval(...), $this->connection->fetchFirstColumn('SELECT DISTINCT type FROM tl_iso_product'));
        }

        return !\in_array($currentId, $this->used, true)
            && $this->accessDecisionManager->decide($token, [IsotopeCorePermissions::USER_CAN_EDIT_PRODUCT_TYPE], $currentId)
            && $this->accessDecisionManager->decide($token, [IsotopeCorePermissions::USER_CAN_DELETE_PRODUCT_TYPES]);
    }
}
