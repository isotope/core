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
class ProductSoldVoter extends AbstractDataContainerVoter implements ResetInterface
{
    private array|null $used = null;

    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function reset(): void
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
                    SELECT REPLACE(i.identifier, 'tl_iso_product.', '') AS id FROM tl_iso_product_collection_item i
                    INNER JOIN tl_iso_product_collection c ON i.pid=c.id
                    WHERE c.type='order'
                UNION
                    SELECT p.pid AS id FROM tl_iso_product p
                    INNER JOIN tl_iso_product_collection_item i ON i.identifier=CONCAT('tl_iso_product.', p.id)
                    INNER JOIN tl_iso_product_collection c ON i.pid=c.id
                    WHERE p.pid>0 AND c.type='order'
            ");
            $this->used = array_map(intval(...), $this->used);
        }

        return !\in_array((int) $action->getCurrentId(), $this->used, true);
    }
}
