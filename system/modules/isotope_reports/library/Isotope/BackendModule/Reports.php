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
use Contao\Session;
use Contao\StringUtil;
use Isotope\Isotope;
use Isotope\Model\Config;
use Isotope\Report\Report;

class Reports extends BackendOverview
{
    protected function compile()
    {
        $summary = [];
        $periods = [
            '-24 hours' => $GLOBALS['TL_LANG']['ISO_REPORT']['24h_summary'],
            date('Y-m-01') => $GLOBALS['TL_LANG']['ISO_REPORT']['month_summary'],
            date('Y-01-01') => $GLOBALS['TL_LANG']['ISO_REPORT']['year_summary'],
        ];

        foreach ($periods as $time => $label) {
            foreach ($this->getSummaryFor(strtotime($time)) as $configId => $data) {
                $summary[$configId][$label] = $data;
            }
        }

        $this->Template->before = $this->getSummary($summary);

        parent::compile();
    }

    protected function getModules()
    {
        $arrReturn = array();
        $arrGroups = &$GLOBALS['BE_MOD']['isotope']['reports']['modules'];

        foreach ($arrGroups as $strGroup => $arrModules) {
            foreach ($arrModules as $strModule => $arrConfig) {
                if ($this->checkUserAccess($strModule)) {
                    $arrReturn[$strGroup]['modules'][$strModule] = array_merge($arrConfig, array
                    (
                        'name' => $strModule,
                        'label' => StringUtil::specialchars(($arrConfig['label'][0] ?: $strModule)),
                        'description' => StringUtil::specialchars(strip_tags($arrConfig['label'][1])),
                        'href' => $this->addToUrl('mod='.$strModule),
                        'class' => $arrConfig['class'] ?? '',
                    ));

                    // @deprecated remove ISO_LANG in Isotope 3.0
                    $arrReturn[$strGroup]['label'] = $strLegend = ($GLOBALS['TL_LANG']['ISO_REPORT'][$strGroup] ?: ($GLOBALS['ISO_LANG']['REPORT'][$strGroup] ?: $strGroup));
                }
            }
        }

        return $arrReturn;
    }

    protected function checkUserAccess($module)
    {
        return BackendUser::getInstance()->isAdmin || BackendUser::getInstance()->hasAccess($module, 'iso_reports');
    }

    /**
     * Generate a summary for config
     */
    private function getSummary(array $data): string
    {
        $session = Session::getInstance()->get('fieldset_states');

        $strBuffer = '
<fieldset id="pal_summary" class="tl_tbox '.(($session['iso_be_overview_legend']['summary'] ?? null) ? '' : ' collapsed').'">
<legend onclick="AjaxRequest.toggleFieldset(this,\'summary\',\'iso_be_overview_legend\')">'.$GLOBALS['TL_LANG']['ISO_REPORT']['summary'].'</b></legend>
<div class="summary">
<div class="tl_listing_container list_view">
    <table class="tl_listing">';

        $hasMultipleConfigs = \count($data) > 0;
        foreach ($data as $configId => $configData) {
            $config = Config::findBy('id', $configId);

            if ($hasMultipleConfigs) {
                $strBuffer .= '
    <tr>
        <th colspan="6" style="padding:5px 0">'.$GLOBALS['TL_LANG']['ISO_REPORT']['shop_config'].': '.$config->name.'</th>
    </tr>';
            }

            $strBuffer .= '
    <tr>
        <th class="tl_folder_tlist">'.$GLOBALS['TL_LANG']['ISO_REPORT']['sales_headline'].'</th>
        <th class="tl_folder_tlist" style="text-align:right">'.$GLOBALS['TL_LANG']['ISO_REPORT']['orders#'].'</th>
        <th class="tl_folder_tlist" style="text-align:right">'.$GLOBALS['TL_LANG']['ISO_REPORT']['products#'].'</th>
        <th class="tl_folder_tlist" style="text-align:right">'.$GLOBALS['TL_LANG']['ISO_REPORT']['sales#'].'</th>
        <th class="tl_folder_tlist" style="text-align:right">'.$GLOBALS['TL_LANG']['ISO_REPORT']['discounts#'].'</th>
        <th class="tl_folder_tlist" style="text-align:right">'.$GLOBALS['TL_LANG']['ISO_REPORT']['sales_avg'].'</th>
    </tr>
            ';

            $i = -1;
            foreach ($configData as $timeRange => $timeRangeData) {
                $strBuffer .= '
    <tr class="row_'.++$i.($i % 2 ? ' odd' : ' even').'">
        <td class="tl_file_list">'.$timeRange.'</td>
        <td class="tl_file_list" style="text-align:right">'.$timeRangeData['total_orders'].'</td>
        <td class="tl_file_list" style="text-align:right">'.$timeRangeData['total_items'].'</td>
        <td class="tl_file_list" style="text-align:right">'.Isotope::formatPriceWithCurrency($timeRangeData['total_sales'], true, null, true, $config).'</td>
        <td class="tl_file_list" style="text-align:right">'.Isotope::formatPriceWithCurrency($timeRangeData['total_discounts'], true, null, true, $config).'</td>
        <td class="tl_file_list" style="text-align:right">'.Isotope::formatPriceWithCurrency($timeRangeData['average_sales'], true, null, true, $config).'</td>
    </tr>';
            }

            if ($hasMultipleConfigs) {
                $strBuffer .= '<tr><th colspan="6">&nbsp;</th></tr>';
            }
        }

        $strBuffer .= '
    </table>
</div>
</div>
</fieldset>';

        return $strBuffer;
    }

    /**
     * Generate a summary for time range
     */
    private function getSummaryFor(int $locked): array
    {
        $objOrders = Database::getInstance()->prepare("
            SELECT
                c.id AS config_id,
                IFNULL(sub.total_orders,0) AS total_orders,
                IFNULL(sub.total_items,0) AS total_items,
                IFNULL(sub.total_sales+sub.total_discounts,0) AS total_sales,
                IFNULL((sub.total_sales+sub.total_discounts)/sub.total_orders,0) AS average_sales,
                IFNULL(sub.total_discounts,0) AS total_discounts
            FROM tl_iso_config c
            LEFT JOIN (SELECT
                            o.config_id,
                            COUNT(DISTINCT o.id) AS total_orders,
                            SUM(o.tax_free_subtotal) AS total_sales,
                            SUM(i.quantity) AS total_items,
                            IFNULL(SUM(discounts.total_price),0) AS total_discounts
                       FROM tl_iso_product_collection o
                       INNER JOIN tl_iso_orderstatus os ON o.order_status = os.id
                       LEFT JOIN tl_iso_product_collection_item i ON o.id=i.pid
                       LEFT JOIN (SELECT
                            pid,
                            total_price
                            FROM tl_iso_product_collection_surcharge
                            WHERE type = 'rule') AS discounts ON o.id=discounts.pid
                       WHERE o.type='order' AND os.paid = 1 AND o.locked>=?
                       GROUP BY o.config_id
                ".Report::getProductProcedure('i', 'product_id')."
                ".Report::getConfigProcedure('o', 'config_id')."
            ) AS sub ON sub.config_id=c.id
        ")->execute($locked);

        $data = [];

        while ($objOrders->next()) {
            $data[$objOrders->config_id] = [
                'total_orders' => $objOrders->total_orders,
                'total_sales' => $objOrders->total_sales,
                'total_items' => $objOrders->total_items,
                'total_discounts' => abs($objOrders->total_discounts),
                'average_sales' => $objOrders->average_sales,
            ];
        }

        return $data;
    }
}
