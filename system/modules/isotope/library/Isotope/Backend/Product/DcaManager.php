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

namespace Isotope\Backend\Product;

use Haste\Util\Format;
use Isotope\Interfaces\IsotopeAttributeForVariants;
use Isotope\Model\Attribute;
use Isotope\Model\Group;
use Isotope\Model\Product;
use Isotope\Model\ProductType;
use Isotope\Model\RelatedCategory;


class DcaManager extends \Backend
{

    /**
     * Initialize the tl_iso_product DCA
     * @return void
     */
    public function initialize($strTable)
    {
        if ($strTable != Product::getTable() || !\Database::getInstance()->tableExists(Attribute::getTable())) {
            return;
        }

        $this->addAttributes();
    }

    /**
     * Load DCA configuration (onload_callback)
     */
    public function load()
    {
        $this->checkFeatures();
        $this->addBreadcrumb();
        $this->buildPaletteString();
        $this->changeVariantColumns();
    }

    /**
     * Store initial values when creating a product
     * @param   string
     * @param   int
     * @param   array
     * @param   DataContainer
     */
    public function updateNewRecord($strTable, $insertID, $arrSet, $dc)
    {
        if ($arrSet['pid'] > 0) {
            return;
        }

        $intType  = 0;
        $intGroup = (int) \Session::getInstance()->get('iso_products_gid') ? : (\BackendUser::getInstance()->isAdmin ? 0 : intval(\BackendUser::getInstance()->iso_groups[0]));
        $objGroup = Group::findByPk($intGroup);

        if (null === $objGroup || null === $objGroup->getRelated('product_type')) {
            $objType = ProductType::findFallback();
        } else {
            $objType = $objGroup->getRelated('product_type');
        }

        if (null !== $objType) {
            $intType = $objType->id;
        }

        \Database::getInstance()->prepare("UPDATE $strTable SET gid=?, type=?, dateAdded=? WHERE id=?")->execute($intGroup, $intType, time(), $insertID);
    }

    /**
     * Update dateAdded on copy
     * @param integer
     * @param object
     * @link http://www.contao.org/callbacks.html#oncopy_callback
     */
    public function updateDateAdded($insertId, $dc)
    {
        $strTable = Product::getTable();
        \Database::getInstance()->prepare("UPDATE $strTable SET dateAdded=? WHERE id=?")->execute(time(), $insertId);
    }

    /**
     * Add custom attributes to tl_iso_product DCA
     */
    protected function addAttributes()
    {
        $arrData               = &$GLOBALS['TL_DCA'][Product::getTable()];
        $arrData['attributes'] = array();

        // Write attributes from database to DCA
        /** @var \Isotope\Model\Attribute[] $objAttributes */
        if (($objAttributes = Attribute::findValid()) !== null) {
            foreach ($objAttributes as $objAttribute) {

                if (null !== $objAttribute) {
                    $objAttribute->saveToDCA($arrData);
                    $arrData['attributes'][$objAttribute->field_name] = $objAttribute;
                }
            }
        }

        // Create temporary models for non-database attributes
        foreach (array_diff_key($arrData['fields'], $arrData['attributes']) as $strName => $arrConfig) {

            if (is_array($arrConfig['attributes'])) {
                if ($arrConfig['attributes']['type'] != '') {
                    $strClass = $arrConfig['attributes']['type'];
                } else {
                    $strClass = Attribute::getClassForModelType($arrConfig['inputType']);
                }

                if ($strClass != '') {
                    $objAttribute = new $strClass();
                    $objAttribute->loadFromDCA($arrData, $strName);
                    $arrData['attributes'][$strName] = $objAttribute;
                }
            }
        }
    }

