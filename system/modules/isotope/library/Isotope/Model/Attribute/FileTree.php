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

        // Make the field sortable
        $arrData['fields'][$this->field_name]['eval']['orderField'] = $this->getOrderFieldName();
        $arrData['fields'][$this->getOrderFieldName()]              = ['sql' => "blob NULL"];
    }

    public function generate(IsotopeProduct $objProduct, array $arrOptions = array())
    {
        $varValue = $objProduct->{$this->field_name};

        if ($this->fieldType == 'checkbox') {
            $varValue = deserialize($varValue, true);
        }

        $objFiles = \FilesModel::findMultipleByUuids((array) $varValue);

        if (null !== $objFiles) {
            $files = [];

            /** @var \FilesModel $objFile */
            foreach ($objFiles as $objFile) {
                $files[$objFile->uuid] = $objFile->path;
            }

            // Order the files
            if (($orderSource = $objProduct->{$this->getOrderFieldName()}) != '') {
                $tmp = deserialize($orderSource);

                if (!empty($tmp) && is_array($tmp)) {
                    // Remove all values
                    $order = array_map(function () {
                    }, array_flip($tmp));

                    // Move the matching elements to their position in $order
                    foreach ($files as $k => $v) {
                        if (array_key_exists($k, $order)) {
                            $order[$k] = $v;
                            unset($files[$k]);
                        }
                    }

                    // Append the left-over images at the end
                    if (count($files) > 0) {
                        $order = array_merge($order, array_values($files));
                    }

                    // Remove empty (unreplaced) entries
                    $files = array_values(array_filter($order));
                    unset($order);
                }
            }

            return $this->generateList(array_values($files));
        }

        return '';
    }

    /**
     * Get the order field name
     *
     * @return string
     */
    protected function getOrderFieldName()
    {
        return $this->field_name.'_order';
    }
}
