<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Class InheritCheckbox
 *
 * Provide methods to inherit checbkox fields.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 */
class InheritCheckBox extends CheckBox
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