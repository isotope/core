<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Report;

use Contao\Database;
use Contao\Session;
use Contao\StringUtil;
use Isotope\Isotope;
use Isotope\Model\ProductType;
use Isotope\Report\Period\PeriodFactory;
use Isotope\Report\Period\PeriodInterface;


class SalesProduct extends Sales
{

    public function generate()
    {
        $this->initializeDefaultValues();

        static::loadLanguageFile('tl_iso_product');
        static::loadDataContainer('tl_iso_product');

        return parent::generate();
    }


    protected function compile()
    {
        $arrSession = Session::getInstance()->get('iso_reports')
        ;

        $strPeriod = (string) $arrSession[$this->name]['period'];
        $intColumns = (int) $arrSession[$this->name]['columns'];
        $blnVariants = (bool) $arrSession[$this->name]['variants'];
        $intStatus = (int) $arrSession[$this->name]['iso_status'];

        if ($arrSession[$this->name]['from'] == '') {
            $intStart = strtotime('-'.($intColumns - 1).' '.$strPeriod);
        } else {
            $intStart = (int) $arrSession[$this->name]['from'];
        }

        $period = PeriodFactory::create($strPeriod);
        $intStart = $period->getPeriodStart($intStart);
        $dateFrom = $period->getKey($intStart);
        $dateTo = $period->getKey(strtotime('+ '.($intColumns - 1).' '.$strPeriod, $intStart));

        if ('locked' === $this->strDateField) {
            $this->strDateField = $arrSession[$this->name]['date_field'];
        }

        $arrData = array('rows' => array());
        $arrData['header'] = $this->getHeader($period, $intStart, $intColumns);

        $groupVariants = $blnVariants ? 'p1.id' : 'IF(p1.pid=0, p1.id, p1.pid)';
        $dateGroup = $period->getSqlField('o.'.$this->strDateField);

        $objProducts = Database::getInstance()->query("
            SELECT
                IFNULL($groupVariants, i.product_id) AS product_id,
                IFNULL(p1.name, MAX(i.name)) AS variant_name,
                IFNULL(p2.name, MAX(i.name)) AS product_name,
                p1.sku AS product_sku,
                p2.sku AS variant_sku,
                IF(p1.pid=0, p1.type, p2.type) AS type,
                MAX(i.configuration) AS product_configuration,
                SUM(i.quantity) AS quantity,
                SUM(i.tax_free_price * i.quantity) AS total,
                $dateGroup AS dateGroup
            FROM tl_iso_product_collection_item i
            LEFT JOIN tl_iso_product_collection o ON i.pid=o.id
            LEFT JOIN tl_iso_orderstatus os ON os.id=o.order_status
            LEFT OUTER JOIN tl_iso_product p1 ON i.product_id=p1.id
            LEFT OUTER JOIN tl_iso_product p2 ON p1.pid=p2.id
            WHERE o.type='order' AND o.order_status>0 AND o.{$this->strDateField} IS NOT NULL
                ".($intStatus > 0 ? " AND o.order_status=".$intStatus : '')."
                ".static::getProductProcedure('p1')."
                ".static::getConfigProcedure('o', 'config_id')."
            GROUP BY dateGroup, product_id
            HAVING dateGroup>=$dateFrom AND dateGroup<=$dateTo
        ")
        ;

        // Cache product types so call to findByPk() will trigger the registry
        ProductType::findMultipleByIds($objProducts->fetchEach('type'));

        $arrRaw = array();
        $product_type_name = null;
        $objProducts->reset();

        // Prepare product data
        while ($objProducts->next()) {
            $arrAttributes = array();
            $arrVariantAttributes = array();
            $blnHasVariants = false;

            // Can't use it without a type
            if ($objProducts->type > 0 && ($objType = ProductType::findByPk($objProducts->type)) !== null) {
                /** @type ProductType $objType */
                $arrAttributes = $objType->getAttributes();
                $arrVariantAttributes = $objType->getVariantAttributes();
                $blnHasVariants = $objType->hasVariants();
                $product_type_name = $objType->name;
            }

            $arrOptions = array('name' => $objProducts->variant_name);

            // Use product title if name is not a variant attribute
            if ($blnHasVariants && !\in_array('name', $arrVariantAttributes, true)) {
                $arrOptions['name'] = $objProducts->product_name;
            }

            $strSku = ($blnHasVariants ? $objProducts->variant_sku : $objProducts->product_sku);
            if (\in_array('sku', $arrAttributes, true) && $strSku != '') {
                $arrOptions['name'] = sprintf('%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>', $arrOptions['name'], $strSku);
            }

            if ($blnVariants && $blnHasVariants) {
                if (\in_array('sku', $arrVariantAttributes, true) && $objProducts->product_sku != '') {
                    $arrOptions['name'] = sprintf('%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>', $arrOptions['name'], $objProducts->product_sku);
                }

                foreach (StringUtil::deserialize($objProducts->product_configuration, true) as $strName => $strValue) {
                    if (isset($GLOBALS['TL_DCA']['tl_iso_product']['fields'][$strName])) {
                        $strValue = $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$strName]['options'][$strValue] ? $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$strName]['options'][$strValue] : $strValue;
                        $strName = $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$strName]['label'][0] ? $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$strName]['label'][0] : $strName;
                    }

                    $arrOptions[] = '<span class="variant">'.$strName.': '.$strValue.'</span>';
                }
            }

            $arrOptions['name'] = '<span class="product">'.$arrOptions['name'].'</span>';

            $arrRaw[$objProducts->product_id]['name'] = implode('<br>', $arrOptions);
            $arrRaw[$objProducts->product_id]['product_type_name'] = $product_type_name;
            $arrRaw[$objProducts->product_id][$objProducts->dateGroup] = (float) ($arrRaw[$objProducts->product_id][$objProducts->dateGroup] ?? 0) + (float) $objProducts->total;
            $arrRaw[$objProducts->product_id][$objProducts->dateGroup.'_quantity'] = (int) ($arrRaw[$objProducts->product_id][$objProducts->dateGroup.'_quantity'] ?? 0) + (int) $objProducts->quantity;
            $arrRaw[$objProducts->product_id]['total'] = (float) ($arrRaw[$objProducts->product_id]['total'] ?? 0) + (float) $objProducts->total;
            $arrRaw[$objProducts->product_id]['quantity'] = (int) ($arrRaw[$objProducts->product_id]['quantity'] ?? 0) + (int) $objProducts->quantity;
        }

        // Prepare columns
        $arrColumns = array();
        for ($i = 0; $i < $intColumns; $i++) {
            $arrColumns[] = $period->getKey($intStart);
            $intStart = $period->getNext($intStart);
        }

        $arrFooter = array();

        // Sort the data
        if ('product_name' === $arrSession[$this->name]['tl_sort']) {
            usort($arrRaw, function ($a, $b) {
                return strcasecmp($a['name'], $b['name']);
            });

        } else {
            usort($arrRaw, function ($a, $b) {
                return ($a['total'] == $b['total'] ? 0 : ($a['total'] < $b['total'] ? 1 : -1));
            });
        }

        // Generate data
        foreach ($arrRaw as $arrProduct) {
            $arrRow = array(
                array(
                    'value' => array(
                        $arrProduct['name'],
                        sprintf('<span style="color:#b3b3b3;">[%s]</span>', $arrProduct['product_type_name']),
                    ),
                ),
            );

            $arrFooter[0] = array(
                'value' => $GLOBALS['TL_LANG']['ISO_REPORT']['sums'],
            );

            foreach ($arrColumns as $i => $column) {
                $arrRow[$i + 1] = array(
                    'value' => Isotope::formatPriceWithCurrency($arrProduct[$column] ?? 0).(isset($arrProduct[$column.'_quantity']) ? '<br><span class="variant">'.Isotope::formatItemsString($arrProduct[$column.'_quantity']).'</span>' : ''),
                );

                $arrFooter[$i + 1] = array(
                    'total' => ($arrFooter[$i + 1]['total'] ?? 0) + ($arrProduct[$column] ?? 0),
                    'quantity' => ($arrFooter[$i + 1]['quantity'] ?? 0) + ($arrProduct[$column.'_quantity'] ?? 0),
                );
            }

            $arrRow[$i + 2] = array(
                'value' => Isotope::formatPriceWithCurrency($arrProduct['total']).(($arrProduct['quantity'] !== null) ? '<br><span class="variant">'.Isotope::formatItemsString($arrProduct['quantity']).'</span>' : ''),
            );

            $arrFooter[$i + 2] = array(
                'total' => ($arrFooter[$i + 2]['total'] ?? 0) + ($arrProduct['total'] ?? 0),
                'quantity' => ($arrFooter[$i + 2]['quantity'] ?? 0) + ($arrProduct['quantity'] ?? 0),
            );

            $arrData['rows'][] = array(
                'columns' => $arrRow,
            );
        }

        for ($i = 1, $iMax = \count($arrFooter); $i < $iMax; $i++) {
            $arrFooter[$i]['value'] = Isotope::formatPriceWithCurrency($arrFooter[$i]['total']).'<br><span class="variant">'.Isotope::formatItemsString($arrFooter[$i]['quantity']).'</span>';
            unset($arrFooter[$i]['total']);
        }

        $arrData['footer'] = $arrFooter;
        $this->Template->data = $arrData;
    }


    protected function getSelectVariantsPanel()
    {
        $arrSession = Session::getInstance()->get('iso_reports');

        return array(
            'name' => 'variants',
            'label' => &$GLOBALS['TL_LANG']['ISO_REPORT']['variants'],
            'type' => 'radio',
            'value' => (string) $arrSession[$this->name]['variants'],
            'class' => 'tl_variants',
            'options' => array(
                '1' => &$GLOBALS['TL_LANG']['MSC']['yes'],
                '' => &$GLOBALS['TL_LANG']['MSC']['no'],
            ),
            'attributes' => ' onchange="this.form.submit()"',
        );
    }


    protected function initializeDefaultValues()
    {
        $this->arrSearchOptions = array(
            'product_name' => &$GLOBALS['TL_LANG']['ISO_REPORT']['product_name'],
        );

        $this->arrSortingOptions = array(
            'product_name' => &$GLOBALS['TL_LANG']['ISO_REPORT']['product_name'],
            'total' => &$GLOBALS['TL_LANG']['ISO_REPORT']['total_sales'],
        );

        // Set default session data
        $arrSession = Session::getInstance()->get('iso_reports')
        ;

        if ($arrSession[$this->name]['tl_sort'] == '') {
            $arrSession[$this->name]['tl_sort'] = 'total';
        }

        Session::getInstance()->set('iso_reports', $arrSession)
        ;

        parent::initializeDefaultValues();
    }


    protected function getHeader(PeriodInterface $period, $intStart, $intColumns)
    {
        $arrHeader = array();
        $arrHeader[] = array('value' => &$GLOBALS['TL_LANG']['ISO_REPORT']['product_name']);

        for ($i = 0; $i < $intColumns; $i++) {
            $arrHeader[] = array(
                'value' => $period->format($intStart),
            );

            $intStart = $period->getNext($intStart);
        }

        $arrHeader[] = array(
            'value' => &$GLOBALS['TL_LANG']['ISO_REPORT']['sales_total'][0],
        );

        return $arrHeader;
    }
}
