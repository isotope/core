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
use Isotope\Model\Config;
use Isotope\Report\Report;


class Reports extends BackendOverview
{

    protected function compile()
    {
        $last24h = $this->getSummaryFor('-24 hours', $GLOBALS['TL_LANG']['ISO_REPORT']['24h_summary']);
        $currentMonth = $this->getSummaryFor(date('Y-m-01'), $GLOBALS['TL_LANG']['ISO_REPORT']['month_summary']);
        $currentYear = $this->getSummaryFor(date('Y-01-01'), $GLOBALS['TL_LANG']['ISO_REPORT']['year_summary']);
        $allSummaries = array_merge_recursive($last24h, $currentMonth, $currentYear);

        $this->Template->before = $this->getSummary($GLOBALS['TL_LANG']['ISO_REPORT']['shop_config'], $allSummaries);

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
     * Generate a summary for config
     * @return array
     */
    protected function getSummary($text, $data)
    {
        foreach ($data as $config_name => $config_data) {
            $config = Config::findBy('name',$config_name);
        $strBuffer .= '
<fieldset class="tl_tbox">
<legend style="cursor: default;">' . $text . ': <b>'. $config_name . '</b></legend>
<div class="summary">';
            $i = -1;
            $strBuffer .= '
<div class="tl_listing_container list_view">
    <table class="tl_listing">
    <tr>
        <th class="tl_folder_tlist">' . $GLOBALS['TL_LANG']['ISO_REPORT']['sales_headline'] . '</th>
        <th class="tl_folder_tlist" style="text-align:right">' . $GLOBALS['TL_LANG']['ISO_REPORT']['orders#'] . '</th>
        <th class="tl_folder_tlist" style="text-align:right">' . $GLOBALS['TL_LANG']['ISO_REPORT']['products#'] . '</th>
        <th class="tl_folder_tlist" style="text-align:right">' . $GLOBALS['TL_LANG']['ISO_REPORT']['sales#'] . '</th>
        <th class="tl_folder_tlist" style="text-align:right">' . $GLOBALS['TL_LANG']['ISO_REPORT']['sales_avg'] . '</th>
    </tr>';
            foreach ($config_data as $time_range => $time_range_data) {
                $strBuffer .= '
    <tr class="row_' . ++$i . ($i % 2 ? ' odd' : ' even') . '">
        <td class="tl_file_list">' . $time_range . '</td>
        <td class="tl_file_list" style="text-align:right">' . $time_range_data['total_orders'] . '</td>
        <td class="tl_file_list" style="text-align:right">' . $time_range_data['total_items'] . '</td>
        <td class="tl_file_list" style="text-align:right">' . Isotope::formatPriceWithCurrencyForConfig($time_range_data['total_sales'], $config) . '</td>
        <td class="tl_file_list" style="text-align:right">' . Isotope::formatPriceWithCurrencyForConfig($time_range_data['average_sales'], $config) . '</td>
    </tr>';
            }
            $strBuffer .= '
    </table>
</div>
</div>
</fieldset>';
        }
        return $strBuffer;
    }

    /**
     * Generate a summary for time range
     * @return array
     */
    protected function getSummaryFor($timeRange, $timeRangeLabel)
    {
        $objOrders = Database::getInstance()->prepare("
            SELECT
                c.name AS config_name,
                IFNULL(sub.total_orders,0) AS total_orders,
                IFNULL(sub.total_sales,0) AS total_sales,
                IFNULL(sub.total_items,0) AS total_items,
                IFNULL(sub.average_sales,0) AS average_sales
            FROM tl_iso_config c

            LEFT JOIN (SELECT
                            o.config_id,
                       		COUNT(DISTINCT o.id) AS total_orders,
                			SUM(i.tax_free_price * i.quantity) AS total_sales,
                			SUM(i.quantity) AS total_items,
                            SUM(i.tax_free_price * i.quantity)/COUNT(DISTINCT o.id) AS average_sales
                       FROM tl_iso_product_collection o
            		   LEFT JOIN tl_iso_product_collection_item i ON o.id=i.pid
                       WHERE o.type='order' AND o.order_status>0 AND o.locked>=?
                       GROUP BY o.config_id
                " . Report::getProductProcedure('i', 'product_id') . "
                " . Report::getConfigProcedure('o', 'config_id') . "
            ) AS sub ON sub.config_id=c.id
        ")->execute(strtotime($timeRange));

        $data = array();
        while ($objOrders->next()) {
            $data[$objOrders->config_name][$timeRangeLabel] = [
                'total_orders' => $objOrders->total_orders,
                'total_sales' => $objOrders->total_sales,
                'total_items' => $objOrders->total_items,
                'average_sales' => $objOrders->average_sales,
            ];
        }
        return $data;
    }
}
