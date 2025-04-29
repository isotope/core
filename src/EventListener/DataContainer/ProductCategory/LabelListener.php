<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\ProductCategory;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Isotope\CoreBundle\EventListener\DataContainer\Product\LabelListener as ProductLabelListener;
use Isotope\Model\Product;

#[AsCallback('tl_iso_product_category', 'list.sorting.child_record_callback')]
class LabelListener
{
    public function __construct(
        private readonly ProductLabelListener $productLabelListener,
    ) {
    }

    public function __invoke(array $row): string
    {
        $objProduct = Product::findById($row['pid']);

        return \sprintf(
            '<span style="display:block;float:left;width:50px;">%s</span><span style="display:block;float:left;margin: 0 0 0 10px;padding: 18px 0;">%s</span>',
            $this->productLabelListener->generateImage($objProduct),
            $objProduct->name,
        );
    }
}
