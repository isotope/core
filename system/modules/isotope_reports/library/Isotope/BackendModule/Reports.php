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
                        'label'         => specialchars(($arrConfig['label'][0] ?: $strModule)),
                        'description'   => specialchars(strip_tags($arrConfig['label'][1])),
                        'href'          => $this->addToUrl('mod=' . $strModule),
                        'class'         => $arrConfig['class'],
                    ));

                    $arrReturn[$strGroup]['label'] = $strLegend = $GLOBALS['ISO_LANG']['REPORT'][$strGroup] ?: $strGroup;;
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
        return \BackendUser::getInstance()->isAdmin || \BackendUser::getInstance()->hasAccess($module, 'iso_reports');
    }


    /**
     * Generate a daily summary for the overview page
     * @return array
     */
    protected function getDailySummary()
    {
        $strBuffer = '
<div class="tl_formbody_edit be_iso_overview">
<fieldset class="tl_tbox">
<legend style="cursor: default;">' . $GLOBALS['ISO_LANG']['REPORT']['24h_summary'] . '</legend>';

        $arrAllowedProducts = \Isotope\Backend\Product\Permission::getAllowedIds();

        $objOrders = \Database::getInstance()->prepare("SELECT
                                                    c.id AS config_id,
                                                    c.name AS config_name,
                                                    c.currency,
                                                    COUNT(o.id) AS total_orders,
                                                    SUM(i.tax_free_price * i.quantity) AS total_sales,
                                                    SUM(i.quantity) AS total_items
                                                FROM " . \Isotope\Model\ProductCollection::getTable() . " o
                                                LEFT JOIN " . \Isotope\Model\ProductCollectionItem::getTable() . " i ON o.id=i.pid
                                                LEFT OUTER JOIN " . \Isotope\Model\Config::getTable() . " c ON o.config_id=c.id
                                                WHERE o.type='order' AND o.order_status>0 AND o.locked>=?
                                                " . ($arrAllowedProducts === true ? '' : (" AND i.product_id IN (" . (empty($arrAllowedProducts) ? '0' : implode(',', $arrAllowedProducts)) . ")")) . "
                                                GROUP BY config_id")
                                    ->execute(strtotime('-24 hours'));

        if (!$objOrders->numRows) {

            $strBuffer .= '
<p class="tl_info" style="margin-top:10px">' . $GLOBALS['ISO_LANG']['REPORT']['24h_empty'] . '</p>';

        } else {

            $i = -1;
            $strBuffer .= '
<br>
<table class="tl_listing">
<tr>
    <th class="tl_folder_tlist">' . $GLOBALS['ISO_LANG']['REPORT']['shop_config'] . '</th>
    <th class="tl_folder_tlist">' . $GLOBALS['ISO_LANG']['REPORT']['currency'] . '</th>
    <th class="tl_folder_tlist">' . $GLOBALS['ISO_LANG']['REPORT']['orders#'] . '</th>
    <th class="tl_folder_tlist">' . $GLOBALS['ISO_LANG']['REPORT']['products#'] . '</th>
    <th class="tl_folder_tlist">' . $GLOBALS['ISO_LANG']['REPORT']['sales#'] . '</th>
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
</table>';
        }


        $strBuffer .= '
</fieldset>
</div>';

        return $strBuffer;
    }
}

