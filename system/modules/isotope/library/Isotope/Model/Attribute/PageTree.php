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

use Contao\StringUtil;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Model\Attribute;

/**
 * Attribute to implement PageTree widget
 *
 * @property array $rootNodes
 */
class PageTree extends Attribute
{
    /**
     * @inheritdoc
     */
    public function saveToDCA(array &$arrData)
    {
        parent::saveToDCA($arrData);

        $arrData['fields'][$this->field_name]['foreignKey'] = 'tl_page.title';
        $arrData['fields'][$this->field_name]['eval']['rootNodes'] = StringUtil::deserialize($this->rootNodes);

        if ('checkbox' === $this->fieldType) {
            $arrData['fields'][$this->field_name]['sql'] = 'blob NULL';
            $arrData['fields'][$this->field_name]['eval']['multiple'] = true;

            // Custom sorting
            $arrData['fields'][$this->field_name]['eval']['orderField'] = $this->getOrderFieldName();
            $arrData['fields'][$this->getOrderFieldName()]['sql'] = 'blob NULL';
        } else {
            $arrData['fields'][$this->field_name]['sql'] = 'binary(16) NULL';
            $arrData['fields'][$this->field_name]['eval']['multiple'] = false;
        }
    }

    /**
     * Make sure array values are unserialized.
     *
     * @param IsotopeProduct $product
     *
     * @return mixed
     */
    public function getValue(IsotopeProduct $product)
    {
        $value = parent::getValue($product);

        if ('checkbox' === $this->fieldType) {
            $value = StringUtil::deserialize($value, true);

            // Drag&drop sorting order
            if (($orderSource = $product->{$this->getOrderFieldName()}) != '') {
                $tmp = StringUtil::deserialize($orderSource);

                if (!empty($tmp) && \is_array($tmp)) {
                    $value = array_merge(
                        array_intersect($tmp, $value),
                        array_diff($value, $tmp)
                    );
                }
            }

            return array_map('intval', (array) $value);
        }

        return (int) $value;
    }

    /**
     * @inheritdoc
     */
    public function generate(IsotopeProduct $objProduct, array $arrOptions = array())
    {
        $value = $this->getValue($objProduct);

        if (!\is_array($value)) {
            return '{{link::'.$value.'}}';
        }

        return $this->generateList(
            array_map(
                function ($pageId) {
                    return '{{link::'.$pageId.'}}';
                },
                $value
            )
        );
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
