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
 * @copyright  Isotope eCommerce Workgroup 2009-2011
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Class InlineGallery
 * 
 * Provide methods to handle inline gallery.
 * @copyright  Isotope eCommerce Workgroup 2009-2011
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
class InlineGallery extends IsotopeGallery
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'iso_gallery_inline';


	/**
	 * Generate gallery and return it as HTML string
	 * @param string
	 * @param integer
	 * @param boolean
	 * @return string
	 */
	public function generateGallery($strType='gallery', $intSkip=0, $blnForce=false)
	{
		// Do not render gallery if there are no additional image
		$total = count($this->arrFiles);

		if (($total == 1 || $total <= $intSkip) && !$blnForce)
		{
			return '';
		}

		$strGallery = '';

		foreach ($this->arrFiles as $i => $arrFile)
		{
			if ($i < $intSkip)
			{
				continue;
			}

			$objTemplate = new IsotopeTemplate($this->strTemplate);

			$objTemplate->setData($arrFile);
			$objTemplate->id = $i;
			$objTemplate->mode = 'gallery';
			$objTemplate->type = $strType;
			$objTemplate->name = $this->name;
			$objTemplate->product_id = $this->product_id;
			$objTemplate->href_reader = $this->href_reader;

			list($objTemplate->link, $objTemplate->rel) = explode('|', $arrFile['link']);

			if ($i == 0)
			{
				$objTemplate->class = 'active';
			}

			$strGallery .= $objTemplate->parse();
		}

		return '<div class="iso_attribute" id="' . $this->name . '_gallery">' . $strGallery . '</div>';
	}


	/**
	 * Inject AJAX script
	 */
	protected function injectAjax()
	{
	}
}

