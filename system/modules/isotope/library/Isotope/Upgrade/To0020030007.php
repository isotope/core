<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Upgrade;

use Contao\Database;
use Contao\StringUtil;
use Isotope\Interfaces\IsotopeAttributeWithOptions;
use Isotope\Model\Attribute;

class To0020030007 extends Base
{
    /**
     * @var Database
     */
    private $db;

    public function run($blnInstalled)
    {
        $this->db = Database::getInstance();

        if ($blnInstalled) {
            $this->convertSerializedValues();
        }
    }

    /**
     * Multiple-fields (e.g. select or checkbox) with numberic keys are stored as CSV to improve filters.
     */
    private function convertSerializedValues()
    {
        $t      = Attribute::getTable();
        $fields = array();

        $attributes = Attribute::findBy(array("$t.multiple='1' AND $t.optionsSource='table'"), null);

        if (null !== $attributes) {
            /** @var Attribute $attribute */
            foreach ($attributes as $attribute) {
                if ($attribute instanceof IsotopeAttributeWithOptions && !empty($attribute->field_name)) {
                    $fields[] = $attribute->field_name;
                }
            }
        }

        if (!empty($fields)) {
            /** @var \Database\Result|object $products */
            $products = $this->db->execute("
                SELECT id, " . implode(', ', $fields) . "
                FROM tl_iso_product
                WHERE " . implode(" IS NOT NULL OR ", $fields) . " IS NOT NULL
            ");

            while ($products->next()) {
                $set = array();

                foreach ($fields as $field) {
                    $value = StringUtil::deserialize($products->$field);

                    if (!empty($value) && \is_array($value)) {
                        $set[$field] = implode(',', $value);
                    }
                }

                if (!empty($set)) {
                    $this->db
                        ->prepare("UPDATE tl_iso_product %s WHERE id=?")
                        ->set($set)
                        ->execute($products->id)
                    ;
                }
            }
        }
    }
}
