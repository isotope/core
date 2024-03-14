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
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Isotope;
use Isotope\Message;

class FavoriteAction extends AbstractButton
{

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'toggle_favorites';
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(IsotopeProduct $product = null)
    {
        $label = $this->isFavorited($product) ? 'remove_from_favorites' : 'add_to_favorites';

        return $GLOBALS['TL_LANG']['MSC']['buttonLabel'][$label];
    }

    /**
     * {@inheritdoc}
     */
    public function handleSubmit(IsotopeProduct $product, array $config = [])
    {
        if (!isset($_POST[$this->getName()])) {
            return false;
        }

        $favorites = Isotope::getFavorites();

        if (null === $favorites) {
            return false;
        }

        if ($favorites->hasProduct($product)) {
            $favorites->deleteItem($favorites->getItemForProduct($product));
            Message::addConfirmation($GLOBALS['TL_LANG']['MSC']['removedFromFavorites']);

        } elseif ($favorites->addProduct($product, 1, $config) !== false) {
            Message::addConfirmation($GLOBALS['TL_LANG']['MSC']['addedToFavorites']);
        }

        Controller::reload();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getClasses(IsotopeProduct $product)
    {
        return $this->isFavorited($product) ? 'active' : '';
    }

    /**
     * @return bool
     */
    private function isFavorited(IsotopeProduct $product = null)
    {
        if (null === $product) {
            return false;
        }

        $favorites = Isotope::getFavorites();

        return $favorites !== null && $favorites->hasProduct($product);
    }
}
