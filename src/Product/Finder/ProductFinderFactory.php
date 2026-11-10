<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Finder;

use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\Product\Query\Filter\FilterInterface;
use Isotope\CoreBundle\Product\Query\Sorting;

class ProductFinderFactory
{
    public function __construct(
        private readonly Connection $connection,
        private readonly TokenChecker $tokenChecker,
    ) {
    }

    /**
     * @param list<int>              $categories
     * @param array<FilterInterface> $baseFilters
     * @param array<Sorting>         $baseSortings
     */
    public function create(array $categories, string $locale, array $baseFilters = [], array $baseSortings = []): ProductFinder
    {
        $qb = $this->connection->createQueryBuilder();
        $localeParameter = $qb->createNamedParameter($locale);
        $qb
            ->select('IFNULL(parent.id, tl_iso_product.id)')
            ->from('tl_iso_product')
            ->where($qb->expr()->in('c.pid', $qb->createNamedParameter($categories, ArrayParameterType::INTEGER)))
            ->andWhere("tl_iso_product.language=''")
            ->leftJoin('tl_iso_product', 'tl_iso_product', 'translation', "tl_iso_product.id=translation.pid AND translation.language=$localeParameter")
            ->leftJoin('tl_iso_product', 'tl_iso_product', 'parent', 'tl_iso_product.pid=parent.id')
            ->leftJoin('parent', 'tl_iso_product', 'parent_translation', "parent.id=parent_translation.pid AND parent_translation.language=$localeParameter")
            ->leftJoin('tl_iso_product', 'tl_iso_product_category', 'c', 'IFNULL(parent.id, tl_iso_product.id)=c.product_id')
            ->groupBy('IFNULL(parent.id, tl_iso_product.id)')
        ;

        if (!$this->tokenChecker->isPreviewMode()) {
            $qb
                ->setParameter('time', time())
                ->andWhere('tl_iso_product.published=1')
                ->andWhere("tl_iso_product.start='' OR tl_iso_product.start < :time")
                ->andWhere("tl_iso_product.stop='' OR tl_iso_product.stop > :time")
                ->andWhere("tl_iso_product.pid=0 OR (parent.published=1 AND (parent.start='' OR parent.start < :time) AND (parent.stop='' OR parent.stop > :time))")
            ;
        }

        return new ProductFinder($this->connection, $qb, $baseFilters, $baseSortings);
    }
}
