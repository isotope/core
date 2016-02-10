<?php

namespace Isotope;

use Contao\Folder;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\Attribute;
use Isotope\Model\Config;
use Isotope\Model\Product\Standard as StandardProduct;
use Isotope\Model\ProductCollection\Order;
use Isotope\Model\ProductCollectionItem;

class Uploads
{

    /**
     * @param IsotopeProductCollection|Order $order
     */
    public function onPostCheckout(IsotopeProductCollection $order)
    {
        /** @var Config $config */
        $config = $order->getRelated('config');

        if (!$config->order_moveUploads && null !== $config->getRelated('order_uploadTarget')) {
            return;
        }

        $items    = $order->getItems();
        $total    = count($items);
        $position = 0;

        foreach ($items as $item) {
            $product = $item->getProduct();
            ++$position;

            if (!$item->hasProduct() || !$product instanceof StandardProduct) {
                continue;
            }

            $itemFolder = $this->getItemFolder($item, $position, $total);

            foreach ($product->getCustomerConfig() as $attributeName => $value) {
                /** @var Attribute $attribute */
                $attribute = $GLOBALS['TL_DCA']['tl_iso_product']['attributes'][$attributeName];

                if (!$attribute instanceof \uploadable) {
                    continue;
                }

                $this->handleUpload(
                    $value,
                    sprintf(
                        '%s/%s/%s',
                        $this->getOrderFolder($order),
                        $this->getItemFolder($item, $position, $total),
                        $attribute->name
                    )
                );
            }
        }
    }

    private function handleUpload($source, $targetPath)
    {
        $target = new Folder($targetPath);

    }

    /**
     * @param ProductCollectionItem $item
     * @param int                   $position
     * @param int                   $total
     *
     * @return string
     */
    private function getItemFolder(ProductCollectionItem $item, $position, $total)
    {
        $blocks = array(
            str_pad($position, max(3, strlen((string) $total)), '0', STR_PAD_LEFT),
            $item->sku,
            $item->name
        );

        return standardize(implode('__', array_filter($blocks)));
    }

    private function getOrderFolder(Order $order)
    {
        /** @var Config $config */
        $config  = $order->getRelated('config');
        $orderId = standardize($order->document_number ?: $order->id);

        return $config->getRelated('order_uploadTarget')->path . '/' . $orderId;
    }
}
