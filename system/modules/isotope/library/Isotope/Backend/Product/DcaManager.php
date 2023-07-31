<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\Product;

use Contao\Backend;
use Contao\BackendUser;
use Contao\Controller;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\Database;
use Contao\Environment;
use Contao\Input;
use Contao\Session;
use Contao\StringUtil;
use Haste\Util\Format;
use Isotope\Backend\Group\Breadcrumb;
use Isotope\Interfaces\IsotopeAttribute;
use Isotope\Interfaces\IsotopeAttributeWithOptions;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Model\Attribute;
use Isotope\Model\Group;
use Isotope\Model\Product;
use Isotope\Model\ProductType;
use Isotope\Model\RelatedCategory;


class DcaManager extends Backend
{

    /**
     * Initialize the tl_iso_product DCA
     *
     * @param string $strTable
     */
    public function initialize($strTable)
    {
        if ($strTable != Product::getTable() || !Database::getInstance()->tableExists(Attribute::getTable())) {
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
     *
     * @param   string $strTable
     * @param   int    $insertID
     * @param   array  $arrSet
     */
    public function updateNewRecord($strTable, $insertID, $arrSet)
    {
        if (($arrSet['pid'] ?? 0) > 0) {
            Database::getInstance()->prepare("UPDATE $strTable SET dateAdded=? WHERE id=?")->execute(time(), $insertID);

            return;
        }

        $intType  = 0;
        $intGroup = (int) Session::getInstance()->get('iso_products_gid');

        if (!$intGroup) {
            $objUser = BackendUser::getInstance();
            $intGroup = ($objUser->isAdmin || empty($objUser->iso_groups)) ? 0 : (int) $objUser->iso_groups[0];
        }

        $objGroup = Group::findByPk($intGroup);

        if (null === $objGroup || null === $objGroup->getRelated('product_type')) {
            $objType = ProductType::findFallback();
        } else {
            $objType = $objGroup->getRelated('product_type');
        }

        if (null !== $objType) {
            $intType = $objType->id;
        }

        Database::getInstance()->prepare("UPDATE $strTable SET gid=?, type=?, dateAdded=? WHERE id=?")->execute($intGroup, $intType, time(), $insertID);
    }

    /**
     * Update dateAdded on copy
     *
     * @param int $insertId
     *
     * @link http://www.contao.org/callbacks.html#oncopy_callback
     */
    public function updateDateAdded($insertId)
    {
        Database::getInstance()
            ->prepare("UPDATE tl_iso_product SET dateAdded=? WHERE id=?")
            ->execute(time(), $insertId)
        ;
    }

    /**
     * Add custom attributes to tl_iso_product DCA
     */
    protected function addAttributes()
    {
        $arrData               = &$GLOBALS['TL_DCA'][Product::getTable()];
        $arrData['attributes'] = array();

        // Write attributes from database to DCA
        /** @var Attribute[] $objAttributes */
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
            if (\is_array($arrConfig['attributes'] ?? null)) {
                if (!empty($arrConfig['attributes']['type'])) {
                    $strClass = $arrConfig['attributes']['type'];
                } else {
                    $strClass = Attribute::getClassForModelType($arrConfig['inputType'] ?? '');
                }

                if ($strClass != '') {

                    /** @var Attribute $objAttribute */
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

        /** @var ProductType[] $objProductTypes */
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

                if (\in_array('sku', $objType->getAttributes(), true)) {
                    $blnShowSku = true;
                }

                if (\in_array('price', $objType->getAttributes(), true)) {
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
            unset(
                $GLOBALS['TL_DCA'][Product::getTable()]['list']['global_operations']['toggleVariants'],
                $GLOBALS['TL_DCA'][Product::getTable()]['list']['operations']['generate']
            );
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
            if ($GLOBALS['TL_DCA'][Product::getTable()]['fields'][$field]['attributes']['systemColumn'] ?? false) {
                continue;
            }

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
        // Avoid the page node trap (#1701)
        if (\defined('TL_SCRIPT') && TL_SCRIPT === 'contao/page.php') {
            return;
        }

        $strBreadcrumb = Breadcrumb::generate(Session::getInstance()->get('iso_products_gid'));
        $strBreadcrumb .= static::getPagesBreadcrumb();

        $GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['breadcrumb'] = $strBreadcrumb;
    }

    /**
     * Build palette for the current product type/variant
     */
    public function buildPaletteString()
    {
        Controller::loadDataContainer(Attribute::getTable());

        if ((Input::get('act') == '' && Input::get('key') == '') || 'select' === Input::get('act')) {
            return;
        }

        $arrTypes      = array();
        $arrFields     = &$GLOBALS['TL_DCA']['tl_iso_product']['fields'];
        /** @var IsotopeAttribute[] $arrAttributes */
        $arrAttributes = &$GLOBALS['TL_DCA']['tl_iso_product']['attributes'];

        $blnVariants     = false;
        $act             = Input::get('act');
        $blnSingleRecord = $act === 'edit' || $act === 'show';

        if (Input::get('id') > 0) {

            /** @var object $objProduct */
            $objProduct = Database::getInstance()->prepare("SELECT p1.pid, p1.type, p2.type AS parent_type FROM tl_iso_product p1 LEFT JOIN tl_iso_product p2 ON p1.pid=p2.id WHERE p1.id=?")->execute(Input::get('id'));

            if ($objProduct->numRows) {
                $objType  = ProductType::findByPk(($objProduct->pid > 0 ? $objProduct->parent_type : $objProduct->type));
                $arrTypes = null === $objType ? array() : array($objType);

                if ($objProduct->pid > 0 || ('edit' !== $act && 'copyFallback' !== $act && 'show' !== $act)) {
                    $blnVariants = true;
                }
            }
        } else {
            $arrTypes = ProductType::findAllUsed() ? : array();
        }

        /** @var ProductType $objType */
        foreach ($arrTypes as $objType) {

            // Enable advanced prices
            if ($blnSingleRecord && $objType->hasAdvancedPrices()) {
                $arrFields['prices']['exclude']    = $arrFields['price']['exclude'];
                $arrFields['prices']['attributes'] = $arrFields['price']['attributes'];
                $arrFields['price']                = $arrFields['prices'];
            }

            // Register callback to version/restore a price
            else {
                $GLOBALS['TL_DCA']['tl_iso_product']['config']['onversion_callback']['iso_product_price'] = array('Isotope\Backend\Product\Price', 'createVersion');
                $GLOBALS['TL_DCA']['tl_iso_product']['config']['onrestore_callback']['iso_product_price'] = array('Isotope\Backend\Product\Price', 'restoreVersion');
            }

            $arrInherit = array();
            $arrPalette = array();
            $arrLegends = array();
            $arrLegendOrder = array();
            $arrCanInherit = array();

            if ($blnVariants) {
                $arrConfig     = $objType->variant_attributes;
                $arrEnabled    = $objType->getVariantAttributes();
                $arrCanInherit = $objType->getAttributes();
            } else {
                $arrConfig  = $objType->attributes;
                $arrEnabled = $objType->getAttributes();
            }

            // Go through each enabled field and build palette
            foreach ($arrFields as $name => $arrField) {
                if (\in_array($name, $arrEnabled)) {

                    if ($arrField['inputType'] == '') {
                        continue;
                    }

                    // Variant fields can only be edited in variant mode
                    if (isset($arrAttributes[$name])
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

                    if ('yes' === $arrConfig[$name]['mandatory']) {
                        $arrFields[$name]['eval']['mandatory'] = true;
                    } elseif ('no' === $arrConfig[$name]['mandatory']) {
                        $arrFields[$name]['eval']['mandatory'] = false;
                    }

                    if ($blnVariants
                        && \in_array($name, $arrCanInherit)
                        && null !== $arrAttributes[$name]
                        && /* @todo in 3.0: $arrAttributes[$name] instanceof IsotopeAttributeForVariants
                        && */!$arrAttributes[$name]->isVariantOption()
                        && !\in_array($name, ['price', 'published', 'start', 'stop'], true)
                    ) {
                        $arrInherit[$name] = Format::dcaLabel('tl_iso_product', $name);
                    }

                } else {

                    // Hide field from "show" option
                    if ((!isset($arrField['attributes']) || ($arrField['inputType'] ?? '') != '') && 'inherit' !== $name) {
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
            $arrInclude = empty($arrPalette) ? array() : array_merge(...array_values($arrPalette));

            foreach ($arrFields as $name => $config) {
                if (($arrFields[$name]['attributes']['legend'] ?? '') != '' && !\in_array($name, $arrInclude)) {
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
        if ((Input::get('act') != '' && 'select' !== Input::get('act'))
            || Input::get('id') == ''
            || ($objProduct = Product::findByPk(Input::get('id'))) === null
        ) {
            return;
        }

        $GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['mode']    = 4;
        $GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['fields']  = ['id'];
        $GLOBALS['TL_DCA']['tl_iso_product']['fields']['alias']['sorting'] = false;

        $arrFields = array();
        $objType = $objProduct->getType();

        $arrVariantFields = $objType->getVariantAttributes();
        $arrVariantOptions = array_intersect($arrVariantFields, Attribute::getVariantOptionFields());

        if (\in_array('images', $arrVariantFields, true)) {
            $arrFields[] = 'images';
        }

        if (\in_array('name', $arrVariantFields, true)) {
            $arrFields[] = 'name';
            $GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['fields'] = array('name');
        }

        if (\in_array('sku', $arrVariantFields, true)) {
            $arrFields[] = 'sku';
            $GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['fields'] = array('sku');
        }

        if (\in_array('price', $arrVariantFields, true)) {
            $arrFields[] = 'price';
        }

        // Limit the number of columns if there are more than 2
        if (\count($arrVariantOptions) > 2) {
            $arrFields[] = 'variantFields';
            $GLOBALS['TL_DCA']['tl_iso_product']['list']['label']['variantFields'] = $arrVariantOptions;
        } else {
            foreach (array_merge($arrVariantOptions) as $name) {

                /** @var Attribute $objAttribute */
                $objAttribute = $GLOBALS['TL_DCA']['tl_iso_product']['attributes'][$name];

                if ($objAttribute instanceof IsotopeAttributeWithOptions
                    && 'table' === $objAttribute->optionsSource
                ) {
                    $name .= ':tl_iso_attribute_option.label';
                }

                $arrFields[] = $name;
            }
        }

        $GLOBALS['TL_DCA']['tl_iso_product']['list']['label']['fields'] = $arrFields;

        // Make all column fields sortable
        foreach ($GLOBALS['TL_DCA']['tl_iso_product']['fields'] as $name => $arrField) {
            $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$name]['sorting'] = ('price' !== $name && 'variantFields' !== $name && \in_array($name, $arrFields));

            $objAttribute = $GLOBALS['TL_DCA']['tl_iso_product']['attributes'][$name] ?? null;
            $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$name]['filter'] = $objAttribute && ($objAttribute->be_filter ? \in_array($name, $arrVariantFields) : false);
            $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$name]['search'] = $objAttribute && ($objAttribute->be_search ? \in_array($name, $arrVariantFields) : false);
        }
    }


    /**
     * Add options from attribute to DCA
     *
     * @param array  $arrData
     * @param object $objDca
     *
     * @return array
     */
    public function addOptionsFromAttribute($arrData, $objDca)
    {
        if ($arrData['strTable'] == Product::getTable()
            && ($arrData['optionsSource'] ?? '') != ''
            && 'foreignKey' !== $arrData['optionsSource']
        ) {

            /** @var IsotopeAttributeWithOptions|Attribute $objAttribute */
            $objAttribute = Attribute::findByFieldName($arrData['strField']);

            if (null !== $objAttribute && $objAttribute instanceof IsotopeAttributeWithOptions) {
                $arrData['options'] = ($objDca instanceof IsotopeProduct) ? $objAttribute->getOptionsForWidget($objDca) : $objAttribute->getOptionsForWidget();

                if (!empty($arrData['options'])) {
                    if ($arrData['includeBlankOption']) {
                        array_unshift($arrData['options'], array('value'=>'', 'label'=>($arrData['blankOptionLabel'] ?: '-')));
                    }

                    if (null !== ($arrData['default'] ?? null)) {
                        $arrDefault = array_filter(
                            $arrData['options'],
                            function (&$option) {
                                return (bool) $option['default'];
                            }
                        );

                        if (!empty($arrDefault)) {
                            array_walk(
                                $arrDefault,
                                function (&$value) {
                                    $value = $value['value'];
                                }
                            );

                            $arrData['value'] = ($objAttribute->multiple ? $arrDefault : $arrDefault[0]);
                        }
                    }
                }
            }
        }

        return $arrData;
    }


    /**
     * Add a breadcrumb menu to the page tree
     *
     * @return string
     */
    protected static function getPagesBreadcrumb()
    {
        $session = Session::getInstance()->getData();

        // Set a new gid
        if (isset($_GET['page'])) {
            $session['filter']['tl_iso_product']['iso_page'] = (int) Input::get('page');
            Session::getInstance()->setData($session);
            Controller::redirect(preg_replace('/&page=[^&]*/', '', Environment::get('request')));
        }

        $intNode = $session['filter']['tl_iso_product']['iso_page'] ?? 0;

        if ($intNode < 1) {
            return '';
        }

        $arrIds   = array();
        $arrLinks = array();

        $objUser = BackendUser::getInstance();

        // Generate breadcrumb trail
        if ($intNode) {
            $intId       = $intNode;
            $objDatabase = Database::getInstance();

            do {
                $objPage = $objDatabase->prepare("SELECT * FROM tl_page WHERE id=?")
                    ->limit(1)
                    ->execute($intId);

                if ($objPage->numRows < 1) {
                    // Currently selected page does not exits
                    if ($intId == $intNode) {
                        $session['filter']['tl_iso_product']['iso_page'] = 0;
                        Session::getInstance()->setData($session);

                        return '';
                    }

                    break;
                }

                $arrIds[] = $intId;

                // No link for the active page
                if ($objPage->id == $intNode) {
                    $arrLinks[] = Backend::addPageIcon($objPage->row(), '', null, '', true) . ' ' . $objPage->title;
                } else {
                    $arrLinks[] = Backend::addPageIcon($objPage->row(), '', null, '', true) . ' <a href="' . Controller::addToUrl('page=' . $objPage->id) . '" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['selectNode']) . '">' . $objPage->title . '</a>';
                }

                // Do not show the mounted pages
                if (!$objUser->isAdmin && $objUser->hasAccess($objPage->id, 'pagemounts')) {
                    break;
                }

                $intId = $objPage->pid;
            } while ($intId > 0 && 'root' !== $objPage->type);
        }

        // Check whether the node is mounted
        if (!$objUser->isAdmin && !$objUser->hasAccess($arrIds, 'pagemounts')) {
            $session['filter']['tl_iso_product']['iso_page'] = 0;
            Session::getInstance()->setData($session);

            throw new AccessDeniedException('Page ID ' . $intNode . ' was not mounted');
        }

        // Add root link
        $arrLinks[] = '<img src="' . TL_FILES_URL . 'system/themes/' . Backend::getTheme() . '/images/pagemounts.svg" width="18" height="18" alt=""> <a href="' . Controller::addToUrl('page=0') . '" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['selectAllNodes']) . '">' . $GLOBALS['TL_LANG']['MSC']['filterAll'] . '</a>';
        $arrLinks   = array_reverse($arrLinks);

        // Insert breadcrumb menu
        return '

<ul id="tl_breadcrumb">
  <li>' . implode(' &gt; </li><li>', $arrLinks) . '</li>
</ul>';
    }
}
