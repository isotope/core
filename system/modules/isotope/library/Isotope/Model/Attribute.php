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

namespace Isotope\Model;

use Haste\Util\Format;
use Isotope\Interfaces\IsotopeAttribute;
use Isotope\Interfaces\IsotopeAttributeWithOptions;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Isotope;
use Isotope\Translation;


/**
 * Attribute represents a product attribute in Isotope eCommerce
 *
 * @property int           $id
 * @property int           $tstamp
 * @property string        $name
 * @property string        $field_name
 * @property string        $type
 * @property string        $legend
 * @property string        $description
 * @property string        $optionsSource
 * @property string|array  $options
 * @property string        $foreignKey
 * @property bool          $includeBlankOption
 * @property string        $blankOptionLabel
 * @property bool          $variant_option
 * @property bool          $customer_defined
 * @property bool          $be_search
 * @property bool          $be_filter
 * @property bool          $mandatory
 * @property bool          $fe_filter
 * @property bool          $fe_search
 * @property bool          $fe_sorting
 * @property bool          $multiple
 * @property int           $size
 * @property string        $extensions
 * @property string        $rte
 * @property bool          $multilingual
 * @property bool          $rgxp
 * @property bool          $placeholder
 * @property int           $minlength
 * @property int           $maxlength
 * @property string        $conditionField
 * @property string        $fieldType
 * @property bool          $files
 * @property bool          $filesOnly
 * @property string        $sortBy
 * @property string        $path
 * @property bool          $storeFile
 * @property string        $uploadFolder
 * @property bool          $useHomeDir
 * @property bool          $doNotOverwrite
 * @property bool          $checkoutRelocate
 * @property string        $checkoutTargetFolder
 * @property string        $checkoutTargetFile
 * @property bool          $datepicker
 */
