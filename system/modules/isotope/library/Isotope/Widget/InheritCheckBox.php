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

namespace Isotope\Widget;


/**
 * Class InheritCheckbox
 *
 * Provide methods to inherit checbkox fields.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 */
class InheritCheckBox extends \CheckBox
{

	/**
	 * Disable mandatory validation for inherited attributes
	 */
	public function validate()
	{
		parent::validate();

		if (is_array($this->varValue) && !empty($this->varValue))
		{
			foreach ($this->varValue as $field)
			{
				$GLOBALS['TL_DCA']['tl_iso_products']['fields'][$field]['eval']['mandatory'] = false;
			}
		}
	}


	/**
	 * Generate the widget and return it as string
	 * @return string
	 */
	public function generate()
	{
		$arrFields = array();
		$arrOptions = array();

		foreach ($this->arrOptions as $i=>$arrOption)
		{
			$arrFields[] = $arrOption['value'];
			$arrOptions[] = $this->generateCheckbox($arrOption, $i);
		}

		// Add a "no entries found" message if there are no options
		if (empty($arrOptions))
		{
			$arrOptions[]= '<p class="tl_noopt">'.$GLOBALS['TL_LANG']['MSC']['noResult'].'</p>';
		}

		$strJS = "
<script>
window.addEvent('domready', function() {
  Isotope.inheritFields(['" . implode("','", $arrFields) . "'], '" . str_replace("'", "\'", $GLOBALS['ISO_LANG']['MSC']['useDefault']) . "');
});
</script>
";

        return sprintf('<div id="ctrl_%s" class="%s%s">%s</div>%s%s',
						$this->strId,
						'tl_checkbox_container',
						(strlen($this->strClass) ? ' ' . $this->strClass : ''),
						str_replace('<br></div><br>', '</div>', implode('<br>', $arrOptions)),
						$this->wizard,
						$strJS);
	}


	/**
	 * Generate a checkbox and return it as string
	 * @param array
	 * @param integer
	 * @return string
	 */
	protected function generateCheckbox($arrOption, $i)
	{
		return sprintf('<input type="checkbox" name="%s" id="opt_%s" class="tl_checkbox" value="%s"%s%s onfocus="Backend.getScrollOffset();"> <label for="opt_%s">%s</label>',
						$this->strName . '[]',
						$this->strId.'_'.$i,
						specialchars($arrOption['value'], true),
						$this->isChecked($arrOption),
						$this->getAttributes(),
						$this->strId.'_'.$i,
						$arrOption['label']);
	}
}