    /**
     * Disable features that are not used in the current installation
     */
    protected function checkFeatures()
    {
        $blnDownloads      = false;
        $blnVariants       = false;
        $blnAdvancedPrices = false;
        $blnShowSku        = false;
        $blnShowPrice      = false;
        $arrAttributes     = array();

        /** @var \Isotope\Model\ProductType[] $objProductTypes */
        if (($objProductTypes = ProductType::findAllUsed()) !== null) {
            foreach ($objProductTypes as $objType) {

                if ($objType->hasDownloads()) {
                    $blnDownloads = true;
                }

                if ($objType->hasVariants()) {
                    $blnVariants = true;
                }

                if ($objType->hasAdvancedPrices()) {
                    $blnAdvancedPrices = true;
                }

                if (in_array('sku', $objType->getAttributes())) {
                    $blnShowSku = true;
                }

                if (in_array('price', $objType->getAttributes())) {
                    $blnShowPrice = true;
                }

                $arrAttributes = array_merge($arrAttributes, $objType->getAttributes());
            }
        }

        // If no downloads are enabled in any product type, we do not need the option
        if (!$blnDownloads) {
            unset($GLOBALS['TL_DCA'][Product::getTable()]['list']['operations']['downloads']);
        }

        // Disable all variant related operations
        if (!$blnVariants) {
            unset($GLOBALS['TL_DCA'][Product::getTable()]['list']['global_operations']['toggleVariants']);
            unset($GLOBALS['TL_DCA'][Product::getTable()]['list']['operations']['generate']);
        }

        // Disable prices button if not enabled in any product type
        if (!$blnAdvancedPrices) {
            unset($GLOBALS['TL_DCA'][Product::getTable()]['list']['operations']['prices']);
        }

        // Hide SKU column if not enabled in any product type
        if (!$blnShowSku) {
            unset($GLOBALS['TL_DCA'][Product::getTable()]['list']['label']['fields'][2]);
        }

        // Hide price column if not enabled in any product type
        if (!$blnShowPrice) {
            unset($GLOBALS['TL_DCA'][Product::getTable()]['list']['label']['fields'][3]);
        }

        // Disable sort-into-group if no groups are defined
        if (Group::countAll() == 0) {
            unset($GLOBALS['TL_DCA'][Product::getTable()]['list']['operations']['group']);
        }

        // Disable related categories if none are defined
        if (RelatedCategory::countAll() == 0) {
            unset($GLOBALS['TL_DCA'][Product::getTable()]['list']['operations']['related']);
        }

        foreach (array_diff(array_keys($GLOBALS['TL_DCA'][Product::getTable()]['fields']), array_unique($arrAttributes)) as $field) {
            $GLOBALS['TL_DCA'][Product::getTable()]['fields'][$field]['filter'] = false;
            $GLOBALS['TL_DCA'][Product::getTable()]['fields'][$field]['sorting'] = false;
            $GLOBALS['TL_DCA'][Product::getTable()]['fields'][$field]['search'] = false;
        }
    }

    /**
     * Add the breadcrumb menu
     */
    public function addBreadcrumb()
    {
        $strBreadcrumb = \Isotope\Backend\Group\Breadcrumb::generate($this->Session->get('iso_products_gid'));
        $strBreadcrumb .= static::getPagesBreadcrumb();

        $GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['breadcrumb'] = $strBreadcrumb;
    }

