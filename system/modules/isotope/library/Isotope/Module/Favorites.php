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
use Isotope\Model\ProductCollection;
use Isotope\Model\ProductCollectionItem;
use Isotope\Template;

/**
 * @property int    $iso_cart_jumpTo
 * @property int    $iso_gallery
 * @property string $iso_collectionTpl
 * @property string $iso_orderCollectionBy
 */
class Favorites extends Module
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_iso_favorites';

    /**
     * Disable caching of the frontend page if this module is in use
     * @var boolean
     */
    protected $blnDisableCache = true;


    /**
     * Display a wildcard in the back end
     *
     * @return string
     */
    public function generate()
    {
        if ('BE' === TL_MODE) {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ISOTOPE ECOMMERCE: FAVORITES ###';

            $objTemplate->title = $this->headline;
            $objTemplate->id    = $this->id;
            $objTemplate->link  = $this->name;
            $objTemplate->href  = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        if (true !== FE_USER_LOGGED_IN) {
            return '';
        }

        return parent::generate();
    }

    /**
     * Generate the module
     */
    protected function compile()
    {
        $collection = Isotope::getFavorites();

        if ($collection->isEmpty()) {
            $this->Template->empty   = true;
            $this->Template->type    = 'empty';
            $this->Template->message = $this->iso_emptyMessage ? $this->iso_noProducts : $GLOBALS['TL_LANG']['MSC']['noItemsInFavorites'];

            return;
        }

        $addToCart = (string) \Input::get('add_to_cart');

        if ('all' === $addToCart) {
            Isotope::getCart()->copyItemsFrom($collection);
            \Controller::redirect(Url::removeQueryString(['add_to_cart']));
        }

        /** @var Template|\stdClass $collectionTemplate */
        $collectionTemplate = new Template($this->iso_collectionTpl);
        $collectionTemplate->linkProducts = true;

        $collection->addToTemplate(
            $collectionTemplate,
            array(
                'gallery' => $this->iso_gallery,
                'sorting' => ProductCollection::getItemsSortingCallable($this->iso_orderCollectionBy),
            )
        );

        $collectionTemplate->items = $this->updateTemplate(
            $collection,
            $collectionTemplate->items,
            (int) \Input::get('remove'),
            (int) $addToCart
        );

        $collectionTemplate->cart_all_href = \Haste\Util\Url::addQueryString('add_to_cart=all');

        $this->Template->collection = $collection;
        $this->Template->products   = $collectionTemplate->parse();
    }

    private function updateTemplate(IsotopeProductCollection $collection, array $data, $removeId, $addToCart)
    {
        foreach ($data as $k => &$row) {
            /** @var ProductCollectionItem $item */
            $item = $row['item'];
            $itemId = (int) $item->id;

            // Remove from collection
            if ($removeId > 0 && $removeId === $itemId) {
                $collection->deleteItemById($itemId);
                \Controller::redirect(Url::removeQueryString(['remove']));
            } elseif ($addToCart > 0 && $addToCart === $itemId) {
                if ($item->hasProduct()) {
                    Isotope::getCart()->addProduct($item->getProduct(), 1, ['jumpTo' => $item->getRelated('jumpTo')]);
                    \Controller::redirect(Url::removeQueryString(['add_to_cart']));
                }
            }

            $row['remove_href']  = Url::addQueryString('remove=' . $itemId);
            $row['remove_title'] = specialchars(sprintf($GLOBALS['TL_LANG']['MSC']['removeProductLinkTitle'], $row['name']));
            $row['remove_link']  = $GLOBALS['TL_LANG']['MSC']['removeProductLinkText'];
            $row['cart_href']    = Url::addQueryString('add_to_cart=' . $itemId);
        }

        return $data;
    }
}