abstract class Attribute extends TypeAgent implements IsotopeAttribute
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_iso_attribute';

    /**
     * Interface to validate attribute
     * @var string
     */
    protected static $strInterface = '\Isotope\Interfaces\IsotopeAttribute';

    /**
     * List of types (classes) for this model
     * @var array
     */
    protected static $arrModelTypes = array();

    /**
     * Holds a map for field name to ID
     * @var array
     */
    protected static $arrFieldNameMap = array();

    /**
     * Options for variants cache
     * @var array
     */
    private $arrOptionsForVariants = array();

    /**
     * Return true if attribute is a variant option
     *
     * @return bool
     *
     * @deprecated will only be available when IsotopeAttributeForVariants interface is implemented
     */
    public function isVariantOption()
    {
        return (bool) $this->variant_option;
    }

    /**
     * @inheritdoc
     */
    public function getFieldName()
    {
        return $this->field_name;
    }

    /**
     * @inheritdoc
     */
    public function isCustomerDefined()
    {
        /* @todo in 3.0: $this instanceof IsotopeAttributeForVariants */
        if ($this->isVariantOption()) {
            return false;
        }

        return (bool) $this->customer_defined;
    }

    /**
     * @inheritdoc
     */
    public function getBackendWidget()
    {
        if (!isset($GLOBALS['BE_FFL'][$this->type])) {
            throw new \LogicException('Backend widget for attribute type "' . $this->type . '" does not exist.');
        }

        return $GLOBALS['BE_FFL'][$this->type];
    }

    /**
     * @inheritdoc
     */
    public function getFrontendWidget()
    {
        if (!isset($GLOBALS['TL_FFL'][$this->type])) {
            throw new \LogicException('Frontend widget for attribute type "' . $this->type . '" does not exist.');
        }

        return $GLOBALS['TL_FFL'][$this->type];
    }

    /**
     * @inheritdoc
     */
    public function loadFromDCA(array &$arrData, $strName)
    {
        $arrField = &$arrData['fields'][$strName];

        $this->arrData = is_array($arrField['attributes']) ? $arrField['attributes'] : array();

        if (is_array($arrField['eval'])) {
            $this->arrData = array_merge($arrField['eval'], $this->arrData);
        }

        $this->field_name  = $strName;
        $this->type        = array_search(get_called_class(), static::getModelTypes());
        $this->name        = is_array($arrField['label']) ? $arrField['label'][0] : ($arrField['label'] ?: $strName);
        $this->description = is_array($arrField['label']) ? $arrField['label'][1] : '';
        $this->be_filter   = $arrField['filter'] ? '1' : '';
        $this->be_search   = $arrField['search'] ? '1' : '';
        $this->foreignKey  = $arrField['foreignKey'];
        $this->optionsSource = '';
    }

    /**
     * @inheritdoc
     */
    public function saveToDCA(array &$arrData)
    {
        // Keep field settings made through DCA code
        $arrField = is_array($arrData['fields'][$this->field_name]) ? $arrData['fields'][$this->field_name] : array();

        $arrField['label']                          = Translation::get(array($this->name, $this->description));
        $arrField['exclude']                        = true;
        $arrField['inputType']                      = '';
        $arrField['attributes']                     = $this->row();
        $arrField['attributes']['variant_option']   = $this->isVariantOption(); /* @todo in 3.0: $this instanceof IsotopeAttributeForVariants */
        $arrField['attributes']['customer_defined'] = $this->isCustomerDefined();
        $arrField['eval']                           = is_array($arrField['eval']) ? array_merge($arrField['eval'], $arrField['attributes']) : $arrField['attributes'];

        if (!$this->isCustomerDefined()) {
            $arrField['inputType'] = (string) array_search($this->getBackendWidget(), $GLOBALS['BE_FFL'], true);
        }

        // Support numeric paths (fileTree)
        unset($arrField['eval']['path']);
        if ($this->path != '' && ($objFile = \FilesModel::findByPk($this->path)) !== null) {
            $arrField['eval']['path'] = $objFile->path;
        }

        // Contao tries to load an empty tinyMCE config otherwise (see #1111)
        if ($this->rte == '') {
            unset($arrField['eval']['rte']);
        }

        if ($this->be_filter) {
            $arrField['filter'] = true;
        }

        if ($this->be_search) {
            $arrField['search'] = true;
        }

        // Variant selection is always mandatory
        /* @todo in 3.0: $this instanceof IsotopeAttributeForVariants */
        if ($this->isVariantOption()) {
            $arrField['eval']['mandatory'] = true;
            $arrField['eval']['includeBlankOption'] = true;
        }

        if ($this->blankOptionLabel != '') {
            $arrField['eval']['blankOptionLabel'] = Translation::get($this->blankOptionLabel);
        }

        // Prepare options
        if (IsotopeAttributeWithOptions::SOURCE_FOREIGNKEY === $this->optionsSource && !$this->isVariantOption()) {
            $arrField['foreignKey'] = $this->parseForeignKey($this->foreignKey, $GLOBALS['TL_LANGUAGE']);
            unset($arrField['options'], $arrField['reference']);

        } else {
            $arrOptions = null;

            switch ($this->optionsSource) {
                case IsotopeAttributeWithOptions::SOURCE_ATTRIBUTE:
                    $arrOptions = deserialize($this->options);
                    break;

                case IsotopeAttributeWithOptions::SOURCE_FOREIGNKEY:
                    $foreignKey = $this->parseForeignKey($this->foreignKey, $GLOBALS['TL_LANGUAGE']);
                    $arrKey     = explode('.', $foreignKey, 2);
                    $arrOptions = \Database::getInstance()
                        ->execute("SELECT id AS value, {$arrKey[1]} AS label FROM {$arrKey[0]} ORDER BY label")
                        ->fetchAllAssoc()
                    ;
                    break;

                case IsotopeAttributeWithOptions::SOURCE_TABLE:
                    $query = new \DC_Multilingual_Query(AttributeOption::getTable());
                    $arrOptions = $query
                        ->addField('t1.id AS value')
                        ->addOrder('t1.label')
                        ->addWhere('t1.pid = ?')
                        ->getStatement()
                        ->execute($this->id)
                        ->fetchAllAssoc()
                    ;
                    break;

                case IsotopeAttributeWithOptions::SOURCE_PRODUCT:
                    $query = new \DC_Multilingual_Query(AttributeOption::getTable());
                    $arrOptions = $query
                        ->addField('t1.id AS value')
                        ->addOrder('t1.label')
                        ->addWhere('t1.field_name = ?')
                        ->getStatement()
                        ->execute($this->field_name)
                        ->fetchAllAssoc()
                    ;
                    break;

                default:
                    if ($this instanceof IsotopeAttributeWithOptions) {
                        unset($arrField['options'], $arrField['reference']);
                    }
            }

            if (!empty($arrOptions) && is_array($arrOptions)) {
                $arrField['default'] = array();
                $arrField['options'] = array();
                $arrField['eval']['isAssociative'] = true;
                unset($arrField['reference']);
                $strGroup = '';

                foreach ($arrOptions as $option) {
                    if ($option['group']) {
                        $strGroup = Translation::get($option['label']);
                        continue;
                    }

                    if ($strGroup != '') {
                        $arrField['options'][$strGroup][$option['value']] = Translation::get($option['label']);
                    } else {
                        $arrField['options'][$option['value']] = Translation::get($option['label']);
                    }

                    if ($option['default']) {
                        $arrField['default'][] = $option['value'];
                    }
                }
            }
        }

        unset($arrField['eval']['foreignKey'], $arrField['eval']['options']);

        // Add field to the current DCA table
        $arrData['fields'][$this->field_name] = $arrField;
    }

    /**
     * Get field options
     * @return  array
     * @deprecated  will only be available when IsotopeAttributeWithOptions interface is implemented
     */
    public function getOptions()
    {
        $arrOptions = deserialize($this->options);

        if (!is_array($arrOptions)) {
            return array();
        }

        return $arrOptions;
    }

    /**
     * Get available variant options for a product
     *
     * @param int[] $arrIds
     * @param array $arrOptions
     *
     * @return array
     * @deprecated will only be available when IsotopeAttributeForVariants interface is implemented
     */
    public function getOptionsForVariants(array $arrIds, array $arrOptions = array())
    {
        if (0 === count($arrIds)) {
            return [];
        }

        sort($arrIds);
        ksort($arrOptions);
        $strKey = md5(implode('-', $arrIds) . '_' . json_encode($arrOptions));

        if (!isset($this->arrOptionsForVariants[$strKey])) {
            $strWhere = '';

            foreach ($arrOptions as $field => $value) {
                $strWhere .= " AND $field=?";
            }

            $this->arrOptionsForVariants[$strKey] = \Database::getInstance()->prepare('
                SELECT DISTINCT ' . $this->field_name . ' FROM tl_iso_product WHERE id IN (' . implode(',', $arrIds) . ')
                ' . $strWhere
            )->execute($arrOptions)->fetchEach($this->field_name);
        }

        return $this->arrOptionsForVariants[$strKey];
    }

    /**
     * Gets attribute value from given product.
     *
     * @param IsotopeProduct $product
     *
     * @return mixed
     */
    public function getValue(IsotopeProduct $product)
    {
        return $product->{$this->field_name};
    }

    /**
     * @param array $options
     *
     * @return string
     */
    public function getLabel(array $options = [])
    {
        return Format::dcaLabel('tl_iso_product', $this->field_name);
    }

    /**
     * Generate HTML markup of product data for this attribute
     *
     * @param IsotopeProduct $objProduct
     * @param array          $arrOptions
     *
     * @return string
     */
    public function generate(IsotopeProduct $objProduct, array $arrOptions = array())
    {
        $varValue = $this->getValue($objProduct);

        if (!is_array($varValue)) {
            return $this->generateValue($varValue, $arrOptions);
        }

        // Generate a HTML table for associative arrays
        if (!array_is_assoc($varValue) && is_array($varValue[0])) {
            return $arrOptions['noHtml'] ? $varValue : $this->generateTable($varValue, $objProduct);
        }

        if ($arrOptions['noHtml']) {
            $result = array();

            foreach ($varValue as $v1) {
                $result[$v1] = $this->generateValue($v1, $arrOptions);
            }

            return $result;
        }

        // Generate ul/li listing for simple arrays
        foreach ($varValue as &$v2) {
            $v2 = $this->generateValue($v2, $arrOptions);
        }

        return $this->generateList($varValue);
    }

    /**
     * @param mixed $value
     * @param array $options
     *
     * @return string
     */
    public function generateValue($value, array $options = [])
    {
        return Format::dcaValue('tl_iso_product', $this->field_name, $value);
    }

    /**
     * Returns the foreign key for a certain language with a fallback option
     *
     * @param string $strSettings
     * @param bool   $strLanguage
     *
     * @return mixed
     */
    protected function parseForeignKey($strSettings, $strLanguage = false)
    {
        $strFallback = null;
        $arrLines    = trimsplit('@\r\n|\n|\r@', $strSettings);

        // Return false if there are no lines
        if ($strSettings == '' || !is_array($arrLines) || empty($arrLines)) {
            return null;
        }

        // Loop over the lines
        foreach ($arrLines as $strLine) {
            // Ignore empty lines and comments
            if ($strLine == '' || strpos($strLine, '#') === 0) {
                continue;
            }

            // Check for a language
            if (strpos($strLine, '=') === 2) {
                list($language, $foreignKey) = explode('=', $strLine, 2);

                if ($language == $strLanguage) {
                    return $foreignKey;
                } elseif (is_null($strFallback)) {
                    $strFallback = $foreignKey;
                }
            } // Otherwise the first row is the fallback
            elseif (is_null($strFallback)) {
                $strFallback = $strLine;
            }
        }

        return $strFallback;
    }

    /**
     * Generate HTML table for associative array values
     *
     * @param array          $arrValues
     * @param IsotopeProduct $objProduct
     *
     * @return string
     */
    protected function generateTable(array $arrValues, IsotopeProduct $objProduct)
    {
        $arrFormat = $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$this->field_name]['tableformat'];

        $last = count($arrValues[0]) - 1;

        $strBuffer = '
