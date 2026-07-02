<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Routing;

use Contao\PageModel;
use Isotope\CoreBundle\Product\CoreProduct;

class ProductReference
{
    /**
     * @internal
     */
    public bool $resolveVariant = true;

    public function __construct(
        public CoreProduct $product,
        public PageModel|int|null $referencePage = null,
        public bool $linkToPrimary = false,
    ) {
    }
}
