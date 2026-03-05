<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Security\Voter;

use Contao\CoreBundle\Security\DataContainer\CreateAction;
use Contao\CoreBundle\Security\DataContainer\DeleteAction;
use Contao\CoreBundle\Security\DataContainer\ReadAction;
use Contao\CoreBundle\Security\DataContainer\UpdateAction;
use Contao\CoreBundle\Security\Voter\DataContainer\AbstractDataContainerVoter;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\ProductCollection\Order\Order;
use Isotope\CoreBundle\Security\IsotopeCorePermissions;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Contracts\Service\ResetInterface;

class ProductCollectionAccessVoter extends AbstractDataContainerVoter implements ResetInterface
{
    /**
     * @var array<int, bool>
     */
    private array $memberAccessCache = [];

    public function __construct(
        private readonly AccessDecisionManagerInterface $accessDecisionManager,
        private readonly Connection $connection,
    ) {
    }

    public function reset(): void
    {
        $this->memberAccessCache = [];
    }

    protected function getTable(): string
    {
        return 'tl_iso_product_collection';
    }

    protected function hasAccess(TokenInterface $token, UpdateAction|CreateAction|ReadAction|DeleteAction $action): bool
    {
        if ([Order::getType()] !== $this->getProperty('type', $action)) {
            return false;
        }

        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        if ($action instanceof DeleteAction) {
            return false;
        }

        // Only check config permissions if the token does not have access to all configs
        if ($this->accessDecisionManager->decide($token, [IsotopeCorePermissions::USER_CAN_EDIT_CONFIG])) {
            foreach ($this->getProperty('config_id', $action) as $configId) {
                if (!$this->accessDecisionManager->decide($token, [IsotopeCorePermissions::USER_CAN_EDIT_CONFIG], $configId)) {
                    return false;
                }
            }
        }

        // Only check member permissions if the token does not have access to all groups
        if ($this->accessDecisionManager->decide($token, [IsotopeCorePermissions::USER_CAN_ACCESS_ORDERS_OF_MEMBER_GROUP])) {
            foreach ($this->getProperty('member', $action) as $memberId) {
                if (!$this->canAccessMember((int) $memberId, $token)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @return array{0: mixed, 1?: mixed}
     */
    private function getProperty(string $field, UpdateAction|CreateAction|ReadAction|DeleteAction $action): array
    {
        $values = match(true) {
            $action instanceof CreateAction => [$action->getNew()[$field] ?? null],
            $action instanceof UpdateAction => [$action->getCurrent()[$field] ?? null, $action->getNew()[$field] ?? null],
            $action instanceof ReadAction,
            $action instanceof DeleteAction => [$action->getCurrent()[$field] ?? null],
        };

        return array_unique(array_filter($values));
    }

    private function canAccessMember(int $memberId, TokenInterface $token): bool
    {
        if (isset($this->memberAccessCache[$memberId])) {
            return $this->memberAccessCache[$memberId];
        }

        if (0 === $memberId) {
            return $this->memberAccessCache[$memberId] = $this->accessDecisionManager->decide(
                $token,
                [IsotopeCorePermissions::USER_CAN_ACCESS_ORDERS_OF_MEMBER_GROUP],
                -1,
            );
        }

        $groups = $this->connection->fetchOne('SELECT `groups` FROM tl_member WHERE id=?', [$memberId]);
        $groups = StringUtil::deserialize($groups, true);

        foreach ($groups as $group) {
            if ($this->accessDecisionManager->decide($token, [IsotopeCorePermissions::USER_CAN_ACCESS_ORDERS_OF_MEMBER_GROUP], $group)) {
                return $this->memberAccessCache[$memberId] = true;
            }
        }

        return $this->memberAccessCache[$memberId] = false;
    }
}
