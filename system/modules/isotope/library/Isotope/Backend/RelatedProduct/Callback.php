<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\RelatedProduct;

use Contao\Backend;
use Contao\Database;
use Contao\Input;
use Contao\StringUtil;
use Isotope\Model\RelatedCategory;


class Callback extends Backend
{

    /**
     * Add an image to each record
     * @param array
     * @param string
     * @return string
     */
    public function listRows($row)
    {
        $strCategory = RelatedCategory::findByPk($row['category'])->name;

        $strBuffer = '<div class="cte_type" style="color:#666966"><strong>' . $GLOBALS['TL_LANG']['tl_iso_related_product']['category'][0] . ':</strong> ' . $strCategory . '</div>';

        $arrProducts = StringUtil::trimsplit(',', $row['products']);

        if (!empty($arrProducts) && \is_array($arrProducts)) {
            $strBuffer .= '<div class="limit_height' . (!$GLOBALS['TL_CONFIG']['doNotCollapse'] ? ' h0' : '') . ' block"><ul>';
            $objProducts = Database::getInstance()->execute("SELECT * FROM tl_iso_product WHERE " . Database::getInstance()->findInSet('id', $arrProducts) . " ORDER BY name");

            while ($objProducts->next()) {
                $strBuffer .= '<li>' . $objProducts->name . '</li>';
            }

            $strBuffer .= '</ul></div>' . "\n";
        }

        return $strBuffer;
    }


    /**
     * Initialize the data container
     * @param object
     */
    public function initDCA($dc)
    {
        $arrCategories = array();
        $objCategories = Database::getInstance()
            ->prepare('
                SELECT * FROM tl_iso_related_category
                WHERE id NOT IN (
                    SELECT category FROM tl_iso_related_product
                    WHERE pid=' . (\strlen(Input::get('act')) ? "(SELECT pid FROM tl_iso_related_product WHERE id=?) AND id!=?" : '?') . '
                )
            ')
            ->execute($dc->id, $dc->id)
        ;

        while ($objCategories->next()) {
            $arrCategories[$objCategories->id] = $objCategories->name;
        }

        if (empty($arrCategories)) {
            $GLOBALS['TL_DCA']['tl_iso_related_product']['config']['closed'] = true;
        }

        if ('edit' === Input::get('act')) {
            unset($GLOBALS['TL_DCA']['tl_iso_related_product']['fields']['category']['foreignKey']);
            $GLOBALS['TL_DCA']['tl_iso_related_product']['fields']['category']['options']            = $arrCategories;
            $GLOBALS['TL_DCA']['tl_iso_related_product']['fields']['products']['eval']['allowedIds'] = Database::getInstance()->prepare("SELECT id FROM tl_iso_product WHERE pid=0 AND id!=(SELECT pid FROM tl_iso_related_product WHERE id=?)")->execute($dc->id)->fetchEach('id');
        }
    }
}
