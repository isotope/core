<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\BackendModule;

use Contao\BackendUser;
use Contao\Database;
use Contao\StringUtil;
use Isotope\Isotope;
use Isotope\Report\Report;


class Reports extends BackendOverview
{

    protected function compile()
    {
        $this->Template->before = $this->getDailySummary();

        parent::compile();
    }

    /**
     * {@inheritdoc}
     */
    protected function getModules()
    {
        $arrReturn = array();
        $arrGroups = &$GLOBALS['BE_MOD']['isotope']['reports']['modules'];

        foreach ($arrGroups as $strGroup => $arrModules) {
            foreach ($arrModules as $strModule => $arrConfig) {

                if ($this->checkUserAccess($strModule)) {

                    $arrReturn[$strGroup]['modules'][$strModule] = array_merge($arrConfig, array
                    (
                        'name'          => $strModule,
                        'label'         => StringUtil::specialchars(($arrConfig['label'][0] ?: $strModule)),
                        'description'   => StringUtil::specialchars(strip_tags($arrConfig['label'][1])),
                        'href'          => $this->addToUrl('mod=' . $strModule),
                        'class'         => $arrConfig['class'] ?? '',
                    ));

                    // @deprecated remove ISO_LANG in Isotope 3.0
                    $arrReturn[$strGroup]['label'] = $strLegend = ($GLOBALS['TL_LANG']['ISO_REPORT'][$strGroup] ?: ($GLOBALS['ISO_LANG']['REPORT'][$strGroup] ?: $strGroup));
                }
            }
        }

        return $arrReturn;
    }

    /**
     * {@inheritdoc}
     */
    protected function checkUserAccess($module)
    {
        return BackendUser::getInstance()->isAdmin || BackendUser::getInstance()->hasAccess($module, 'iso_reports');
    }


    /**
     * Generate a daily summary for the overview page
     * @return array
     */
    protected function getDailySummary()
    {
        $strBuffer = '
<fieldset class="tl_tbox">
<legend style="cursor: default;">' . $GLOBALS['TL_LANG']['ISO_REPORT']['24h_summary'] . '</legend>
<div class="daily_summary">';

        $arrAllowedProducts = \Isotope\Backend\Product\Permission::getAllowedIds();

        $objOrders = Database::getInstance()->prepare("
            SELECT
                c.id AS config_id,
                c.name AS config_name,
                c.currency,
                COUNT(DISTINCT o.id) AS total_orders,
                SUM(i.tax_free_price * i.quantity) AS total_sales,
                SUM(i.quantity) AS total_items
            FROM tl_iso_product_collection o
            LEFT JOIN tl_iso_product_collection_item i ON o.id=i.pid
            LEFT OUTER JOIN tl_iso_config c ON o.config_id=c.id
            WHERE o.type='order' AND o.order_status>0 AND o.locked>=?
                " . Report::getProductProcedure('i', 'product_id') . "
                " . Report::getConfigProcedure('o', 'config_id') . "
            GROUP BY config_id
        ")->execute(strtotime('-24 hours'));

        if (!$objOrders->numRows) {

            $strBuffer .= '
<p class="tl_info">' . $GLOBALS['TL_LANG']['ISO_REPORT']['24h_empty'] . '</p>';

        } else {

            $i = -1;
            $strBuffer .= '
<div class="tl_listing_container list_view">
    <table class="tl_listing">
    <tr>
        <th class="tl_folder_tlist">' . $GLOBALS['TL_LANG']['ISO_REPORT']['shop_config'] . '</th>
        <th class="tl_folder_tlist">' . $GLOBALS['TL_LANG']['ISO_REPORT']['currency'] . '</th>
        <th class="tl_folder_tlist">' . $GLOBALS['TL_LANG']['ISO_REPORT']['orders#'] . '</th>
        <th class="tl_folder_tlist">' . $GLOBALS['TL_LANG']['ISO_REPORT']['products#'] . '</th>
        <th class="tl_folder_tlist">' . $GLOBALS['TL_LANG']['ISO_REPORT']['sales#'] . '</th>
    </tr>';


            while ($objOrders->next())
            {
                $strBuffer .= '
    <tr class="row_' . ++$i . ($i%2 ? 'odd' : 'even') . '">
        <td class="tl_file_list">' . $objOrders->config_name . '</td>
        <td class="tl_file_list">' . $objOrders->currency . '</td>
        <td class="tl_file_list">' . $objOrders->total_orders . '</td>
        <td class="tl_file_list">' . $objOrders->total_items . '</td>
        <td class="tl_file_list">' . Isotope::formatPrice($objOrders->total_sales) . '</td>
    </tr>';
            }

            $strBuffer .= '
    </table>
</div>';
        }


        $strBuffer .= '
</div>
</fieldset>';

        return $strBuffer;
    }
}
