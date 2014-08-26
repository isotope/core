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


class To0020010064 extends \System
{

    public function run($blnInstalled)
    {
        if ($blnInstalled) {

            \Controller::loadDataContainer('tl_iso_product');

            $arrFields = array();

            foreach ($GLOBALS['TL_DCA']['tl_iso_product']['fields'] as $field => $config) {
                if ($config['inputType'] == 'mediaManager') {
                    $arrFields[] = $field;
                }
            }

            if (empty($arrFields)) {
                return;
            }

            $objProducts = \Database::getInstance()->query("
                SELECT * FROM tl_iso_product WHERE language=''
            ");

            while ($objProducts->next()) {
                foreach ($arrFields as $field) {
                    $arrUpdate = array();
                    $arrData = deserialize($objProducts->$field);

                    if (!empty($arrData) && is_array($arrData)) {
                        foreach ($arrData as $k => $image) {
                            if ($image['translate'] == '') {
                                $arrData[$k]['translate'] = 'none';
                            }
                        }

                        $arrUpdate[$field] = serialize($arrData);
                    }
                }

                if (!empty($arrUpdate)) {
                    \Database::getInstance()->prepare(
                        "UPDATE tl_iso_product %s WHERE id=?"
                    )->set($arrUpdate)->execute($objProducts->id);
                }
            }
        }
    }
}
