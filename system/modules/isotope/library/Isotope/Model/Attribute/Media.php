<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Attribute;

use Contao\ContentMedia;
use Contao\ContentModel;
use Contao\FilesModel;
use Contao\StringUtil;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Model\Attribute;

/**
 * Attribute to provide an audio/video player in the product details
 */
class Media extends Attribute
{
    /**
     * @inheritdoc
     */
    public function saveToDCA(array &$arrData)
    {
        parent::saveToDCA($arrData);

        $arrData['fields'][$this->field_name]['sql'] = "blob NULL";
        $arrData['fields'][$this->field_name]['eval']['fieldType'] = 'checkbox';
        $arrData['fields'][$this->field_name]['eval']['multiple'] = true;
        $arrData['fields'][$this->field_name]['eval']['files'] = true;
        $arrData['fields'][$this->field_name]['eval']['filesOnly'] = true;
        $arrData['fields'][$this->field_name]['eval']['extensions'] = 'mp4,m4v,mov,wmv,webm,ogv,m4a,mp3,wma,mpeg,wav,ogg,' . $GLOBALS['TL_CONFIG']['validImageTypes'];
    }

    /**
     * @inheritdoc
     */
    public function getBackendWidget()
    {
        return $GLOBALS['BE_FFL']['fileTree'];
    }

    /**
     * @inheritdoc
     */
    public function generate(IsotopeProduct $objProduct, array $arrOptions = array())
    {
        $strPoster = null;
        $arrFiles = StringUtil::deserialize($objProduct->{$this->field_name}, true);

        // Return if there are no files
        if (empty($arrFiles) || !\is_array($arrFiles)) {
            return '';
        }

        // Get the file entries from the database
        $objFiles = FilesModel::findMultipleByIds($arrFiles);

        if (null === $objFiles) {
            return '';
        }

        // Find poster
        while ($objFiles->next()) {
            if (\in_array($objFiles->extension, StringUtil::trimsplit(',', $GLOBALS['TL_CONFIG']['validImageTypes']))) {
                $strPoster = $objFiles->uuid;
                $arrFiles = array_diff($arrFiles, array($objFiles->uuid));
            }
        }

        $objContentModel = new ContentModel();
        $objContentModel->tstamp = time();
        $objContentModel->type = 'media';
        $objContentModel->cssID = serialize(array('', $this->field_name));
        $objContentModel->playerSRC = serialize($arrFiles);
        $objContentModel->posterSRC = $strPoster;

        if ($arrOptions['autoplay']) {
            $objContentModel->autoplay = '1';
        }

        if ($arrOptions['width'] || $arrOptions['height']) {
            $objContentModel->playerSize = serialize(array($arrOptions['width'], $arrOptions['height']));
        }

        $objElement = new ContentMedia($objContentModel);
        return $objElement->generate();
    }
}
