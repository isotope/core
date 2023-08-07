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

use Contao\Controller;
use Contao\Database;
use Contao\StringUtil;
use Contao\System;

class To0020010064 extends System
{

    public function run($blnInstalled)
    {
        if ($blnInstalled) {

            Controller::loadDataContainer('tl_iso_product');

            $arrFields = array();

            foreach ($GLOBALS['TL_DCA']['tl_iso_product']['fields'] as $field => $config) {
                if ('mediaManager' === ($config['inputType'] ?? '')) {
                    $arrFields[] = $field;
                }
            }

            if (0 === \count($arrFields)) {
                return;
            }

            $arrProducts = Database::getInstance()->query("
                SELECT * FROM tl_iso_product WHERE language=''
            ")->fetchAllAssoc();

            foreach ($arrProducts as $arrProduct) {
                $arrUpdate = array();

                foreach ($arrFields as $field) {
                    $arrData = StringUtil::deserialize($arrProduct[$field]);

                    if (!empty($arrData) && \is_array($arrData)) {
                        foreach ($arrData as $k => $image) {
                            if (($image['translate'] ?? '') == '') {
                                $arrData[$k]['translate'] = 'none';
                            }
                        }

                        $arrUpdate[$field] = serialize($arrData);
                    }
                }

                if (0 !== \count($arrUpdate)) {
                    Database::getInstance()->prepare(
                        'UPDATE tl_iso_product %s WHERE id=?'
                    )->set($arrUpdate)->execute($arrProduct['id']);
                }
            }
        }
    }
}
