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
        if ($this->sortBy === 'custom') {
            $arrData['fields'][$this->field_name]['eval']['orderField'] = $this->getOrderFieldName();
            $arrData['fields'][$this->getOrderFieldName()]              = ['sql' => "blob NULL"];
        }
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
                if (!is_file(TL_ROOT.'/'.$objFile->path)) {
                    continue;
                }

                $files[$objFile->path] = $objFile;
            }

            // Sort the files
            $files = $this->sortFiles($files, $objProduct);

            // Convert the file models to paths
            foreach ($files as $k => $v) {
                $files[$k] = $v->path;
            }

            return $this->generateList($files);
        }

        return '';
    }

    /**
     * Sort the files
     *
     * @param array $files
     * @param IsotopeProduct $product
     *
     * @return array
     */
    protected function sortFiles(array $files, IsotopeProduct $product)
    {
        switch ($this->sortBy)
        {
            default:
            case 'name_asc':
                uksort($files, 'basename_natcasecmp');
                break;

            case 'name_desc':
                uksort($files, 'basename_natcasercmp');
                break;

            case 'date_asc':
                array_multisort($files, SORT_NUMERIC, $this->getSortDateHelper($files), SORT_ASC);
                break;

            case 'date_desc':
                array_multisort($files, SORT_NUMERIC, $this->getSortDateHelper($files), SORT_DESC);
                break;

            case 'custom':
                if (($orderSource = $product->{$this->getOrderFieldName()}) != '') {
                    $tmp = deserialize($orderSource);

                    if (!empty($tmp) && is_array($tmp)) {
                        // Remove all values
                        $order = array_map(function () {
                        }, array_flip($tmp));

                        // Move the matching elements to their position in $order
                        /** @var \FilesModel $file */
                        foreach ($files as $k => $file) {
                            if (array_key_exists($file->uuid, $order)) {
                                $order[$file->uuid] = $file;
                                unset($files[$k]);
                            }
                        }

                        // Append the left-over images at the end
                        if (count($files) > 0) {
                            $order = array_merge($order, array_values($files));
                        }

                        // Remove empty (unreplaced) entries
                        $files = array_filter($order);
                        unset($order);
                    }
                }
                break;

            case 'random':
                shuffle($files);
                break;
        }

        return array_values($files);
    }

    /**
     * Get the sort date helper
     *
     * @param array $files
     *
     * @return array
     */
    protected function getSortDateHelper(array $files)
    {
        $helper = [];

        /** @var \FilesModel $fileModel */
        foreach ($files as $fileModel) {
            $file = new \File($fileModel->path);
            $helper[] = $file->mtime;
        }

        return $helper;
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
