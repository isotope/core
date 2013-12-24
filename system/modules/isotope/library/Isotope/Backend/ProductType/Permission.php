<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2013 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\ProductType;

use Isotope\Model\Product;
use Isotope\Model\ProductCollection;
use Isotope\Model\ProductCollectionItem;


class Permission extends \Backend
{

    /**
     * Check permissions for that entry
     * @return void
     */
    public static function check()
    {
        $session = \Session::getInstance()->getData();

        if (\Input::get('act') == 'delete' && in_array(\Input::get('id'), static::getUndeletableIds())) {
            \System::log('Product type ID '.\Input::get('id').' is used in an order and can\'t be deleted', __METHOD__, TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');

        } elseif (\Input::get('act') == 'deleteAll' && is_array($session['CURRENT']['IDS'])) {
            $arrDeletable = array_diff($session['CURRENT']['IDS'], static::getUndeletableIds());

            if (count($arrDeletable) != count($session['CURRENT']['IDS'])) {
                $session['CURRENT']['IDS'] = array_values($arrDeletable);
                \Session::getInstance()->setData($session);

                \Message::addInfo($GLOBALS['TL_LANG']['MSC']['undeletableRecords']);
            }
        }
    }

    /**
     * Check if a product can be deleted by the current backend user
     * Deleting is prohibited if a product has been ordered
     * @return  bool
     */
    public static function getUndeletableIds()
    {
        static $arrProducts;

        if (null === $arrProducts) {
            $arrProducts = \Database::getInstance()->query("
                    SELECT p.type AS type FROM " . Product::getTable() . " p
                    INNER JOIN " . ProductCollectionItem::getTable() . " i ON i.product_id=p.id
                    INNER JOIN " . ProductCollection::getTable() . " c ON i.pid=c.id
                    WHERE p.type>0 AND c.type='order'
                UNION
                    SELECT p.type AS type FROM " . Product::getTable() . " p
                    INNER JOIN " . Product::getTable() . " p2 ON p2.pid=p.pid
                    INNER JOIN " . ProductCollectionItem::getTable() . " i ON i.product_id=p2.id
                    INNER JOIN " . ProductCollection::getTable() . " c ON i.pid=c.id
                    WHERE c.type='order'
            ")->fetchEach('type');
        }

        return $arrProducts;
    }
}
