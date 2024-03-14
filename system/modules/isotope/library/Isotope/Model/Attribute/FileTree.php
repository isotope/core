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

use Contao\File;
use Contao\FilesModel;
use Contao\StringUtil;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Model\Attribute;

/**
 * Attribute to implement FileTree widget
 */
class FileTree extends Attribute
{
    /**
     * @inheritdoc
     */
    public function saveToDCA(array &$arrData)
    {
        parent::saveToDCA($arrData);

        if ('checkbox' === $this->fieldType) {
            $arrData['fields'][$this->field_name]['sql'] = 'blob NULL';
            $arrData['fields'][$this->field_name]['eval']['multiple'] = true;
            $this->multiple = true;

            // Custom sorting
            if ('custom' === $this->sortBy) {
                $arrData['fields'][$this->field_name]['eval']['orderField'] = $this->getOrderFieldName();
                $arrData['fields'][$this->getOrderFieldName()]['sql'] = 'blob NULL';
            }
        } else {
            $arrData['fields'][$this->field_name]['sql'] = 'binary(16) NULL';
            $arrData['fields'][$this->field_name]['eval']['multiple'] = false;
            $this->multiple = false;
        }
    }

    /**
     * Make sure array values are unserialized.
     *
     *
     * @return mixed
     */
    public function getValue(IsotopeProduct $product)
    {
        $value = parent::getValue($product);

        if ('checkbox' === $this->fieldType) {
            $value = StringUtil::deserialize($value);
        }

        return (array) $value;
    }

    /**
     * @inheritdoc
     */
    public function generate(IsotopeProduct $objProduct, array $arrOptions = array())
    {
        $varValue = $this->getValue($objProduct);

        /** @var FilesModel[] $objFiles */
        $objFiles = FilesModel::findMultipleByUuids((array) $varValue);

        if (null !== $objFiles) {
            $files = [];

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

            if ($arrOptions['noHtml']) {
                if (!$this->multiple) {
                    return reset($files);
                }

                return $files;
            }

            return $this->generateList($files);
        }

        return '';
    }

    /**
     * Sort the files
     *
     * @param FilesModel[]  $files
     *
     * @return array
     */
    private function sortFiles(array $files, IsotopeProduct $product)
    {
        switch ($this->sortBy) {
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

            case 'random':
                shuffle($files);
                break;

            case 'custom':
                if (($orderSource = $product->{$this->getOrderFieldName()}) != '') {
                    $tmp = StringUtil::deserialize($orderSource);

                    if (!empty($tmp) && \is_array($tmp)) {
                        // Remove all values
                        $order = array_map(
                            function () {
                            },
                            array_flip($tmp)
                        );

                        // Move the matching elements to their position in $order
                        foreach ($files as $k => $file) {
                            if (\array_key_exists($file->uuid, $order)) {
                                $order[$file->uuid] = $file;
                                unset($files[$k]);
                            }
                        }

                        // Append the left-over images at the end
                        if (\count($files) > 0) {
                            $order = array_merge($order, array_values($files));
                        }

                        // Remove empty (unreplaced) entries
                        $files = array_filter($order);
                        unset($order);
                    }
                }
                break;
        }

        return array_values($files);
    }

    /**
     * Get the sort date helper
     *
     * @param FilesModel[] $files
     *
     * @return array
     */
    private function getSortDateHelper(array $files)
    {
        $helper = [];

        foreach ($files as $fileModel) {
            $file = new File($fileModel->path);
            $helper[] = $file->mtime;
        }

        return $helper;
    }

    /**
     * Get the order field name
     *
     * @return string
     */
    private function getOrderFieldName()
    {
        return $this->field_name . '_order';
    }
}
