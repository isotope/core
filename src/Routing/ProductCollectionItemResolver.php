<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Routing;

use Contao\CoreBundle\Routing\Content\ContentUrlResolverInterface;
use Contao\CoreBundle\Routing\Content\ContentUrlResult;
use Contao\PageModel;
use Isotope\CoreBundle\Model\ProductCollectionItem;

class ProductCollectionItemResolver implements ContentUrlResolverInterface
{
    public function resolve(object $content): ContentUrlResult|null
    {
        if (!$content instanceof ProductCollectionItem) {
            return null;
        }

        return ContentUrlResult::redirect($content->getProduct());
    }

    public function getParametersForContent(object $content, PageModel $pageModel): array
    {
        return [];
    }
}
