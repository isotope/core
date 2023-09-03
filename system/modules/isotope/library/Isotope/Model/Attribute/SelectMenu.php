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
use Isotope\Interfaces\IsotopeAttributeForVariants;

/**
 * Attribute to impelement SelectMenu widget
 */
class SelectMenu extends AbstractAttributeWithOptions implements IsotopeAttributeForVariants
{
    /**
     * @inheritdoc
     */
    public function prepareOptionsWizard($objWidget, $arrColumns)
    {
        if ($this->isVariantOption()) {
            unset($arrColumns['default'], $arrColumns['group']);
        }

        return $arrColumns;
    }

    /**
     * @inheritdoc
     */
    public function saveToDCA(array &$arrData)
    {
        // Varian select menu cannot have multiple option
        if ($this->isVariantOption()) {
            $this->multiple           = false;
            $this->size               = 1;
        }

        parent::saveToDCA($arrData);

        if ($this->isVariantOption()) {
            $arrData['fields'][$this->field_name]['eval']['includeBlankOption'] = true;
        }

        if ($this->multiple) {
            $arrData['fields'][$this->field_name]['sql'] = 'blob NULL';
        } else {
            if ('attribute' === $this->optionsSource) {
                $length = 64;

                array_walk(StringUtil::deserialize($this->options, true), function($option) use (&$length) {
                    $length = max(ceil(mb_strlen($option['value'] ?? '') / 64) * 64, $length);
                });

                $arrData['fields'][$this->field_name]['sql'] = "varchar($length) NOT NULL default ''";
            } else {
                $arrData['fields'][$this->field_name]['sql'] = 'int(10) NOT NULL default 0';
            }

            if ($this->fe_filter) {
                $arrData['config']['sql']['keys'][$this->field_name] = 'index';
            }
        }
    }
}
