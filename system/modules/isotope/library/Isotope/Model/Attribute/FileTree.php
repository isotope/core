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
 * Attribute to impelement FileTree widget
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class FileTree extends Attribute implements IsotopeAttribute
{

    public function saveToDCA(array &$arrData)
    {
        parent::saveToDCA($arrData);

        $arrData['fields'][$this->field_name]['sql'] = "binary(16) NULL";

        if ($this->fieldType == 'checkbox') {
            $arrData['fields'][$this->field_name]['eval']['multiple'] = true;
            $arrData['fields'][$this->field_name]['sql'] = "blob NULL";
        }
    }

    public function generate(IsotopeProduct $objProduct, array $arrOptions = array())
    {
        $varValue = $objProduct->{$this->field_name};

        if ($this->fieldType == 'checkbox') {
            $varValue = deserialize($varValue, true);
        }

        $objFiles = \FilesModel::findMultipleByIds((array) $varValue);

        if (null !== $objFiles) {
            return $this->generateList($objFiles->fetchEach('path'));
        }

        return '';
    }
}
