<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Routing;

use Contao\PageModel;
use Isotope\Model\Product;

class ProductReference
{
    public Product $product;
    public bool $linkToPrimary = false;
    public PageModel|int|null $referencePage = null;

    public function __construct(Product $product, PageModel|int|null $referencePage = null, bool $linkToPrimary = false)
    {
        $this->product = $product;
        $this->linkToPrimary = $linkToPrimary;
        $this->referencePage = $referencePage;
    }
}
