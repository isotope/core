<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Module;

use Contao\StringUtil;
use Contao\System;
use Haste\Util\Url;
use Isotope\Frontend\ProductCollectionAction\ContinueShoppingAction;
use Isotope\Frontend\ProductCollectionAction\GoToCartAction;
use Isotope\Frontend\ProductCollectionAction\GoToCheckoutAction;
use Isotope\Frontend\ProductCollectionAction\UpdateCartAction;
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

        if (isset($GLOBALS['ISO_HOOKS']['compileCart']) && \is_array($GLOBALS['ISO_HOOKS']['compileCart'])) {
            foreach ($GLOBALS['ISO_HOOKS']['compileCart'] as $callback) {
                $strCustom .= System::importStatic($callback[0])->{$callback[1]}($this);
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

        if (isset($data['configuration']) && !$item->hasErrors()) {
            [$baseUrl,] = explode('?', $data['href'], 2);
            $data['edit_href']  = Url::addQueryString('collection_item=' . $item->id, $baseUrl);
            $data['edit_title'] = StringUtil::specialchars(sprintf($GLOBALS['TL_LANG']['MSC']['editProductLinkTitle'], $data['name']));
            $data['edit_link']  = $GLOBALS['TL_LANG']['MSC']['editProductLinkText'];
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    protected function getActions()
    {
        return [
            new UpdateCartAction(),
            new GoToCartAction($this),
            new GoToCheckoutAction($this),
            new ContinueShoppingAction($this),
        ];
    }
}
