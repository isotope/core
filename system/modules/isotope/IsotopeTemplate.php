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
 * Class IsotopeTemplate
 *
 * Provide methods to handle Isotope templates.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
class IsotopeTemplate extends FrontendTemplate
{

	/**
	 * Initialize the template
	 * @param string
	 * @param string
	 */
	public function __construct($strTemplate='', $strContentType='text/html')
	{
		parent::__construct($strTemplate, $strContentType);
		$this->import('Isotope');
	}


	/**
	 * Check the Isotope config directory for a particular template
	 * @param string
	 * @return string
	 * @throws Exception
	 */
	protected function getTemplate($strTemplate, $strFormat='html5')
	{
		$arrAllowed = trimsplit(',', $GLOBALS['TL_CONFIG']['templateFiles']);

		if (is_array($GLOBALS['TL_CONFIG']['templateFiles']) && !in_array($strFormat, $arrAllowed))
		{
			throw new Exception("Invalid output format $strFormat");
		}

		$strKey = $strTemplate . '.' . $strFormat;
		$strPath = TL_ROOT . '/templates';
		$strTemplate = basename($strTemplate);

		// Check the templates subfolder
		if (TL_MODE == 'FE')
		{
			global $objPage;
			$strTemplateGroup = str_replace(array('../', 'templates/'), '', $this->Isotope->Config->templateGroup);

			if ($strTemplateGroup != '')
			{
				$strFile = $strPath . '/' . $strTemplateGroup . '/' . $strKey;

				if (file_exists($strFile))
				{
					return $strFile;
				}

				// Also check for .tpl files (backwards compatibility)
				$strFile = $strPath . '/' . $strTemplateGroup . '/' . $strTemplate . '.tpl';

				if (file_exists($strFile))
				{
					return $strFile;
				}
			}
		}

		return parent::getTemplate($strTemplate, $strFormat);
	}
}

