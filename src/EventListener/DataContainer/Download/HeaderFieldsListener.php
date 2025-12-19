<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Download;

use Codefog\HasteBundle\Formatter;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\DataCollector\ProductDto;
use Isotope\CoreBundle\Product\ProductBuilder;
use Isotope\CoreBundle\Store\StoreManager;

#[AsCallback('tl_iso_download', 'list.sorting.header')]
class HeaderFieldsListener
{
    public function __construct(
        private readonly ProductBuilder $productBuilder,
        private readonly StoreManager $storeManager,
        private readonly AttributeRegistry $attributeRegistry,
        private readonly Formatter $formatter,
    ) {
    }

    public function __invoke(array $arrFields, DataContainer $dc): array
    {
        $arrNew = [];
        $product = $this->productBuilder->buildOne(new ProductDto('tl_iso_product.'.$dc->id), $this->storeManager->getCurrentContext());

        if (!$product instanceof CoreProduct) {
            return $arrFields;
        }

        $arrAttributes = ['name', 'alias', 'sku'];

        if ($product->isVariant()) {
            $arrAttributes = array_merge(
                $arrAttributes,
                array_intersect(
                    array_merge($product->getProductType()->getAttributes(), $product->getProductType()
                        ->getVariantAttributes()),
                    $this->attributeRegistry->getVariantOptionFields(),
                ),
            );
        }

        foreach ($arrAttributes as $field) {
            $v = $product->$field;

            if ($v != '') {
                $arrNew[$this->formatter->dcaLabel('tl_iso_product', $field)] = $this->formatter->dcaValue('tl_iso_product', $field, $v);
            }
        }

        // Add fields that have potentially been added through the DCA, but do not allow to override the core fields
        return array_merge($arrNew, array_diff_key($arrFields, $arrNew));
    }
}
