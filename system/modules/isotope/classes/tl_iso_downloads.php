<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 *
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */

namespace Isotope;


/**
 * Class tl_iso_downloads
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_iso_downloads extends \Backend
{

	/**
	 * Update singleSRC field depending on type
	 */
	public function prepareSRC($dc)
	{
		if ($this->Input->get('act') == 'edit')
		{
			$objDownload = $this->Database->prepare("SELECT * FROM tl_iso_downloads WHERE id=?")->execute($dc->id);

			if ($objDownload->type == 'folder')
			{
				$GLOBALS['TL_DCA']['tl_iso_downloads']['fields']['singleSRC']['eval']['files'] = false;
				$GLOBALS['TL_DCA']['tl_iso_downloads']['fields']['singleSRC']['eval']['filesOnly'] = false;
			}
		}
	}


	/**
	 * Add an image to each record
	 * @param array
	 * @return string
	 */
	public function listRows($row)
	{
		if ($row['type'] == 'folder')
		{
			if (!is_dir(TL_ROOT . '/' . $row['singleSRC']))
			{
				return '';
			}

			$arrDownloads = array();

			foreach (scan(TL_ROOT . '/' . $row['singleSRC']) as $file)
			{
				if (is_file(TL_ROOT . '/' . $row['singleSRC'] . '/' . $file))
				{
					$objFile = new File($row['singleSRC'] . '/' . $file);
					$icon = 'background:url(system/themes/' . $this->getTheme() . '/images/' . $objFile->icon . ') left center no-repeat; padding-left: 22px';
					$arrDownloads[] = sprintf('<div style="margin-bottom:5px;height:16px;%s">%s</div>', $icon, $row['singleSRC'] . '/' . $file);
				}
			}

			if (empty($arrDownloads))
			{
				return $GLOBALS['ISO_LANG']['ERR']['emptyDownloadsFolder'];
			}

			return implode("\n", $arrDownloads);
		}

		if (is_file(TL_ROOT . '/' . $row['singleSRC']))
		{
			$objFile = new File($row['singleSRC']);
			$icon = 'background: url(system/themes/' . $this->getTheme() . '/images/' . $objFile->icon . ') left center no-repeat; padding-left: 22px';
		}

		return sprintf('<div style="height: 16px;%s">%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span></div>', $icon, $row['title'], $row['singleSRC']);
	}
}