<table class="' . $this->field_name . '">
  <thead>
    <tr>';

        foreach (array_keys($arrValues[0]) as $i => $name) {
            if ($arrFormat[$name]['doNotShow']) {
                continue;
            }

            $label = $arrFormat[$name]['label'] ?: $name;

            $strBuffer .= '
      <th class="head_' . $i . ($i == 0 ? ' head_first' : '') . ($i == $last ? ' head_last' : '') . (!is_numeric($name) ? ' ' . standardize($name) : '') . '">' . $label . '</th>';
        }

        $strBuffer .= '
    </tr>
  </thead>
  <tbody>';

        foreach ($arrValues as $r => $row) {
            $strBuffer .= '
    <tr class="row_' . $r . ($r == 0 ? ' row_first' : '') . ($r == $last ? ' row_last' : '') . ' ' . ($r % 2 ? 'odd' : 'even') . '">';

            $c = -1;

            foreach ($row as $name => $value) {
                if ($arrFormat[$name]['doNotShow']) {
                    continue;
                }

                if ('price' === $arrFormat[$name]['rgxp']) {
                    $intTax = (int) $row['tax_class'];

                    $value = Isotope::formatPriceWithCurrency(Isotope::calculatePrice($value, $objProduct, $this->field_name, $intTax));
                } else {
                    $value = $arrFormat[$name]['format'] ? sprintf($arrFormat[$name]['format'], $value) : $value;
                }

                $strBuffer .= '
      <td class="col_' . ++$c . ($c == 0 ? ' col_first' : '') . ($c == $last ? ' col_last' : '') . ' ' . standardize($name) . '">' . $value . '</td>';
            }

            $strBuffer .= '
    </tr>';
        }

        $strBuffer .= '
  </tbody>
