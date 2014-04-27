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

namespace Isotope\BackendModule;

use Isotope\Isotope;

class Integrity extends \BackendModule
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'be_iso_integrity';

    /**
     * Generate the module
     * @return string
     */
    public function generate()
    {
        if (!\BackendUser::getInstance()->isAdmin) {
            return '<p class="tl_gerror">Only admin can perform integrity check.</p>';
        }

        return parent::generate();
    }

    /**
     * Generate the module
     */
    protected function compile()
    {
        $arrChecks = array();

        $arrChecks[] = $this->validatePriceTable();

        $this->Template->checks = $arrChecks;
        $this->Template->back = str_replace('&mod=integrity', '', \Environment::get('request'));
    }

    /**
     * Check for invalid information in the prices table
     * @return array
     */
    protected function validatePriceTable()
    {
        $arrProducts = \Database::getInstance()->query("
            SELECT id FROM tl_iso_product
            WHERE (
                    pid=0
                    AND type IN (SELECT id FROM tl_iso_producttype WHERE prices='')
                )
                OR (
                    pid>0 AND language=''
                    AND pid IN (SELECT id FROM tl_iso_product WHERE type IN (SELECT id FROM tl_iso_producttype WHERE prices=''))
                )
        ")->fetchEach('id');

        if (!empty($arrProducts)) {
            $objPrices = \Database::getInstance()->query("
                SELECT id, pid, COUNT(*) AS total FROM tl_iso_product_price WHERE " . \Database::getInstance()->findInSet('pid', implode(',', $arrProducts)) . " GROUP BY pid HAVING total>1"
            );

            if ($objPrices->numRows) {
                return array(
                    'id'        => 'prices',
                    'name'      => 'Erweiterte Preise',
                    'result'    => sprintf('Es wurde %s ungültige erweiterte Preise gefunden (PIDs: %s).', $objPrices->numRows, implode(', ', $objPrices->fetchEach('pid'))),
                    'action'    => true,
                );
            }
        }

        return array(
            'id'        => 'prices',
            'name'      => 'Erweiterte Preise',
            'result'    => 'Es wurden keine ungültigen Erweiterten Preise gefunden.',
            'action'    => false,
        );
    }
}
