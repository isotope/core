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

use Isotope\Interfaces\IsotopeProduct;


/**
 * Class Shipping
 *
 * Parent class for all gallery types
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
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
     * @param   IsotopeProduct
     * @param   string
     * @param   array
     * @return  Gallery
     */
    public static function createForProductAttribute(IsotopeProduct $objProduct, $strAttribute, $arrConfig)
    {
        $objGallery = static::findByPk((int) $arrConfig['gallery']);

        if (null === $objGallery) {
            $objGallery = new \Isotope\Model\Gallery\Standard();
        }

        $objGallery->setName($objProduct->formSubmit . '_' . $strAttribute);
        $objGallery->setFiles($objProduct->$strAttribute); //Isotope::mergeMediaData($objProduct->{$this->field_name}, deserialize($objProduct->{$strKey.'_fallback'})));
        $objGallery->product_id = ($objProduct->pid ? $objProduct->pid : $objProduct->id);
        $objGallery->href_reader = $objProduct()->generateUrl($arrConfig['reader_page']);

        return $objGallery;
    }
}
