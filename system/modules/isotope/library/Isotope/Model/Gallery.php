<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model;

use Isotope\Interfaces\IsotopeProduct;

/**
 * Gallery is the parent class for all gallery types
 *
 *
 * @property int    id
 * @property int    tstamp
 * @property string name
 * @property string type
 * @property string anchor
 * @property string placeholder
 * @property string main_size
 * @property string main_watermark_image
 * @property string main_watermark_position
 * @property string gallery_size
 * @property string gallery_watermark_image
 * @property string gallery_watermark_position
 * @property string customTpl
 */
abstract class Gallery extends TypeAgent
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_iso_gallery';

    /**
     * Interface to validate shipping method
     * @var string
     */
    protected static $strInterface = '\Isotope\Interfaces\IsotopeGallery';

    /**
     * List of types (classes) for this model
     * @var array
     */
    protected static $arrModelTypes = array();

    /**
     * Create a gallery for product, falls back to standard gallery if none is defined
     *
     * @param IsotopeProduct $objProduct
     * @param string         $strAttribute
     * @param array          $arrConfig
     *
     * @return static
     */
    public static function createForProductAttribute(IsotopeProduct $objProduct, $strAttribute, $arrConfig)
    {
        $objGallery = static::findByPk((int) $arrConfig['gallery']);

        if (null === $objGallery) {
            $objGallery = new \Isotope\Model\Gallery\Standard();
        }

        $objGallery->setName($objProduct->getFormId() . '_' . $strAttribute);
        $objGallery->setFiles(static::mergeMediaData(
            deserialize($objProduct->$strAttribute, true),
            deserialize($objProduct->{$strAttribute . '_fallback'}, true)
        ));
        $objGallery->product_id = ($objProduct->pid ? $objProduct->pid : $objProduct->id);
        $objGallery->href       = $objProduct->generateUrl($arrConfig['jumpTo']);

        return $objGallery;
    }

    /**
     * Merge media manager data from fallback and translated product data
     *
     * @param array $arrCurrent
     * @param array $arrParent
     *
     * @return array
     */
    public static function mergeMediaData($arrCurrent, $arrParent)
    {
        $arrTranslate = array();

        if (!empty($arrParent) && is_array($arrParent)) {

            // Create an array of images where key = image name
            foreach ($arrParent as $image) {
                if ($image['translate'] != 'all') {
                    $arrTranslate[$image['src']] = $image;
                }
            }
        }

        if (!empty($arrCurrent) && is_array($arrCurrent)) {
            foreach ($arrCurrent as $i => $image) {

                if (isset($arrTranslate[$image['src']])) {
                    if ($arrTranslate[$image['src']]['translate'] == 'none') {
                        $arrCurrent[$i] = $arrTranslate[$image['src']];
                    } else {
                        $arrCurrent[$i]['link']      = $arrTranslate[$image['src']]['link'];
                        $arrCurrent[$i]['translate'] = $arrTranslate[$image['src']]['translate'];
                    }

                    unset($arrTranslate[$image['src']]);

                } elseif ($arrCurrent[$i]['translate'] != 'all') {
                    unset($arrCurrent[$i]);
                }
            }

            // Add remaining parent image to the list
            if (!empty($arrTranslate)) {
                $arrCurrent = array_merge($arrCurrent, array_values($arrTranslate));
            }

            $arrCurrent = array_values($arrCurrent);

        } else {
            $arrCurrent = array_values($arrTranslate);
        }

        return $arrCurrent;
    }
}
