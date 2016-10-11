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

use Contao\Files;
use Contao\Folder;
use Haste\Util\FileUpload;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Model\Attribute;
use Isotope\Model\ProductCollectionItem;

/**
 * Attribute to implement frontend uploads
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class Upload extends Attribute implements \uploadable
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

        // Files are stored by Isotope
        $arrData['fields'][$this->field_name]['eval']['storeFile'] = false;
        unset($arrData['fields'][$this->field_name]['attributes']['storeFile']);
        $arrData['fields'][$this->field_name]['save_callback'][] = 'processFiles';
    }

    /**
     * @inheritdoc
     */
    public function generateValue($value, array $options = [])
    {
        if (empty($value)) {
            return '';
        }

        /** @var ProductCollectionItem $item */
        if (($item = $options['item']) instanceof ProductCollectionItem && !is_file(TL_ROOT . '/' . $value)) {
            $item->addError('File does not exist.'); // TODO add real error message
        }

        return substr(basename($value), 9);
    }

    /**
     * @param mixed          $value
     * @param IsotopeProduct $product
     * @param \Widget        $widget
     *
     * @return mixed
     */
    public function processFiles($value, IsotopeProduct $product, \Widget $widget)
    {
        if (!isset($_SESSION['FILES'][$this->field_name]) || empty($_SESSION['FILES'][$this->field_name]['name'])) {
            return $value;
        }

        $file = $_SESSION['FILES'][$this->field_name]['name'];
        $temp = $_SESSION['FILES'][$this->field_name]['tmp_name'];
        unset($_SESSION['FILES'][$this->field_name]);

        // Make sure the upload folder exists and is protected
        $folder = new Folder('isotope/uploads');
        $folder->protect();

        $file = substr(md5_file($temp), 0, 8) . '-' . $file;
        $file = FileUpload::getFileName($file, $folder->path);
        $file = $folder->path . '/' . $file;

        Files::getInstance()->move_uploaded_file($temp, $file);
        Files::getInstance()->chmod($file, \Config::get('defaultFileChmod'));

        return $file;
    }
}
