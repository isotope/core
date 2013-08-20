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
	 * Save attribute configuration into the given DCA array
	 * @param	array
	 */
	public function saveToDCA(array &$arrData)
	{
		// Keep field settings made through DCA code
        $arrField = is_array($arrData['fields'][$this->field_name]) ? $arrData['fields'][$this->field_name] : array();

        $arrField['label']        = Isotope::translate(array($this->name, $this->description));
        $arrField['exclude']      = true;
        $arrField['inputType']    = (TL_MODE == 'FE' ? $this->getFrontendWidget() : $this->getBackendWidget());
        $arrField['attributes']	  = $this->row();
        $arrField['eval']         = is_array($arrField['eval']) ? array_merge($arrField['eval'], $this->row()) : $arrField['attributes'];

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
                $arrOptions = $this->Database->execute("SELECT id AS value, {$arrKey[1]} AS label FROM {$arrKey[0]} ORDER BY label")->fetchAllAssoc();
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
                        $arrField['eval']['blankOptionLabel'] = Isotope::translate($option['label']);
                        continue;
                    }
                    elseif ($option['group'])
                    {
                        $strGroup = Isotope::translate($option['label']);
                        continue;
                    }

                    if ($strGroup != '')
                    {
                        $arrField['options'][$strGroup][$option['value']] = Isotope::translate($option['label']);
                    }
                    else
                    {
                        $arrField['options'][$option['value']] = Isotope::translate($option['label']);
                    }

                    $arrField['reference'][$option['value']] = Isotope::translate($option['label']);
                }
            }
        }

        unset($arrField['eval']['foreignKey']);
        unset($arrField['eval']['options']);

		// Add field to the current DCA table
        $arrData['fields'][$this->field_name] = $arrField;
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
}
