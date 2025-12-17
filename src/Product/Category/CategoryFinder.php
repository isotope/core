<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Category;

use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Database;
use Contao\PageModel;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Util\ProductCategoryUtil;
use Symfony\Contracts\Service\ResetInterface;

class CategoryFinder implements ResetInterface
{
    /** @var array<string, array<int>> */
    private array $cache = [];

    public function __construct(
        private readonly Connection $connection,
        private readonly TokenChecker $tokenChecker,
        private readonly ProductCategoryUtil $categoryUtil,
    ) {
    }

    public function reset()
    {
        $this->cache = [];
    }

    /**
     * @return array<int>
     */
    public function getIds(CategoryScope|string $scope, PageModel $pageModel, CoreProduct|null $product): array
    {
        $cacheKey = $scope instanceof CategoryScope ? $scope->value : $scope;
        $cacheKey .= '_'.$pageModel->id.'_'.($product?->getProductId() ?? 0);

        if (!isset($this->cache[$cacheKey])) {
            $this->cache[$cacheKey] = $this->doFind($scope, $pageModel, $product);
        }

        return $this->cache[$cacheKey];
    }

    /**
     * @return array<int>|null
     */
    private function doFind(CategoryScope|string $scope, PageModel $pageModel, CoreProduct|null $product): array
    {
        $where = "type != 'error_401' AND type != 'error_403' AND type != 'error_404' AND type != 'error_503'";

        if (!$this->tokenChecker->isPreviewMode()) {
            $now = time();
            $where .= " AND published=1 AND (start='' OR start<$now) AND (stop='' OR stop>$now)";
        }

        switch ($scope) {
            case CategoryScope::Current:
                return [$pageModel->id];

            case CategoryScope::CurrentAndFirstChild:
                return $this->connection->fetchFirstColumn(
                    'SELECT id FROM tl_page WHERE id=:id OR pid=:id AND '.$where,
                    ['id' => $pageModel->id],
                );

            case CategoryScope::CurrentAndChildren:
                return Database::getInstance()->getChildRecords([$pageModel->id], 'tl_page', false, [$pageModel->id], $where);

            case CategoryScope::Parent:
                return [$pageModel->pid];

            case CategoryScope::Product:
                return $product ? $this->categoryUtil->getPublishedForProduct($product) : [];

            case CategoryScope::All:
                $pageModel->loadDetails();
                return Database::getInstance()->getChildRecords([$pageModel->rootId], 'tl_page', false, [$pageModel->rootId], $where);
        }

        //if (isset($GLOBALS['ISO_HOOKS']['findCategories']) && \is_array($GLOBALS['ISO_HOOKS']['findCategories'])) {
        //    foreach ($GLOBALS['ISO_HOOKS']['findCategories'] as $callback) {
        //        $arrCategories = System::importStatic($callback[0])->{$callback[1]}($this);
        //
        //        if ($arrCategories !== false) {
        //            break;
        //        }
        //    }
        //}

        throw new \RuntimeException('Unable to determine categories for scope "'.$scope.'"');
    }
}
