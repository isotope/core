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

namespace Isotope\Model\Attribute;

use Isotope\Interfaces\IsotopeAttribute;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Model\Attribute;


/**
 * Attribute to provide downloads in the product details
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Christoph Wiechert <cw@4wardmedia.de>
 */
class Downloads extends Attribute implements IsotopeAttribute
{
    public function saveToDCA(array &$arrData)
    {
        parent::saveToDCA($arrData);

        $arrData['fields'][$this->field_name]['sql'] = "blob NULL";

        if ($this->fieldType == 'checkbox') {
            $arrData['fields'][$this->field_name]['sql'] = "blob NULL";
            $arrData['fields'][$this->field_name]['eval']['multiple'] = true;

            // Custom sorting
            if ($this->sortBy == 'custom') {
                $strOrderField = $this->field_name . '_order';
                $arrData['fields'][$this->field_name]['eval']['orderField'] = $strOrderField;
                $arrData['fields'][$strOrderField]['sql'] = "blob NULL";
            }
        } else {
            $arrData['fields'][$this->field_name]['sql'] = "binary(16) NULL";
            $arrData['fields'][$this->field_name]['eval']['multiple'] = false;
        }
    }

    /**
     * Return class name for the backend widget or false if none should be available
     * @return    string
     */
    public function getBackendWidget()
    {
        return $GLOBALS['BE_FFL']['fileTree'];
    }


    /**
     * Generate download attributes
     *
     * @param \Isotope\Interfaces\IsotopeProduct $objProduct
     * @param array $arrOptions
     * @return string
     */
    public function generate(IsotopeProduct $objProduct, array $arrOptions = array())
    {
        $objContentModel = new \ContentModel();
        $objContentModel->type = 'downloads';
        $objContentModel->multiSRC = $objProduct->{$this->field_name};
        $objContentModel->sortBy = $this->sortBy;
        $objContentModel->orderSRC = $this->orderSRC;
        $objContentModel->cssID = serialize(array('', $this->field_name));

        $objElement = new \ContentDownloads($objContentModel);
        return $objElement->generate();
    }
}
