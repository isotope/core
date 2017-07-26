<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Module;

use Haste\Util\Url;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Isotope\Model\ProductCollectionItem;

/**
 * @property bool   $iso_continueShopping
 * @property int    $iso_cart_jumpTo
 * @property int    $iso_checkout_jumpTo
 * @property int    $iso_gallery
 * @property string $iso_collectionTpl
 * @property string $iso_orderCollectionBy
 */
class Cart extends AbstractProductCollection
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_iso_cart';

    /**
     * Override parent variable for BC reasons
     * @var string
     */
    protected $strFormId = 'iso_cart_update_';

    /**
     * @inheritdoc
     */
    protected function compile()
    {
        parent::compile();

        $strCustom = '';

        if (isset($GLOBALS['ISO_HOOKS']['compileCart']) && is_array($GLOBALS['ISO_HOOKS']['compileCart'])) {
            foreach ($GLOBALS['ISO_HOOKS']['compileCart'] as $callback) {
                $strCustom .= \System::importStatic($callback[0])->{$callback[1]}($this);
            }
        }

        $this->Template->custom = $strCustom;
    }

    /**
     * @return IsotopeProductCollection
     */
    protected function getCollection()
    {
        return Isotope::getCart();
    }

    /**
     * @return string
     */
    protected function getEmptyMessage()
    {
        return $GLOBALS['TL_LANG']['MSC']['noItemsInCart'];
    }

    /**
     * @return bool
     */
    protected function canEditQuantity()
    {
        return true;
    }

    /**
     * @return bool
     */
    protected function canRemoveProducts()
    {
        return true;
    }

    /**
     * @param IsotopeProductCollection $collection
     * @param array                    $data
     * @param array                    $quantity
     * @param bool                     $hasChanges
     *
     * @return array
     */
    protected function updateItemTemplate(
        IsotopeProductCollection $collection,
        ProductCollectionItem $item,
        array $data,
        array $quantity,
        &$hasChanges
    ) {
        $data = parent::updateItemTemplate($collection, $item, $data, $quantity, $hasChanges);

        if (isset($data['configuration'])) {
            list($baseUrl,) = explode('?', $data['href'], 2);
            $data['edit_href']  = Url::addQueryString('collection_item=' . $item->id, $baseUrl);
            $data['edit_title'] = specialchars(sprintf($GLOBALS['TL_LANG']['MSC']['editProductLinkTitle'], $data['name']));
            $data['edit_link']  = $GLOBALS['TL_LANG']['MSC']['editProductLinkText'];
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    protected function generateButtons(array $buttons = [])
    {
        // Add "update cart" button
        $this->addButton($buttons, 'update', $GLOBALS['TL_LANG']['MSC']['updateCartBT']);

        // Add button to cart button (usually if not on the cart page)
        if ($this->iso_cart_jumpTo > 0 && ($jumpToCart = \PageModel::findByPk($this->iso_cart_jumpTo)) !== null) {
            $this->addButton(
                $buttons,
                'cart',
                $GLOBALS['TL_LANG']['MSC']['cartBT'],
                $jumpToCart->getFrontendUrl()
            );
        }

        // Add button to checkout page
        if ($this->iso_checkout_jumpTo > 0
            && !Isotope::getCart()->hasErrors()
            && ($jumpToCheckout = \PageModel::findByPk($this->iso_checkout_jumpTo)) !== null
        ) {
            $this->addButton(
                $buttons,
                'checkout',
                $GLOBALS['TL_LANG']['MSC']['checkoutBT'],
                $jumpToCheckout->getFrontendUrl()
            );
        }

        if ($this->iso_continueShopping && \Input::get('continue') != '') {
            $this->addButton(
                $buttons,
                'continue',
                $GLOBALS['TL_LANG']['MSC']['continueShoppingBT'],
                ampersand(base64_decode(\Input::get('continue', true)))
            );
        }

        return $buttons;
    }
}
