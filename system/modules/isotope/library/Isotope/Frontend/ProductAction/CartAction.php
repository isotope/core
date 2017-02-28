<?php

namespace Isotope\Frontend\ProductAction;

use Haste\Util\Url;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Isotope;
use Isotope\Message;
use Isotope\Model\ProductCollectionItem;

class CartAction extends AbstractButton
{

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'add_to_cart';
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(IsotopeProduct $product = null)
    {
        if ($this->getCurrentCartItem($product)) {
            return $GLOBALS['TL_LANG']['MSC']['buttonLabel']['update_cart'];
        }

        return $GLOBALS['TL_LANG']['MSC']['buttonLabel']['add_to_cart'];
    }

    /**
     * {@inheritdoc}
     */
    public function handleSubmit(IsotopeProduct $product, array $config = [])
    {
        if (!isset($_POST[$this->getName()])) {
            return false;
        }

        $item = $this->getCurrentCartItem($product);

        if (null === $item) {
            $success = $this->handleAddToCart($product, $config);
        } else {
            $success = $this->handleUpdateCart($item, $product);
        }

        if ($success) {
            if (!$config['module']->iso_addProductJumpTo) {
                \Controller::reload();
            }

            \Controller::redirect(
                Url::addQueryString(
                    'continue=' . base64_encode(\Environment::get('request')),
                    $config['module']->iso_addProductJumpTo
                )
            );
        }
    }

    /**
     * @param IsotopeProduct|null $product
     *
     * @return ProductCollectionItem|null
     */
    private function getCurrentCartItem(IsotopeProduct $product = null)
    {
        if (null === $product || !\Input::get('collection_item')) {
            return null;
        }

        /** @var ProductCollectionItem $item */
        $item = ProductCollectionItem::findByPk(\Input::get('collection_item'));

        if ($item->pid == Isotope::getCart()->id
            && $item->hasProduct()
            && $item->getProduct()->getProductId() == $product->getProductId()
        ) {
            return $item;
        }

        return null;
    }

    /**
     * @param IsotopeProduct $product
     * @param array          $config
     *
     * @return bool
     */
    private function handleAddToCart(IsotopeProduct $product, array $config = [])
    {
        $module   = $config['module'];
        $quantity = 1;

        if ($module->iso_use_quantity && \Input::post('quantity_requested') > 0) {
            $quantity = (int) \Input::post('quantity_requested');
        }

        // Do not add parent of variant product to the cart
        if (($product->hasVariants() && !$product->isVariant())
            || !Isotope::getCart()->addProduct($product, $quantity, $config)
        ) {
            return false;
        }

        Message::addConfirmation($GLOBALS['TL_LANG']['MSC']['addedToCart']);

        return true;
    }

    /**
     * @param ProductCollectionItem $item
     * @param IsotopeProduct        $product
     *
     * @return bool
     */
    private function handleUpdateCart(ProductCollectionItem $item, IsotopeProduct $product)
    {
        return Isotope::getCart()->updateProduct($product, $item);
    }
}
