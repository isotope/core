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
 */
class Media extends Attribute implements IsotopeAttribute
{
    public function saveToDCA(array &$arrData)
    {
        parent::saveToDCA($arrData);

        $arrData['fields'][$this->field_name]['sql'] = "blob NULL";
        $arrData['fields'][$this->field_name]['eval']['fieldType'] = "checkbox";
        $arrData['fields'][$this->field_name]['eval']['multiple'] = true;
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
        $arrFiles = deserialize($objProduct->{$this->field_name}, true);
        $poster = null;

        // Return if there are no files
        if (empty($arrFiles) || !is_array($arrFiles)) {
            return '';
        }

        // Get the file entries from the database
        $objFiles = \FilesModel::findMultipleByIds($arrFiles);

        if (null === $objFiles) {
            return '';
        }

        // Find poster
        while ($objFiles->next()) {
            if (in_array($objFiles->extension, explode(',', $GLOBALS['TL_CONFIG']['validImageTypes']))) {
                $poster = $objFiles->uuid;
                $arrFiles = array_diff($arrFiles, array($objFiles->uuid));
            }
        }

        $objContentModel = new \ContentModel();
        $objContentModel->type = 'media';
        $objContentModel->cssID = serialize(array('', $this->field_name));
        $objContentModel->playerSRC = serialize($arrFiles);
        $objContentModel->posterSRC = $poster;

        if ($arrOptions['autoplay']) {
            $objContentModel->autoplay = '1';
        }

        if ($arrOptions['width'] || $arrOptions['height']) {
            $objContentModel->playerSize = serialize(array($arrOptions['width'], $arrOptions['height']));
        }

        $objElement = new \ContentMedia($objContentModel);
        return $objElement->generate();
    }
}
