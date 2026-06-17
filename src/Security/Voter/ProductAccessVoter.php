<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Security\Voter;

use Contao\BackendUser;
use Contao\CoreBundle\Security\DataContainer\CreateAction;
use Contao\CoreBundle\Security\DataContainer\DeleteAction;
use Contao\CoreBundle\Security\DataContainer\ReadAction;
use Contao\CoreBundle\Security\DataContainer\UpdateAction;
use Contao\CoreBundle\Security\Voter\DataContainer\AbstractDataContainerVoter;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Contracts\Service\ResetInterface;

class ProductAccessVoter extends AbstractDataContainerVoter implements ResetInterface
{
    private array $cache = [];

    public function __construct(private readonly Connection $connection)
    {
    }

    public function reset(): void
    {
        $this->cache = [];
    }

    protected function getTable(): string
    {
        return 'tl_iso_product';
    }

    protected function hasAccess(TokenInterface $token, CreateAction|DeleteAction|ReadAction|UpdateAction $action): bool
    {
        $user = $token->getUser();

        if (!$user instanceof BackendUser || $user->isAdmin) {
            return true;
        }

        if ([] === $user->iso_producttypes) {
            return false;
        }

        $cacheKey = $this->getCacheKey($user->iso_producttypes);

        $allowedIds = $this->cache[$cacheKey] ?? $this->getAllowedIds($user->iso_producttypes, $cacheKey);

        return match (true) {
            $action instanceof ReadAction,
            $action instanceof DeleteAction => \in_array((int) $action->getCurrentId(), $allowedIds, true),
            $action instanceof CreateAction => \in_array((int) $action->getNewId(), $allowedIds, true),
            $action instanceof UpdateAction => \in_array((int) $action->getCurrentId(), $allowedIds, true)
                && (null === $action->getNewId() || \in_array((int) $action->getNewId(), $allowedIds, true)),
        };
    }

    private function getCacheKey(array $productTypes): string
    {
        sort($productTypes);

        return implode(',', $productTypes);
    }

    private function getAllowedIds(array $productTypes, string $cacheKey): array
    {
        $productIds = $this->connection->fetchFirstColumn(
            <<<'SQL'
                SELECT id
                FROM tl_iso_product
                WHERE pid=0 AND language='' AND type=0 OR type IN (?)

                UNION

                SELECT id
                FROM tl_iso_product
                WHERE pid IN (
                    SELECT id
                    FROM tl_iso_product
                    WHERE pid=0 AND language='' AND type=0 OR type IN (?)
                ) AND language=''
                SQL,
            [$productTypes, $productTypes],
            [ArrayParameterType::INTEGER, ArrayParameterType::INTEGER],
        );

        return $this->cache[$cacheKey] = array_map(intval(...), $productIds);
    }
}
