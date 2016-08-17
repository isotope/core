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
 * @property bool $iso_use_quantity
 */
class Favorites extends AbstractProductCollection
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_iso_favorites';

    /**
     * @inheritdoc
     */
    public function generate()
    {
        if ('FE' === TL_MODE && true !== FE_USER_LOGGED_IN) {
            return '';
        }

        return parent::generate();
    }

    /**
     * @inheritdoc
     */
    protected function getCollection()
    {
        return Isotope::getFavorites();
    }

    /**
     * @inheritdoc
     */
    protected function getEmptyMessage()
    {
        return $GLOBALS['TL_LANG']['MSC']['noItemsInFavorites'];
    }

    /**
     * @inheritdoc
     */
    protected function canEditQuantity()
    {
        return (bool) $this->iso_use_quantity;
    }

    /**
     * @inheritdoc
     */
    protected function canRemoveProducts()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    protected function updateItemTemplate(
        IsotopeProductCollection $collection,
        ProductCollectionItem $item,
        array $data,
        array $quantity,
        &$hasChanges
    ) {
        $data = parent::updateItemTemplate($collection, $item, $data, $quantity, $hasChanges);

        $data['cart_href'] = Url::addQueryString('add_to_cart=' . $item->id);

        if ((int) \Input::get('add_to_cart') === $item->id && $item->hasProduct()) {
            Isotope::getCart()->addProduct(
                $item->getProduct(),
                $item->quantity,
                ['jumpTo' => $item->getRelated('jumpTo')]
            );

            \Controller::redirect(Url::removeQueryString(['add_to_cart']));
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    protected function generateButtons(array $buttons = [])
    {
        if ($this->canEditQuantity()) {
            $this->addButton($buttons, 'save', $GLOBALS['TL_LANG']['MSC']['save']);
        }

        $this->addButton(
            $buttons,
            'add_to_cart',
            $GLOBALS['TL_LANG']['MSC']['buttonLabel']['add_all_to_cart'],
            function () {
                Isotope::getCart()->copyItemsFrom($this->getCollection());
            }
        );

        return $buttons;
    }
}
