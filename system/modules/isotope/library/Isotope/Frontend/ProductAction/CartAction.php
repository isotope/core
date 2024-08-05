<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Frontend\ProductAction;

use Contao\Controller;
use Contao\Environment;
use Contao\Input;
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
                Controller::reload();
            }

            Controller::redirect(
                Url::addQueryString(
                    'continue=' . base64_encode(Environment::get('request')),
                    $config['module']->iso_addProductJumpTo
                )
            );
        }

        return $success;
    }

    /**
     * @param IsotopeProduct|null $product
     *
     * @return ProductCollectionItem|null
     */
    private function getCurrentCartItem(IsotopeProduct $product = null)
    {
        if (null === $product || !Input::get('collection_item')) {
            return null;
        }

        /** @var ProductCollectionItem $item */
        $item = Isotope::getCart()->getItemById(Input::get('collection_item'));

        if (null !== $item
            && $item->hasProduct()
            && $item->getProduct()->getProductId() == $product->getProductId()
        ) {
            return $item;
        }

        return null;
    }

    /**
     *
     * @return bool
     */
    private function handleAddToCart(IsotopeProduct $product, array $config = [])
    {
        $module   = $config['module'];
        $quantity = 1;

        if ($module->iso_use_quantity && Input::post('quantity_requested') > 0) {
            $quantity = (int) Input::post('quantity_requested');
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
     *
     * @return bool
     */
    private function handleUpdateCart(ProductCollectionItem $item, IsotopeProduct $product)
    {
        $success = Isotope::getCart()->updateProduct($product, $item);

        if ($success) {
            Message::addConfirmation($GLOBALS['TL_LANG']['MSC']['updatedInCart']);
        }

        return $success;
    }
}
