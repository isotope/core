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

use Contao\Config;
use Contao\Files;
use Contao\Folder;
use Contao\Widget;
use Haste\Util\FileUpload;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Model\Attribute;
use Isotope\Model\ProductCollectionItem;


/**
 * Attribute to implement terminal42/contao-fineuploader
 */
class FineUploader extends Attribute implements \uploadable
{

    /**
     * Upload widget is always customer defined
     * @return    bool
     */
    public function isCustomerDefined()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getBackendWidget()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function saveToDCA(array &$arrData)
    {
        parent::saveToDCA($arrData);

        unset($arrData['fields'][$this->field_name]['sql']);

        // An upload field is always customer defined
        $arrData['fields'][$this->field_name]['attributes']['customer_defined'] = true;

        $arrData['fields'][$this->field_name]['eval']['storeFile'] = false;
        $arrData['fields'][$this->field_name]['save_callback'][] = 'processFiles';
        $arrData['fields'][$this->field_name]['eval']['doNotOverwrite'] = true;
        $arrData['fields'][$this->field_name]['eval']['useHomeDir'] = false;
        $arrData['fields'][$this->field_name]['eval']['addToDbafs'] = false;

        if ($this->multiple) {
            $arrData['fields'][$this->field_name]['eval']['multiple'] = true;
            $arrData['fields'][$this->field_name]['eval']['uploaderLimit'] = (int) $this->size;
        } else {
            $arrData['fields'][$this->field_name]['eval']['multiple'] = false;
            $arrData['fields'][$this->field_name]['eval']['uploaderLimit'] = 1;
        }
    }

    /**
     * @inheritdoc
     */
    public function generateValue($value, array $options = [])
    {
        if (empty($value)) {
            return '';
        }

        $value = \is_array($value) ? $value : [$value];
        $parsed = [];

        foreach ($value as $file) {
            /** @var ProductCollectionItem $item */
            if (($item = $options['item']) instanceof ProductCollectionItem && !is_file(TL_ROOT . '/' . $file)) {
                $item->addError($GLOBALS['TL_LANG']['ERR']['uploadNotFound']);
            }

            $parsed[] = substr(basename($file), 9);
        }

        return implode(', ', $parsed);
    }

    /**
     * @param mixed          $value
     * @param IsotopeProduct $product
     * @param Widget         $widget
     *
     * @return mixed
     */
    public function processFiles($value, IsotopeProduct $product, Widget $widget)
    {
        $files = [];

        foreach (array_filter((array) $value) as $temp) {
            $file = basename($temp);

            // Make sure the upload folder exists and is protected
            $folder = new Folder('isotope/uploads');
            $folder->protect();

            $file = substr(md5_file(TL_ROOT . '/' . $temp), 0, 8) . '-' . $file;
            $file = FileUpload::getFileName($file, $folder->path);
            $file = $folder->path . '/' . $file;

            Files::getInstance()->rename($temp, $file);
            Files::getInstance()->chmod($file, Config::get('defaultFileChmod'));

            $files[] = $file;
        }

        return \is_array($value) ? $files : $files[0];
    }
}
