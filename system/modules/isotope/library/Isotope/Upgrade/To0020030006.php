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

class To0020030006 extends Base
{
    /**
     * @var Database
     */
    private $db;

    public function run($blnInstalled)
    {
        $this->db = Database::getInstance();

        if ($blnInstalled) {
            $this->updateProductCollectionUuid();
            $this->updateCumulativeFilterFields();
        }
    }

    /**
     * tl_iso_product_collection.uniqid is NULL by default in Isotope 2.3
     */
    private function updateProductCollectionUuid()
    {
        // Will update the field definition
        $this->renameDatabaseField('uniqid', 'uniqid', 'tl_iso_product_collection');

        $this->db->query("UPDATE tl_iso_product_collection SET uniqid=NULL WHERE uniqid=''");
    }

    /**
     * Convert iso_filterFields configuration for new cumulative filter
     */
    private function updateCumulativeFilterFields()
    {
        if ($this->createDatabaseField('iso_cumulativeFields', 'tl_module')) {
            $modules = $this->db->query(
                "SELECT id, iso_filterFields FROM tl_module WHERE type='iso_cumulativefilter'"
            );

            while ($modules->next()) {
                $fields = StringUtil::deserialize($modules->iso_filterFields);

                if (!empty($fields) && \is_array($fields)) {
                    $config = array();

                    foreach ($fields as $field) {
                        $config[] = array(
                            'attribute'  => $field,
                            'queryType'  => 'and',
                            'matchCount' => 'none',
                        );
                    }

                    $this->db
                        ->prepare("UPDATE tl_module SET iso_cumulativeFields=? WHERE id=?")
                        ->execute(serialize($config), $modules->id)
                    ;
                }
            }
        }
    }
}
