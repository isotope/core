<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model;

/**
 * ProductCollectionLog represents a log in a product collection.
 *
 * @property int    $id
 * @property int    $pid
 * @property int    $tstamp
 * @property int    $author
 * @property int    $order_status
 * @property int    $date_paid
 * @property int    $date_shipped
 * @property string $notes
 * @property int    $notification
*/
class ProductCollectionLog extends \Model
{
    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_product_collection_log';
}
