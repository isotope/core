<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
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
 * @copyright  Winans Creative 2009, Intelligent Spark 2010, iserv.ch GmbH 2010
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


class InlineGallery extends IsotopeGallery
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'iso_gallery_inline';


	/**
	 * Generate gallery
	 */
	public function generateGallery($strType='gallery', $intSkip=0, $blnForce=false)
	{
		// Do not render gallery if there are no additional image
		$total = count($this->arrFiles);
		if (($total == 1 || $total <= $intSkip) && !$blnForce)
			return '';

		$strGallery = '';

		foreach( $this->arrFiles as $i => $arrFile )
		{
			if ($i < $intSkip)
				continue;

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


	protected function injectAjax()
	{
	}
}

