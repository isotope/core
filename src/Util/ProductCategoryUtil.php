<?php

namespace Isotope\CoreBundle\Util;

use Contao\CoreBundle\Routing\PageFinder;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Database;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\Model\Product;
use Isotope\CoreBundle\Product\CoreProduct;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Service\ResetInterface;

#[Autoconfigure(public: true)]
class ProductCategoryUtil implements ResetInterface
{
    /**
     * @var array<int, array<int>>
     */
    private array|null $published = null;

    /**
     * @var array<int, array<int>>
     */
    private array|null $unpublished = null;

    /**
     * @var array<int, array<int>>
     */
    private array $root = [];

    public function __construct(
        private readonly Connection $connection,
        private readonly TokenChecker $tokenChecker,
        private readonly RequestStack $requestStack,
        private readonly PageFinder $pageFinder,
    ) {
    }

    /**
     * @return array<int>
     */
    public function getForProduct(Product $product): array
    {
        if (null === $this->unpublished) {
            $this->unpublished = $this->preload(false);
        }

        return $this->unpublished[$product->pid ?: $product->id] ?? [];
    }

    /**
     * @return array<int>
     */
    public function getPublishedForProduct(CoreProduct $product): array
    {
        if (null === $this->published) {
            $this->published = $this->preload(true);
        }

        return $this->published[$product->getProductId()] ?? [];
    }

    /**
     * @return array<int>
     */
    public function getPublishedForProductInRoot(int $rootId, CoreProduct $product): array
    {
        if (!isset($this->root[$rootId])) {
            $this->root[$rootId] = Database::getInstance()->getChildRecords($rootId, 'tl_page', false, [$rootId]);
        }

        return array_intersect($this->getPublishedForProduct($product), $this->root[$rootId]);
    }

    /**
     * @return array<int>
     */
    public function getPublishedForProductInCurrentRoot(CoreProduct $product): array
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            return [];
        }

        $rootPage = $this->pageFinder->findRootPageForRequest($request);

        if (!$rootPage) {
            return [];
        }

        return $this->getPublishedForProductInRoot((int) $rootPage->id, $product);
    }

    public function preload(bool $published): array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select('c.product_id', 'GROUP_CONCAT(c.pid ORDER BY c.product_sorting, c.id)')
            ->from('tl_iso_product_category', 'c')
            ->join('c', 'tl_page', 'p', 'c.pid=p.id')
            ->where("p.type NOT LIKE 'error_401' AND p.type NOT LIKE 'error_403' AND p.type != 'error_404' AND p.type != 'error_503'")
            ->groupBy('c.product_id')
        ;

        if ($published && !$this->tokenChecker->isPreviewMode()) {
            $qb
                ->andWhere("p.published > 0 AND (p.start='' OR p.start<:now) AND (p.stop='' OR p.stop>:now)")
                ->setParameter('now', time())
            ;
        }

        return array_map(
            static fn ($v) => array_map(intval(...), explode(',', $v)),
            $qb->fetchAllKeyValue(),
        );
    }

    public function reset(): void
    {
        $this->published = null;
        $this->unpublished = null;
    }
}
