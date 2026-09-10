<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Security\Voter;

use Contao\CoreBundle\Security\DataContainer\CreateAction;
use Contao\CoreBundle\Security\DataContainer\DeleteAction;
use Contao\CoreBundle\Security\DataContainer\ReadAction;
use Contao\CoreBundle\Security\DataContainer\UpdateAction;
use Contao\CoreBundle\Security\Voter\DataContainer\AbstractDataContainerVoter;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @internal
 */
class OrderstatusAccessVoter extends AbstractDataContainerVoter implements ResetInterface
{
    private array|null $used = null;

    public function __construct(private readonly Connection $connection)
    {
    }

    public function reset(): void
    {
        $this->used = null;
    }

    protected function getTable(): string
    {
        return 'tl_iso_orderstatus';
    }

    protected function hasAccess(TokenInterface $token, CreateAction|DeleteAction|ReadAction|UpdateAction $action): bool
    {
        return match (true) {
            $action instanceof CreateAction,
            $action instanceof ReadAction => true,
            $action instanceof UpdateAction => !isset($action->getNew()['alias']),
            $action instanceof DeleteAction => $this->canDelete($action->getCurrent()),
        };
    }

    private function canDelete(array $current): bool
    {
        $this->used ??= [
            'order' => $this->connection->fetchFirstColumn("SELECT DISTINCT order_status FROM tl_iso_product_collection WHERE type='order'"),
            'transaction' => $this->connection->fetchFirstColumn("SELECT DISTINCT transaction_status FROM tl_iso_product_collection WHERE type='order'"),
            'delivery' => $this->connection->fetchFirstColumn("SELECT DISTINCT delivery_status FROM tl_iso_product_collection WHERE type='order'"),
        ];

        return !\in_array($current['alias'], $this->used[$current['type']], true);
    }
}
