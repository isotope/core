<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Model;

use Isotope\Isotope;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Translation;


/**
 * Attribute represents a product attribute in Isotope eCommerce
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
abstract class Attribute extends TypeAgent
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_iso_attributes';

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
	 * Return true if attribute is a variant option
	 * @return	bool
	 */
	public function isVariantOption()
	{
		return (bool) $this->variant_option;
	}

	/**
	 * Return true if attribute is customer defined
	 * @return	bool
	 */
	public function isCustomerDefined()
	{
		return (bool) $this->customer_defined;
	}

	/**
	 * Return class name for the backend widget or false if none should be available
	 * @return	string
	 */
	public function getBackendWidget()
	{
		if (!isset($GLOBALS['BE_FFL'][$this->type])) {
			throw new \LogicException('Backend widget for attribute type "' . $this->type . '" does not exist.');
		}

		return $GLOBALS['BE_FFL'][$this->type];
	}

	/**
	 * Return class name for the frontend widget or false if none should be available
	 * @return	string
	 */
	public function getFrontendWidget()
	{
		if (!isset($GLOBALS['TL_FFL'][$this->type])) {
			throw new \LogicException('Frontend widget for attribute type "' . $this->type . '" does not exist.');
		}

		return $GLOBALS['TL_FFL'][$this->type];
	}

	/**
	 * Load attribute configuration from given DCA array
	 * @param   array
	 */
	public function loadFromDCA(array &$arrData, $strName)
	{
	    $arrField = &$arrData['fields'][$strName];

        $this->arrData = is_array($arrField['attributes']) ? $arrField['attributes'] : array();

        if (is_array($arrField['eval'])) {
            $this->arrData = array_merge($arrField['eval'], $this->arrData);
        }

        $this->field_name = $strName;
        $this->type = $this->getClassForModelType(get_called_class());
        $this->name = is_array($arrField['label']) ? $arrField['label'][0] : ($arrField['label'] ?: $strName);
        $this->description = is_array($arrField['label']) ? $arrField['label'][1] : '';
        $this->be_filter = $arrField['filter'] ? '1' : '';
        $this->be_search = $arrField['search'] ? '1' : '';
        $this->foreignKey = $arrField['foreignKey'];
	}

	/**
	 * Save attribute configuration into the given DCA array
	 * @param	array
	 */
	public function saveToDCA(array &$arrData)
	{
		// Keep field settings made through DCA code
        $arrField = is_array($arrData['fields'][$this->field_name]) ? $arrData['fields'][$this->field_name] : array();

        $arrField['label']        = Translation::get(array($this->name, $this->description));
        $arrField['exclude']      = true;
        $arrField['inputType']    = array_search($this->getBackendWidget(), $GLOBALS['BE_FFL']);
        $arrField['attributes']	  = $this->row();
        $arrField['eval']         = is_array($arrField['eval']) ? array_merge($arrField['eval'], $arrField['attributes']) : $arrField['attributes'];

        // Support numeric paths (fileTree)
        unset($arrField['eval']['path']);
        if ($this->path && is_numeric($this->path)) {
            if (($objFile = \FilesModel::findByPk($this->path)) !== null) {
                $arrField['eval']['path'] = $objFile->path;
            }
        }

        if ($this->be_filter) {
            $arrField['filter'] = true;
        }

        if ($this->be_search) {
            $arrField['search'] = true;
        }

        // Variant selection is always mandatory
        if ($this->isVariantOption()) {
            $arrField['eval']['mandatory'] = true;
        }

        // Parse multiline/multilingual foreignKey
        $this->foreignKey = $this->parseForeignKey($this->foreignKey, $GLOBALS['TL_LANGUAGE']);

        // Prepare options
        if ($this->foreignKey != '' && !$this->isVariantOption())
        {
            $arrField['foreignKey'] = $this->foreignKey;
            $arrField['eval']['includeBlankOption'] = true;
            unset($arrField['options']);
        }
        else
        {
            $arrField['options'] = array();
            $arrField['reference'] = array();

            if ($this->foreignKey)
            {
                $arrKey = explode('.', $this->foreignKey, 2);
                $arrOptions = \Database::getInstance()->execute("SELECT id AS value, {$arrKey[1]} AS label FROM {$arrKey[0]} ORDER BY label")->fetchAllAssoc();
            }
            else
            {
                $arrOptions = deserialize($this->options);
            }

            if (is_array($arrOptions) && !empty($arrOptions))
            {
                $strGroup = '';

                foreach ($arrOptions as $option)
                {
                    if (!strlen($option['value']))
                    {
                        $arrField['eval']['includeBlankOption'] = true;
                        $arrField['eval']['blankOptionLabel'] = Translation::get($option['label']);
                        continue;
                    }
                    elseif ($option['group'])
                    {
                        $strGroup = Translation::get($option['label']);
                        continue;
                    }

                    if ($strGroup != '')
                    {
                        $arrField['options'][$strGroup][$option['value']] = Translation::get($option['label']);
                    }
                    else
                    {
                        $arrField['options'][$option['value']] = Translation::get($option['label']);
                    }

                    $arrField['reference'][$option['value']] = Translation::get($option['label']);
                }
            }
        }

        unset($arrField['eval']['foreignKey']);
        unset($arrField['eval']['options']);

		// Add field to the current DCA table
        $arrData['fields'][$this->field_name] = $arrField;
	}

	/**
	 * Get field options
	 * @return  array
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
     * @param   array
     * @param   array
     * @return  array
     */
	public function getOptionsForVariants(array $arrIds, array $arrOptions=array())
	{
	    $strWhere = '';

        foreach ($arrOptions as $field => $value) {
            $strWhere .= " AND $field='$value'";
        }

        return \Database::getInstance()->execute("
            SELECT DISTINCT " . $this->field_name . " FROM tl_iso_products WHERE id IN (" . implode(',', $arrIds) . ")
            " . $strWhere . "
        ")->fetchEach($this->field_name);
	}


	public function generate(IsotopeProduct $objProduct, array $arrOptions=array())
	{
	    $varValue = $objProduct->{$this->field_name};
	    $strBuffer = '';

	    // Generate a HTML table for associative arrays
        if (is_array($varValue) && !array_is_assoc($varValue) && is_array($varValue[0]))
        {
            $strBuffer = $this->generateTable($varValue);
        }

        // Generate ul/li listing for simpley arrays
        elseif (is_array($varValue))
        {
            $strBuffer = $this->generateList($varValue);
        }
        else
        {
            $strBuffer = Isotope::formatValue('tl_iso_products', $this->field_name, $varValue);
        }

        return $strBuffer;
	}


	/**
     * Returns the foreign key for a certain language with a fallback option
     * @param string
     * @param string
     * @return mixed
     */
    protected function parseForeignKey($strSettings, $strLanguage=false)
    {
        $strFallback = null;
        $arrLines = trimsplit('@\r\n|\n|\r@', $strSettings);

        // Return false if there are no lines
        if ($strSettings == '' || !is_array($arrLines) || empty($arrLines))
        {
            return null;
        }

        // Loop over the lines
        foreach ($arrLines as $strLine)
        {
            // Ignore empty lines and comments
            if ($strLine == '' || strpos($strLine, '#') === 0)
            {
                continue;
            }

            // Check for a language
            if (strpos($strLine, '=') === 2)
            {
                list($language, $foreignKey) = explode('=', $strLine, 2);

                if ($language == $strLanguage)
                {
                    return $foreignKey;
                }
                elseif (is_null($strFallback))
                {
                    $strFallback = $foreignKey;
                }
            }

            // Otherwise the first row is the fallback
            elseif (is_null($strFallback))
            {
                $strFallback = $strLine;
            }
        }

        return $strFallback;
    }

    /**
     * Generate HTML table for associative array values
     * @param   array
     * @return  string
     */
    protected function generateTable(array $arrValues)
    {
        $arrFormat = $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$this->field_name]['tableformat'];

        $last = count($arrValues[0])-1;

        $strBuffer = '
<table class="'.$this->field_name.'">
  <thead>
    <tr>';

        foreach (array_keys($arrValues[0]) as $i => $name)
        {
            if ($arrFormat[$name]['doNotShow'])
            {
                continue;
            }

            $label = $arrFormat[$name]['label'] ? $arrFormat[$name]['label'] : $name;

            $strBuffer .= '
      <th class="head_'.$i.($i==0 ? ' head_first' : '').($i==$last ? ' head_last' : ''). (!is_numeric($name) ? ' '.standardize($name) : '').'">' . $label . '</th>';
        }

        $strBuffer .= '
    </tr>
  </thead>
  <tbody>';

        foreach ($arrValues as $r => $row)
        {
            $strBuffer .= '
    <tr class="row_'.$r.($r==0 ? ' row_first' : '').($r==$last ? ' row_last' : '').' '.($r%2 ? 'odd' : 'even').'">';

            $c = -1;

            foreach ($row as $name => $value)
            {
                if ($arrFormat[$name]['doNotShow'])
                {
                    continue;
                }

                if ($arrFormat[$name]['rgxp'] == 'price')
                {
                    $intTax = (int) $row['tax_class'];

                    $value = Isotope::formatPriceWithCurrency(Isotope::calculatePrice($value, $objProduct, $this->field_name, $intTax));
                }
                else
                {
                    $value = $arrFormat[$name]['format'] ? sprintf($arrFormat[$name]['format'], $value) : $value;
                }

                $strBuffer .= '
      <td class="col_'.++$c.($c==0 ? ' col_first' : '').($c==$i ? ' col_last' : '').' '.standardize($name).'">' . $value . '</td>';
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
     * @param   array
     * @return  string
     */
    protected function generateList(array $arrValues)
    {
        $strBuffer = "\n<ul>";

        $current = 0;
        $last = count($arrValues)-1;
        foreach ($arrValues as $value ) {
            $class = trim(($current == 0 ? 'first' : '') . ($current == $last ? ' last' : ''));

            $strBuffer .= "\n<li".($class != '' ? ' class="'.$class.'"' : '').'>' . $value . '</li>';
        }

        $strBuffer .= "\n</ul>";

        return $strBuffer;
    }

    /**
     * Get list of system columns
     * @return  array
     */
    public static function getSystemColumnsFields()
    {
        static $arrFields;

        Isotope::getInstance()->call('loadDataContainer', 'tl_iso_products');

        if (null === $arrFields) {
            $arrFields = array();
            $arrDCA = &$GLOBALS['TL_DCA']['tl_iso_products']['fields'];

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
     * @return  array
     */
    public static function getVariantOptionFields()
    {
        static $arrFields;

        Isotope::getInstance()->call('loadDataContainer', 'tl_iso_products');

        if (null === $arrFields) {
            $arrFields = array();
            $arrDCA = &$GLOBALS['TL_DCA']['tl_iso_products']['fields'];

            foreach ($arrDCA as $field => $config) {
                if ($config['attributes']['variant_option']) {
                    $arrFields[] = $field;
                }
            }
        }

        return $arrFields;
    }

    /**
     * Return list of fields that are customer defined
     * @return  array
     */
    public static function getCustomerDefinedFields()
    {
        static $arrFields;

        Isotope::getInstance()->call('loadDataContainer', 'tl_iso_products');

        if (null === $arrFields) {
            $arrFields = array();
            $arrDCA = &$GLOBALS['TL_DCA']['tl_iso_products']['fields'];

            foreach ($arrDCA as $field => $config) {
                if ($config['attributes']['customer_defined']) {
                    $arrFields[] = $field;
                }
            }
        }

        return $arrFields;
    }

    /**
     * Return list of fields that are multilingual
     * @return  array
     */
    public static function getMultilingualFields()
    {
        static $arrFields;

        Isotope::getInstance()->call('loadDataContainer', 'tl_iso_products');

        if (null === $arrFields) {
            $arrFields = array();
            $arrDCA = &$GLOBALS['TL_DCA']['tl_iso_products']['fields'];

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
     * @return  array
     */
    public static function getFetchFallbackFields()
    {
        static $arrFields;

        Isotope::getInstance()->call('loadDataContainer', 'tl_iso_products');

        if (null === $arrFields) {
            $arrFields = array();
            $arrDCA = &$GLOBALS['TL_DCA']['tl_iso_products']['fields'];

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
     * @return  array
     */
    public static function getDynamicAttributeFields()
    {
        static $arrFields;

        Isotope::getInstance()->call('loadDataContainer', 'tl_iso_products');

        if (null === $arrFields) {
            $arrFields = array();
            $arrDCA = &$GLOBALS['TL_DCA']['tl_iso_products']['fields'];

            foreach ($arrDCA as $field => $config) {
                if ($config['attributes']['dynamic'] || $config['eval']['multiple']) {
                    $arrFields[] = $field;
                }
            }
        }

        return $arrFields;
    }

    /**
     * Return list of fixed fields
     * Fixed fields cannot be disabled in product type config
     * @return  array
     */
    public static function getFixedFields()
    {
        static $arrFields;

        Isotope::getInstance()->call('loadDataContainer', 'tl_iso_products');

        if (null === $arrFields) {
            $arrFields = array();
            $arrDCA = &$GLOBALS['TL_DCA']['tl_iso_products']['fields'];

            foreach ($arrDCA as $field => $config) {
                if ($config['attributes']['fixed']) {
                    $arrFields[] = $field;
                }
            }
        }

        return $arrFields;
    }

    /**
     * Return list of fixed fields
     * Fixed fields cannot be disabled in product type config
     * @return  array
     */
    public static function getVariantFixedFields()
    {
        static $arrFields;

        Isotope::getInstance()->call('loadDataContainer', 'tl_iso_products');

        if (null === $arrFields) {
            $arrFields = array();
            $arrDCA = &$GLOBALS['TL_DCA']['tl_iso_products']['fields'];

            foreach ($arrDCA as $field => $config) {
                if ($config['attributes']['variant_fixed']) {
                    $arrFields[] = $field;
                }
            }
        }

        return $arrFields;
    }
}
