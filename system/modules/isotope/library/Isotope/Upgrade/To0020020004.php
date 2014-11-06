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

namespace Isotope\Upgrade;


class To0020020004 extends Base
{

    public function run($blnInstalled)
    {
        if ($blnInstalled) {
            $database = \Database::getInstance();

            // Convert tl_iso_rule.discount into inputUnit style
            if ($database->tableExists('tl_iso_rule')) {
                $resultSet = $database
                    ->query('SELECT * FROM tl_iso_rule WHERE discount REGEXP "^[+-]?[0-9]+(\.[0-9]+)?%?$"');

                while ($resultSet->next()) {
                    if (preg_match('~^([\+\-]?\d+(?:\.\d+)?)(\%?)$~', $resultSet->discount, $matches)) {
                        $discount = array(
                            'value' => $matches[1],
                            'unit'  => $matches[2]
                        );

                        $database
                            ->prepare('UPDATE tl_iso_rule SET discount=? WHERE id=?')
                            ->execute(serialize($discount), $resultSet->id);
                    }
                }
            }
            $this->createDatabaseField('product_types_condition', 'tl_iso_shipping');
            $this->createDatabaseField('product_types_condition', 'tl_iso_payment');
        }
    }
}