</table>';

        return $strBuffer;
    }

    /**
     * Generate HTML list for array values
     *
     * @param array $arrValues
     *
     * @return string
     */
    protected function generateList(array $arrValues)
    {
        $strBuffer = "\n<ul>";

        $current = 0;
        $last    = count($arrValues) - 1;
        foreach ($arrValues as $value) {
            $class = trim(($current == 0 ? 'first' : '') . ($current == $last ? ' last' : ''));

            $strBuffer .= "\n<li" . ($class != '' ? ' class="' . $class . '"' : '') . '>' . $value . '</li>';

            ++$current;
        }

        $strBuffer .= "\n</ul>";

        return $strBuffer;
    }

    /**
     * Get list of system columns
     *
     * @return array
     */
    public static function getSystemColumnsFields()
    {
        static $arrFields;

        if (null === $arrFields) {
            \Controller::loadDataContainer('tl_iso_product');

            $arrFields = array();
            $arrDCA    = &$GLOBALS['TL_DCA']['tl_iso_product']['fields'];

            foreach ($arrDCA as $field => $config) {
                if ($config['attributes']['systemColumn']) {
                    $arrFields[] = $field;
                }
            }
        }

        return $arrFields;
    }

    /**
     * Return list of variant option fields
     *
     * @return array
     */
    public static function getVariantOptionFields()
    {
        static $arrFields;

        if (null === $arrFields) {
            \Controller::loadDataContainer('tl_iso_product');

            $arrFields = array();
            $arrAttributes = &$GLOBALS['TL_DCA']['tl_iso_product']['attributes'];

            /** @var Attribute $objAttribute */
            foreach ($arrAttributes as $field => $objAttribute) {
                /* @todo in 3.0: $objAttribute instanceof IsotopeAttributeForVariants */
                if ($objAttribute->isVariantOption()) {
                    $arrFields[] = $field;
                }
            }
        }

        return $arrFields;
    }

    /**
     * Return list of fields that are customer defined
     *
     * @return array
     */
    public static function getCustomerDefinedFields()
    {
        static $arrFields;

        if (null === $arrFields) {
            \Controller::loadDataContainer('tl_iso_product');

            $arrFields = array();
            $arrDCA    = &$GLOBALS['TL_DCA']['tl_iso_product']['fields'];

            foreach ($arrDCA as $field => $config) {
                if ($config['attributes']['customer_defined']) {
                    $arrFields[] = $field;
                }
            }
        }

        return $arrFields;
    }

    /**
     * Return array of attributes that have price relevant information
     *
     * @return array
     */
    public static function getPricedFields()
    {
        static $arrFields;

        if (null === $arrFields) {
            $arrFields = \Database::getInstance()->query("
                SELECT a.field_name
                FROM tl_iso_attribute a
                JOIN tl_iso_attribute_option o ON a.id=o.pid
                WHERE
                  a.optionsSource='table'
                  AND o.ptable='tl_iso_attribute'
                  AND o.published='1'
                  AND o.price!=''

                UNION

                SELECT a.field_name
                FROM tl_iso_attribute a
                JOIN tl_iso_attribute_option o ON a.field_name=o.field_name
                WHERE
                  a.optionsSource='product'
                  AND o.ptable='tl_iso_product'
                  AND o.published='1'
                  AND o.price!=''
            ")->fetchEach('field_name');
        }

        return $arrFields;
    }

    /**
     * Return list of fields that are multilingual
     *
     * @return array
     */
    public static function getMultilingualFields()
    {
        static $arrFields;

        if (null === $arrFields) {
            \Controller::loadDataContainer('tl_iso_product');

            $arrFields = array();
            $arrDCA    = &$GLOBALS['TL_DCA']['tl_iso_product']['fields'];

            foreach ($arrDCA as $field => $config) {
                if ($config['attributes']['multilingual']) {
                    $arrFields[] = $field;
                }
            }
        }

        return $arrFields;
    }

    /**
     * Return list of fields that have fetch_fallback set
     *
     * @return array
     */
    public static function getFetchFallbackFields()
    {
        static $arrFields;

        if (null === $arrFields) {
            \Controller::loadDataContainer('tl_iso_product');

            $arrFields = array();
            $arrDCA    = &$GLOBALS['TL_DCA']['tl_iso_product']['fields'];

            foreach ($arrDCA as $field => $config) {
                if ($config['attributes']['fetch_fallback']) {
                    $arrFields[] = $field;
                }
            }
        }

        return $arrFields;
    }

    /**
     * Return list of dynamic fields
     * Dynamic fields cannot be filtered on database level (e.g. product price)
     *
     * @return array
     */
    public static function getDynamicAttributeFields()
    {
        static $arrFields;

        if (null === $arrFields) {
            \Controller::loadDataContainer('tl_iso_product');

            $arrFields = array();
            $arrDCA    = &$GLOBALS['TL_DCA']['tl_iso_product']['fields'];

            foreach ($arrDCA as $field => $config) {
                if ($config['attributes']['dynamic']
                    || ($config['eval']['multiple'] && !$config['eval']['csv'])
                ) {
                    $arrFields[] = $field;
                }
            }
        }

        return $arrFields;
    }

    /**
     * Return list of fixed fields
     * Fixed fields cannot be disabled in product type config
     *
     * @param string|null $class
     *
     * @return array
     */
    public static function getFixedFields($class = null)
    {
        \Controller::loadDataContainer('tl_iso_product');

        $arrFields = array();
        $arrDCA    = &$GLOBALS['TL_DCA']['tl_iso_product']['fields'];

        foreach ($arrDCA as $field => $config) {
            $fixed = $config['attributes']['fixed'];
            $isArray = is_array($fixed);

            if ((!$isArray && $fixed) || (null !== $class && $isArray && in_array($class, $fixed, true))) {
                $arrFields[] = $field;
            }
        }

        return $arrFields;
    }

    /**
     * Return list of variant fixed fields
     * Fixed fields cannot be disabled in product type config
     *
     * @param string|null $class
     *
     * @return array
     */
    public static function getVariantFixedFields($class = null)
    {
        \Controller::loadDataContainer('tl_iso_product');

        $arrFields = array();
        $arrDCA = &$GLOBALS['TL_DCA']['tl_iso_product']['fields'];

        foreach ($arrDCA as $field => $config) {
            $fixed   = $config['attributes']['variant_fixed'];
            $isArray = is_array($fixed);

            if ((!$isArray && $fixed) || (null !== $class && $isArray && in_array($class, $fixed, true))) {
                $arrFields[] = $field;
            }
        }

        return $arrFields;
    }

    /**
     * Return list of excluded fields
     * Excluded fields cannot be enabled in product type config
     *
     * @return array
     */
    public static function getExcludedFields()
    {
        static $arrFields;

        if (null === $arrFields) {
            \Controller::loadDataContainer('tl_iso_product');

            $arrFields = array();
            $arrDCA    = &$GLOBALS['TL_DCA']['tl_iso_product']['fields'];

            foreach ($arrDCA as $field => $config) {
                if ($config['attributes']['excluded']) {
                    $arrFields[] = $field;
                }
            }
        }

        return $arrFields;
    }

    /**
     * Return list of variant excluded fields
     * Excluded fields cannot be disabled in product type config
     *
     * @return array
     */
    public static function getVariantExcludedFields()
    {
        static $arrFields;

        if (null === $arrFields) {
            \Controller::loadDataContainer('tl_iso_product');

            $arrFields = array();
            $arrDCA    = &$GLOBALS['TL_DCA']['tl_iso_product']['fields'];

            foreach ($arrDCA as $field => $config) {
                if ($config['attributes']['variant_excluded']) {
                    $arrFields[] = $field;
                }
            }
        }

        return $arrFields;
    }

    /**
     * Return list of singular fields
     * Singular fields must not be enabled in product AND variant configuration.
     *
     * @return array
     */
    public static function getSingularFields()
    {
        static $arrFields;

        if (null === $arrFields) {
            \Controller::loadDataContainer('tl_iso_product');

            $arrFields = array();
            $arrDCA    = &$GLOBALS['TL_DCA']['tl_iso_product']['fields'];

            foreach ($arrDCA as $field => $config) {
                if ($config['attributes']['singular']) {
                    $arrFields[] = $field;
                }
            }
        }

        return $arrFields;
    }

    /**
     * Return list of fields that must be inherited by variants
     *
     * @return array
     */
    public static function getInheritFields()
    {
        static $arrFields;

        if (null === $arrFields) {
            \Controller::loadDataContainer('tl_iso_product');

            $arrFields = array();
            $arrDCA    = &$GLOBALS['TL_DCA']['tl_iso_product']['fields'];

            foreach ($arrDCA as $field => $config) {
                if ($config['attributes']['inherit']) {
                    $arrFields[] = $field;
                }
            }
        }

        return $arrFields;
    }

    /**
     * Find all valid attributes
     *
     * @param array $arrOptions An optional options array
     *
     * @return \Isotope\Model\Attribute[]|null The model collection or null if the result is empty
     */
    public static function findValid(array $arrOptions = array())
    {
        $t = static::getTable();

        // Allow to set custom option conditions
        if (!isset($arrOptions['column'])) {
            $arrOptions['column'] = array();
        } elseif (!is_array($arrOptions['column'])) {
            $arrOptions['column'] = $t.'.'.$arrOptions['column'].'=?';
        }

        $arrOptions['column'][] = "$t.type!=''";
        $arrOptions['column'][] = "$t.field_name!=''";

        return static::findAll($arrOptions);
    }

    /**
     * Get an attribute by database field name
     *
     * @param string $strField
     * @param array  $arrOptions
     *
     * @return \Model|null
     */
    public static function findByFieldName($strField, array $arrOptions = array())
    {
        if (!isset(static::$arrFieldNameMap[$strField])) {
            $objAttribute = static::findOneBy('field_name', $strField, $arrOptions);

            if (null === $objAttribute) {
                static::$arrFieldNameMap[$strField] = false;
            } else {
                static::$arrFieldNameMap[$strField] = $objAttribute->id;
            }

            return $objAttribute;

        } elseif (static::$arrFieldNameMap[$strField] === false) {
            return null;
        }

        return static::findByPk(static::$arrFieldNameMap[$strField], $arrOptions);
    }
}
