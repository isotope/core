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
use Isotope\Model\Attribute;
use Isotope\Model\ProductCollectionItem;


/**
 * Attribute to implement terminal42/contao-fineuploader
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class FineUploader extends Attribute implements IsotopeAttribute, \uploadable
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

        $arrData['fields'][$this->field_name]['eval']['storeFile'] = true;
        $arrData['fields'][$this->field_name]['eval']['uploadFolder'] = 'isotope/uploads';
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

        /** @var ProductCollectionItem $item */
        if (($item = $options['item']) instanceof ProductCollectionItem && !is_file(TL_ROOT . '/' . $value)) {
            $item->addError('File does not exist.'); // TODO add real error message
        }

        return substr(basename($value), 9);
    }
}
