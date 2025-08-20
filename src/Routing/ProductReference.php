<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Routing;

use Contao\PageModel;
use Isotope\CoreBundle\Product\ProductInterface;

class ProductReference
{
    public ProductInterface $product;
    public bool $linkToPrimary = false;
    public PageModel|int|null $referencePage = null;

    public function __construct(ProductInterface $product, PageModel|int|null $referencePage = null, bool $linkToPrimary = false)
    {
        $this->product = $product;
        $this->linkToPrimary = $linkToPrimary;
        $this->referencePage = $referencePage;
    }
}
