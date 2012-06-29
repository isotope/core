<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
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
 * @copyright  Isotope eCommerce Workgroup 2011-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Kamil Kuźmiński <kamil.kuzminski@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


class ZoomGallery extends InlineGallery
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'iso_gallery_zoom';


	/**
	 * Generate gallery
	 * @param string
	 * @param integer
	 * @param boolean
	 */
	public function generateGallery($strType='gallery', $intSkip=0, $blnForce=false)
	{
		// Include scripts and styles
		if (version_compare(MOOTOOLS_CORE, '1.3.0') >= 0)
		{
			$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/isotope/html/zoomgallery.js';
			$GLOBALS['TL_CSS'][] = 'system/modules/isotope/html/zoomgallery.css';
		}

		return parent::generateGallery();
	}
}

