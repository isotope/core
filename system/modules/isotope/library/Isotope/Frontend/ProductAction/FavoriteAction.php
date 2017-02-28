<?php

namespace Isotope\Frontend\ProductAction;

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
    public function isAvailable(IsotopeProduct $product, array $config = [])
    {
        return true === FE_USER_LOGGED_IN;
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

        \Controller::reload();

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
     * @param IsotopeProduct $product
     *
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
