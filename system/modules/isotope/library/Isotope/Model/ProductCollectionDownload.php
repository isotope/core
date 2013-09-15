<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Model;

use Isotope\Interfaces\IsotopeProductCollection;


/**
 * ProductCollectionDownload model represents a download in a collection (usually an order)
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class ProductCollectionDownload extends \Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_product_collection_download';

    /**
     * Find all downloads that belong to items of a given collection
     * @param   IsotopeProductCollection
     * @param   array
     * @return  \Collection|null
     */
    public static function findByCollection(IsotopeProductCollection $objCollection, array $arrOptions=array())
    {
        $arrOptions = array_merge(
			array(
				'column' => ("pid IN (SELECT id FROM tl_iso_product_collection_item WHERE pid=?)"),
				'value'  => $objCollection->id,
				'return' => 'Collection'
			),
			$arrOptions
		);

		return static::find($arrOptions);
    }

    /**
     * Create ProductCollectionDownload for all product downloads in the given collection
     * @param   IsotopeProductCollection
     * @return  array
     */
    public static function createForProductsInCollection(IsotopeProductCollection $objCollection)
    {
        $arrDownloads = array();
        $t = Download::getTable();
        $time = time();

        foreach ($objCollection->getItems() as $objItem) {
            if ($objItem->hasProduct()) {
                $objDownloads = Download::findBy(array("($t.pid=? OR $t.pid=?)", "$t.published='1'"), array($objItem->getProduct()->id, $objItem->getProduct()->pid));

                if (null !== $objDownloads) {
                    while ($objDownloads->next()) {

                        $objItemDownload = new ProductCollectionDownload();
                        $objItemDownload->pid = $objItem->id;
                        $objItemDownload->tstamp = $time;
                        $objItemDownload->download_id = $objDownloads->id;

                        if ($objDownloads->downloads_allowed > 0) {
                            $objItemDownload->downloads_remaining = ($objDownloads->downloads_allowed * $objItem->quantity);
                        }

                        $expires = $objDownloads->current()->getExpirationTimestamp();
                        if (null !== $expires) {
                            $objItemDownload->expires = $expires;
                        }

                        $arrDownloads[] = $objItemDownload;
                    }
                }
            }
        }

        return $arrDownloads;
    }
}
