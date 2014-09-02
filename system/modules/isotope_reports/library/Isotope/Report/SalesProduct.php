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

namespace Isotope\Report;

use Isotope\Isotope;
use Isotope\Model\OrderStatus;
use Isotope\Model\Product;
use Isotope\Model\ProductCollection;
use Isotope\Model\ProductCollectionItem;
use Isotope\Model\ProductType;


class SalesProduct extends Sales
{

    public function generate()
    {
        $this->initializeDefaultValues();

        $this->loadLanguageFile('tl_iso_product');
        $this->loadDataContainer('tl_iso_product');

        return parent::generate();
    }


    protected function compile()
    {
        $arrSession = \Session::getInstance()->get('iso_reports');
        $strPeriod = (string) $arrSession[$this->name]['period'];
        $intColumns = (int) $arrSession[$this->name]['columns'];
        $blnVariants = (bool) $arrSession[$this->name]['variants'];
        $intStatus = (int) $arrSession[$this->name]['iso_status'];

        if ($arrSession[$this->name]['from'] == '')
        {
            $intStart = strtotime('-' . ($intColumns-1) . ' ' . $strPeriod);
        }
        else
        {
            $intStart = (int) $arrSession[$this->name]['from'];
        }

        list($publicDate, $privateDate, $sqlDate) = $this->getPeriodConfiguration($strPeriod);

        $arrData = array('rows'=>array());
        $arrData['header'] = $this->getHeader($strPeriod, $publicDate, $intStart, $intColumns);

        $dateFrom = date($privateDate, $intStart);
        $dateTo = date($privateDate, strtotime('+ ' . ($intColumns-1) . ' ' . $strPeriod, $intStart));
        $groupVariants = $blnVariants ? 'p1.id' : 'IF(p1.pid=0, p1.id, p1.pid)';

        $objProducts = \Database::getInstance()->query("
            SELECT
                IFNULL($groupVariants, i.product_id) AS product_id,
                IFNULL(p1.name, i.name) AS variant_name,
                IFNULL(p2.name, i.name) AS product_name,
                p1.sku AS product_sku,
                p2.sku AS variant_sku,
                IF(p1.pid=0, p1.type, p2.type) AS type,
                i.options AS product_options,
                SUM(i.quantity) AS quantity,
                SUM(i.tax_free_price * i.quantity) AS total,
                DATE_FORMAT(FROM_UNIXTIME(o.{$this->strDateField}), '$sqlDate') AS dateGroup
            FROM " . ProductCollectionItem::getTable() . " i
            LEFT JOIN " . ProductCollection::getTable() . " o ON i.pid=o.id
            LEFT JOIN " . OrderStatus::getTable() . " os ON os.id=o.order_status
            LEFT OUTER JOIN " . Product::getTable() . " p1 ON i.product_id=p1.id
            LEFT OUTER JOIN " . Product::getTable() . " p2 ON p1.pid=p2.id
            WHERE o.type='order' AND o.order_status>0 AND o.locked!=''
                " . ($intStatus > 0 ? " AND o.order_status=".$intStatus : '') . "
                " . $this->getProductProcedure('p1') . "
                " . $this->getConfigProcedure('o', 'config_id') . "
            GROUP BY dateGroup, product_id
            HAVING dateGroup>=$dateFrom AND dateGroup<=$dateTo
        ");

        // Cache product types so call to findByPk() will trigger the registry
        ProductType::findMultipleByIds($objProducts->fetchEach('type'));

        $arrRaw = array();
        $objProducts->reset();

        // Prepare product data
        while ($objProducts->next())
        {
            $arrAttributes = array();
            $arrVariantAttributes = array();
            $blnHasVariants = false;

            // Can't use it without a type
            if ($objProducts->type > 0 && ($objType = ProductType::findByPk($objProducts->type)) !== null) {
                /** @type ProductType $objType */
                $arrAttributes = $objType->getAttributes();
                $arrVariantAttributes = $objType->getVariantAttributes();
                $blnHasVariants = $objType->hasVariants();
            }

            $arrOptions = array('name'=>$objProducts->variant_name);

            // Use product title if name is not a variant attribute
            if ($blnHasVariants && !in_array('name', $arrVariantAttributes)) {
                $arrOptions['name'] = $objProducts->product_name;
            }

            $strSku = ($blnHasVariants ? $objProducts->variant_sku : $objProducts->product_sku);
            if (in_array('sku', $arrAttributes) && $strSku != '') {
                $arrOptions['name'] = sprintf('%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>', $arrOptions['name'], $strSku);
            }

            if ($blnVariants && $blnHasVariants) {
                if (in_array('sku', $arrVariantAttributes) && $objProducts->product_sku != '') {
                    $arrOptions['name'] = sprintf('%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>', $arrOptions['name'], $objProducts->product_sku);
                }

                foreach (deserialize($objProducts->product_options, true) as $strName => $strValue) {
                    if (isset($GLOBALS['TL_DCA']['tl_iso_product']['fields'][$strName])) {
                        $strValue = $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$strName]['options'][$strValue] ? $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$strName]['options'][$strValue] : $strValue;
                        $strName = $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$strName]['label'][0] ? $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$strName]['label'][0] : $strName;
                    }

                    $arrOptions[] = '<span class="variant">' . $strName . ': ' . $strValue . '</span>';
                }
            }

            $arrOptions['name'] = '<span class="product">' . $arrOptions['name'] . '</span>';

            $arrRaw[$objProducts->product_id]['name'] = implode('<br>', $arrOptions);
            $arrRaw[$objProducts->product_id][$objProducts->dateGroup] = (float) $arrRaw[$objProducts->product_id][$objProducts->dateGroup] + (float) $objProducts->total;
            $arrRaw[$objProducts->product_id][$objProducts->dateGroup.'_quantity'] = (int) $arrRaw[$objProducts->product_id][$objProducts->dateGroup.'_quantity'] + (int) $objProducts->quantity;
            $arrRaw[$objProducts->product_id]['total'] = (float) $arrRaw[$objProducts->product_id]['total'] + (float) $objProducts->total;
            $arrRaw[$objProducts->product_id]['quantity'] = (int) $arrRaw[$objProducts->product_id]['quantity'] + (int) $objProducts->quantity;
        }

        // Prepare columns
        $arrColumns = array();
        for ($i=0; $i<$intColumns; $i++) {
            $arrColumns[] = date($privateDate, $intStart);
            $intStart = strtotime('+1 ' . $strPeriod, $intStart);
        }

        $arrFooter = array();

        // Sort the data
        if ($arrSession[$this->name]['tl_sort'] == 'product_name') {

            usort($arrRaw, function ($a, $b) {
                return strcasecmp($a['name'], $b['name']);
            });

        } else {

            usort($arrRaw, function ($a, $b) {
                return ($a['total'] == $b['total'] ? 0 : ($a['total'] < $b['total'] ? 1 : -1));
            });
        }

        // Generate data
        foreach ($arrRaw as $arrProduct)
        {
            $arrRow = array(array
            (
                'value'      => $arrProduct['name'],
            ));

            $arrFooter[0] = array
            (
                'value'      => $GLOBALS['TL_LANG']['ISO_REPORT']['sums'],
            );

            foreach ($arrColumns as $i=>$column)
            {
                $arrRow[$i+1] = array
                (
                    'value'         => Isotope::formatPriceWithCurrency($arrProduct[$column]) . (($arrProduct[$column.'_quantity'] !== null) ? '<br><span class="variant">' . Isotope::formatItemsString($arrProduct[$column.'_quantity']) . '</span>' : ''),
                );

                $arrFooter[$i+1] = array
                (
                    'total'         => $arrFooter[$i+1]['total'] + $arrProduct[$column],
                    'quantity'      => $arrFooter[$i+1]['quantity'] + $arrProduct[$column.'_quantity'],
                );
            }

            $arrRow[$i+2] = array
            (
                'value'         => Isotope::formatPriceWithCurrency($arrProduct['total']) . (($arrProduct['quantity'] !== null) ? '<br><span class="variant">' . Isotope::formatItemsString($arrProduct['quantity']) . '</span>' : ''),
            );

            $arrFooter[$i+2] = array
            (
                'total'         => $arrFooter[$i+2]['total'] + $arrProduct['total'],
                'quantity'      => $arrFooter[$i+2]['quantity'] + $arrProduct['quantity'],
            );

            $arrData['rows'][] = array
            (
                'columns' => $arrRow,
            );
        }

        for ($i=1; $i<count($arrFooter); $i++)
        {
            $arrFooter[$i]['value'] = Isotope::formatPriceWithCurrency($arrFooter[$i]['total']) . '<br><span class="variant">' . Isotope::formatItemsString($arrFooter[$i]['quantity']) . '</span>';
            unset($arrFooter[$i]['total'], $arrFooter[$i]['quantity']);
        }

        $arrData['footer'] = $arrFooter;
        $this->Template->data = $arrData;
    }


    protected function getSelectVariantsPanel()
    {
        $arrSession = \Session::getInstance()->get('iso_reports');

        return array (
            'name'          => 'variants',
            'label'         => &$GLOBALS['TL_LANG']['ISO_REPORT']['variants'],
            'type'          => 'radio',
            'value'         => (string) $arrSession[$this->name]['variants'],
            'class'         => 'tl_variants',
            'options'       => array (
                '1'         => &$GLOBALS['TL_LANG']['MSC']['yes'],
                ''          => &$GLOBALS['TL_LANG']['MSC']['no'],
            ),
            'attributes'    => ' onchange="this.form.submit()"'
        );
    }


    protected function initializeDefaultValues()
    {
        $this->arrSearchOptions = array
        (
            'product_name' => &$GLOBALS['TL_LANG']['ISO_REPORT']['product_name'],
        );

        $this->arrSortingOptions = array
        (
            'product_name' => &$GLOBALS['TL_LANG']['ISO_REPORT']['product_name'],
            'total' => &$GLOBALS['TL_LANG']['ISO_REPORT']['total_sales'],
        );

        // Set default session data
        $arrSession = \Session::getInstance()->get('iso_reports');

        if ($arrSession[$this->name]['tl_sort'] == '')
        {
            $arrSession[$this->name]['tl_sort'] = 'total';
        }

        \Session::getInstance()->set('iso_reports', $arrSession);

        parent::initializeDefaultValues();
    }


    protected function getHeader($strPeriod, $strFormat, $intStart, $intColumns)
    {
        $arrHeader = array();
        $arrHeader[] = array('value'=>'Produkt');

        for ($i=0; $i<$intColumns; $i++)
        {
            $arrHeader[] = array
            (
                'value' => \Date::parse($strFormat, $intStart),
            );

            $intStart = strtotime('+ 1 ' . $strPeriod, $intStart);
        }

        $arrHeader[] = array
        (
            'value' => 'Total',
        );

        return $arrHeader;
    }
}

