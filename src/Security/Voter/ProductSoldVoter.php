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
class ProductSoldVoter extends AbstractDataContainerVoter implements ResetInterface
{
    private array|null $used = null;

    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function reset()
    {
        $this->used = null;
    }

    protected function getTable(): string
    {
        return 'tl_iso_product';
    }

    protected function hasAccess(TokenInterface $token, CreateAction|DeleteAction|ReadAction|UpdateAction $action): bool
    {
        if (!$action instanceof DeleteAction) {
            return true;
        }

        if (null === $this->used) {
            $this->used = $this->connection->fetchFirstColumn("
                    SELECT i.product_id AS id FROM tl_iso_product_collection_item i
                    INNER JOIN tl_iso_product_collection c ON i.pid=c.id
                    WHERE c.type='order'
                UNION
                    SELECT p.pid AS id FROM tl_iso_product p
                    INNER JOIN tl_iso_product_collection_item i ON i.product_id=p.id
                    INNER JOIN tl_iso_product_collection c ON i.pid=c.id
                    WHERE p.pid>0 AND c.type='order'
            ");
        }

        return !\in_array($action->getCurrentId(), $this->used, false);
    }
}
