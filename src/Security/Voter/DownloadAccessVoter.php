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
class DownloadAccessVoter extends AbstractDataContainerVoter implements ResetInterface
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
        return 'tl_iso_download';
    }

    protected function hasAccess(TokenInterface $token, CreateAction|DeleteAction|ReadAction|UpdateAction $action): bool
    {
        return match (true) {
            $action instanceof CreateAction,
            $action instanceof ReadAction,
            $action instanceof UpdateAction => true,
            $action instanceof DeleteAction => $this->canDelete((int) $action->getCurrentId()),
        };
    }

    private function canDelete(int $currentId): bool
    {
        if (null === $this->used) {
            $this->used = array_map('intval', $this->connection->fetchFirstColumn('SELECT DISTINCT download_id FROM tl_iso_product_collection_download'));
        }

        return !\in_array($currentId, $this->used, true);
    }
}
