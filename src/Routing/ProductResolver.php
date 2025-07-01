<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Routing;

use Contao\CoreBundle\Routing\Content\ContentUrlResolverInterface;
use Contao\CoreBundle\Routing\Content\ContentUrlResult;
use Contao\CoreBundle\Routing\PageFinder;
use Contao\Database;
use Contao\PageModel;
use Isotope\Model\Product;
use Symfony\Component\HttpFoundation\RequestStack;

class ProductResolver implements ContentUrlResolverInterface
{
    public function __construct(
        private readonly PageFinder $pageFinder,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function resolve(object $content): ContentUrlResult|null
    {
        if (!$content instanceof Product) {
            return null;
        }

        $rootPage = $this->pageFinder->findRootPageForRequest($this->requestStack->getCurrentRequest());

        if (!$rootPage) {
            return null;
        }

        $categories = array_intersect(
            $content->getCategories(),
            Database::getInstance()->getChildRecords([$rootPage->id], 'tl_page'),
        );

        if ([] === $categories) {
            return null;
        }

        return ContentUrlResult::resolve(PageModel::findById(reset($categories)));
    }

    public function getParametersForContent(object $content, PageModel $pageModel): array
    {
        if (!$content instanceof Product) {
            return [];
        }

        if ($content->isVariant()) {
            $content = Product::findById($content->getProductId());
        }

        return [
            'parameters' => '/'.($content->alias ?: $content->getProductId()),
        ];
    }
}