    /**
     * Build palette for the current product type/variant
     */
    public function buildPaletteString()
    {
        $this->loadDataContainer(Attribute::getTable());

        if (\Input::get('act') == '' && \Input::get('key') == '' || \Input::get('act') == 'select') {
            return;
        }

        $arrTypes      = array();
        $arrFields     = &$GLOBALS['TL_DCA']['tl_iso_product']['fields'];
        $arrAttributes = &$GLOBALS['TL_DCA']['tl_iso_product']['attributes'];

        $blnVariants     = false;
        $act             = \Input::get('act');
        $blnSingleRecord = $act === 'edit' || $act === 'show';

        if (\Input::get('id') > 0) {
            $objProduct = \Database::getInstance()->prepare("SELECT p1.pid, p1.type, p2.type AS parent_type FROM tl_iso_product p1 LEFT JOIN tl_iso_product p2 ON p1.pid=p2.id WHERE p1.id=?")->execute(\Input::get('id'));

            if ($objProduct->numRows) {
                $objType  = ProductType::findByPk(($objProduct->pid > 0 ? $objProduct->parent_type : $objProduct->type));
                $arrTypes = null === $objType ? array() : array($objType);

                if ($objProduct->pid > 0 || ($act != 'edit' && $act != 'copyFallback' && $act != 'show')) {
                    $blnVariants = true;
                }
            }
        } else {
            $arrTypes = ProductType::findAllUsed() ? : array();
        }

        /** @var \Isotope\Model\ProductType $objType */
        foreach ($arrTypes as $objType) {

            // Enable advanced prices
            if ($blnSingleRecord && $objType->hasAdvancedPrices()) {
                $arrFields['prices']['exclude']    = $arrFields['price']['exclude'];
                $arrFields['prices']['attributes'] = $arrFields['price']['attributes'];
                $arrFields['price']                = $arrFields['prices'];
            }

            // Register callback to version/restore a price
            else {
                $GLOBALS['TL_DCA']['tl_iso_product']['config']['onversion_callback'][] = array('Isotope\Backend\Product\Price', 'createVersion');
                $GLOBALS['TL_DCA']['tl_iso_product']['config']['onrestore_callback'][] = array('Isotope\Backend\Product\Price', 'restoreVersion');
            }

            $arrInherit = array();
            $arrPalette = array();
            $arrLegends = array();
            $arrLegendOrder = array();

            if ($blnVariants) {
                $arrConfig     = deserialize($objType->variant_attributes, true);
                $arrEnabled    = $objType->getVariantAttributes();
                $arrCanInherit = $objType->getAttributes();
            } else {
                $arrConfig  = deserialize($objType->attributes, true);
                $arrEnabled = $objType->getAttributes();
            }

            // Go through each enabled field and build palette
            foreach ($arrFields as $name => $arrField) {
                if (in_array($name, $arrEnabled)) {

                    // Do not show customer defined fields
                    if (null !== $arrAttributes[$name] && $arrAttributes[$name]->isCustomerDefined()) {
                        continue;
                    }

                    // Variant fields can only be edited in variant mode
                    if (null !== $arrAttributes[$name]
                        && !$blnVariants
                        && /* @todo in 3.0: $arrAttributes[$name] instanceof IsotopeAttributeForVariants
                        && */$arrAttributes[$name]->isVariantOption()
                    ) {
                        continue;
                    }

                    // Field cannot be edited in variant
                    if ($blnVariants && $arrAttributes[$name]->inherit) {
                        continue;
                    }

                    $arrLegendOrder[$arrConfig[$name]['position']] = $arrConfig[$name]['legend'];
                    $arrPalette[$arrConfig[$name]['legend']][$arrConfig[$name]['position']] = $name;

                    // Apply product type attribute config
                    if ($arrConfig[$name]['tl_class'] != '') {
                        $arrFields[$name]['eval']['tl_class'] = $arrConfig[$name]['tl_class'];
                    }

                    if ($arrConfig[$name]['mandatory'] > 0) {
                        $arrFields[$name]['eval']['mandatory'] = $arrConfig[$name]['mandatory'] == 1 ? false : true;
                    }

                    if ($blnVariants
                        && in_array($name, $arrCanInherit)
                        && null !== $arrAttributes[$name]
                        && /* @todo in 3.0: $arrAttributes[$name] instanceof IsotopeAttributeForVariants
                        && */!$arrAttributes[$name]->isVariantOption()
                        && !in_array($name, array('price', 'published', 'start', 'stop'))
                    ) {
                        $arrInherit[$name] = Format::dcaLabel('tl_iso_product', $name);
                    }

                } else {

                    // Hide field from "show" option
                    if (!isset($arrField['attributes']) || $arrField['inputType'] != '' && $name != 'inherit') {
                        $arrFields[$name]['eval']['doNotShow'] = true;
                    }
                }
            }

            ksort($arrLegendOrder);
            $arrLegendOrder = array_unique($arrLegendOrder);

            // Build
            foreach ($arrLegendOrder as $legend) {
                $fields = $arrPalette[$legend];
                ksort($fields);
                $arrLegends[] = '{' . $legend . '},' . implode(',', $fields);
            }

            // Set inherit options
            $arrFields['inherit']['options'] = $arrInherit;

            // Add palettes
            $GLOBALS['TL_DCA']['tl_iso_product']['palettes'][($blnVariants ? 'default' : $objType->id)] = ($blnVariants ? 'inherit,' : '') . implode(';', $arrLegends);
        }

        // Remove non-active fields from multi-selection
        if ($blnVariants && !$blnSingleRecord) {
            $arrInclude = empty($arrPalette) ? array() : call_user_func_array('array_merge', $arrPalette);

            foreach ($arrFields as $name => $config) {
                if ($arrFields[$name]['attributes']['legend'] != '' && !in_array($name, $arrInclude)) {
                    $arrFields[$name]['exclude'] = true;
                }
            }
        }
    }

    /**
     * Change the displayed columns in the variants view
     */
    public function changeVariantColumns()
    {
        if ((\Input::get('act') != '' && \Input::get('act') != 'select') || \Input::get('id') == '' || ($objProduct = Product::findByPk(\Input::get('id'))) === null) {
            return;
        }


        $GLOBALS['TL_DCA'][$objProduct->getTable()]['list']['sorting']['mode']      = 4;
        $GLOBALS['TL_DCA'][$objProduct->getTable()]['list']['sorting']['fields']    = array('id');
        $GLOBALS['TL_DCA'][$objProduct->getTable()]['fields']['alias']['sorting']   = false;

        $arrFields         = array();
        $arrVariantFields  = $objProduct->getRelated('type')->getVariantAttributes();
        $arrVariantOptions = array_intersect($arrVariantFields, Attribute::getVariantOptionFields());

        if (in_array('images', $arrVariantFields)) {
            $arrFields[] = 'images';
        }

        if (in_array('name', $arrVariantFields)) {
            $arrFields[]                                                             = 'name';
            $GLOBALS['TL_DCA'][$objProduct->getTable()]['list']['sorting']['fields'] = array('name');
        }

        if (in_array('sku', $arrVariantFields)) {
            $arrFields[]                                                             = 'sku';
            $GLOBALS['TL_DCA'][$objProduct->getTable()]['list']['sorting']['fields'] = array('sku');
        }

        if (in_array('price', $arrVariantFields)) {
            $arrFields[] = 'price';
        }

        // Limit the number of columns if there are more than 2
        if (count($arrVariantOptions) > 2) {
            $arrFields[]                                                                  = 'variantFields';
            $GLOBALS['TL_DCA'][$objProduct->getTable()]['list']['label']['variantFields'] = $arrVariantOptions;
        } else {
            $arrFields = array_merge($arrFields, $arrVariantOptions);
        }

        $GLOBALS['TL_DCA'][$objProduct->getTable()]['list']['label']['fields'] = $arrFields;

        // Make all column fields sortable
        foreach ($GLOBALS['TL_DCA'][$objProduct->getTable()]['fields'] as $name => $arrField) {
            $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$name]['sorting'] = ($name != 'price' && $name != 'variantFields' && in_array($name, $arrFields));

            $objAttribute = $GLOBALS['TL_DCA']['tl_iso_product']['attributes'][$name];
            $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$name]['filter'] = $objAttribute->be_filter ? in_array($name, $arrVariantFields) : false;
            $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$name]['search'] = $objAttribute->be_search ? in_array($name, $arrVariantFields) : false;
        }
    }


    /**
     * Add a breadcrumb menu to the page tree
     *
     * @param string
     */
    protected static function getPagesBreadcrumb()
    {
        $session = \Session::getInstance()->getData();

        // Set a new gid
        if (isset($_GET['page'])) {
            $session['filter']['tl_iso_product']['iso_page'] = (int) \Input::get('page');
            \Session::getInstance()->setData($session);
            \Controller::redirect(preg_replace('/&page=[^&]*/', '', \Environment::get('request')));
        }

        $intNode = $session['filter']['tl_iso_product']['iso_page'];

        if ($intNode < 1) {
            return '';
        }

        $arrIds   = array();
        $arrLinks = array();

        /** @var \BackendUser $objUser */
        $objUser  = \BackendUser::getInstance();

        // Generate breadcrumb trail
        if ($intNode) {
            $intId       = $intNode;
            $objDatabase = \Database::getInstance();

            do {
                $objPage = $objDatabase->prepare("SELECT * FROM tl_page WHERE id=?")
                    ->limit(1)
                    ->execute($intId);

                if ($objPage->numRows < 1) {
                    // Currently selected page does not exits
                    if ($intId == $intNode) {
                        $session['filter']['tl_iso_product']['iso_page'] = 0;
                        \Session::getInstance()->setData($session);

                        return '';
                    }

                    break;
                }

                $arrIds[] = $intId;

                // No link for the active page
                if ($objPage->id == $intNode) {
                    $arrLinks[] = \Backend::addPageIcon($objPage->row(), '', null, '', true) . ' ' . $objPage->title;
                } else {
                    $arrLinks[] = \Backend::addPageIcon($objPage->row(), '', null, '', true) . ' <a href="' . \Controller::addToUrl('page=' . $objPage->id) . '" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['selectNode']) . '">' . $objPage->title . '</a>';
                }

                // Do not show the mounted pages
                if (!$objUser->isAdmin && $objUser->hasAccess($objPage->id, 'pagemounts')) {
                    break;
                }

                $intId = $objPage->pid;
            } while ($intId > 0 && $objPage->type != 'root');
        }

        // Check whether the node is mounted
        if (!$objUser->isAdmin && !$objUser->hasAccess($arrIds, 'pagemounts')) {
            $session['filter']['tl_iso_product']['iso_page'] = 0;
            \Session::getInstance()->setData($session);

            \System::log('Page ID ' . $intNode . ' was not mounted', __METHOD__, TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');
        }

        // Limit tree
        $GLOBALS['TL_DCA']['tl_page']['list']['sorting']['root'] = array($intNode);

        // Add root link
        $arrLinks[] = '<img src="' . TL_FILES_URL . 'system/themes/' . \Backend::getTheme() . '/images/pagemounts.gif" width="18" height="18" alt=""> <a href="' . \Controller::addToUrl('page=0') . '" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['selectAllNodes']) . '">' . $GLOBALS['TL_LANG']['MSC']['filterAll'] . '</a>';
        $arrLinks   = array_reverse($arrLinks);

        // Insert breadcrumb menu
        return '

<ul id="tl_breadcrumb">
  <li>' . implode(' &gt; </li><li>', $arrLinks) . '</li>
</ul>';
    }
}
