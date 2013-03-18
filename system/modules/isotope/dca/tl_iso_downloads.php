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
 * Table tl_iso_downloads
 */
$GLOBALS['TL_DCA']['tl_iso_downloads'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'					=> 'Table',
		'enableVersioning'				=> true,
		'ptable'						=> 'tl_iso_products',
		'onload_callback' => array
		(
			array('tl_iso_downloads', 'prepareSRC'),
		),
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'						=> 4,
			'fields'					=> array('sorting'),
			'flag'						=> 1,
			'panelLayout'				=> 'filter;search,limit',
			'headerFields'				=> array('name', 'alias', 'sku'),
			'child_record_callback'		=> array('tl_iso_downloads', 'listRows'),
			'disableGrouping'			=> true,
		),
		'label' => array
		(
			'fields'					=> array('title', 'singleSRC'),
			'format'					=> '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
		),
		'global_operations' => array
		(
			'all' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'					=> 'act=select',
				'class'					=> 'header_edit_all',
				'attributes'			=> 'onclick="Backend.getScrollOffset();"'
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_downloads']['edit'],
				'href'					=> 'act=edit',
				'icon'					=> 'edit.gif'
			),
			'copy' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_downloads']['copy'],
				'href'					=> 'act=paste&amp;mode=copy',
				'icon'					=> 'copy.gif',
				'attributes'			=> 'onclick="Backend.getScrollOffset();"'
			),
			'cut' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_downloads']['cut'],
				'href'					=> 'act=paste&amp;mode=cut',
				'icon'					=> 'cut.gif',
				'attributes'			=> 'onclick="Backend.getScrollOffset();"'
			),
			'delete' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_downloads']['delete'],
				'href'					=> 'act=delete',
				'icon'					=> 'delete.gif',
				'attributes'			=> 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_downloads']['show'],
				'href'					=> 'act=show',
				'icon'					=> 'show.gif'
			),
		)
	),

	// Palettes
	'palettes' => array
	(
		'__selector__'					=> array('type'),
		'default'						=> '{file_legend},type,',
		'file'							=> '{file_legend},type,singleSRC;{name_legend},title,description;{limit_legend},downloads_allowed,expires',
		'folder'						=> '{file_legend},type,singleSRC;{limit_legend},downloads_allowed,expires',
	),

	// Fields
	'fields' => array
	(
		'type' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_downloads']['type'],
			'exclude'					=> true,
			'inputType'					=> 'select',
			'options'					=> array('file', 'folder'),
			'reference'					=> &$GLOBALS['TL_LANG']['tl_iso_downloads'],
			'eval'						=> array('mandatory'=>true, 'submitOnChange'=>true),
		),
		'singleSRC' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_downloads']['singleSRC'],
			'exclude'					=> true,
			'inputType'					=> 'fileTree',
			'eval'						=> array('mandatory'=>true, 'fieldType'=>'radio', 'files'=>true, 'filesOnly'=>true, 'extensions'=>$GLOBALS['TL_CONFIG']['allowedDownload']),
		),
		'title' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_downloads']['title'],
			'exclude'					=> true,
			'inputType'					=> 'text',
			'eval'						=> array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'long'),
		),
		'description' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_downloads']['description'],
			'exclude'					=> true,
			'inputType'					=> 'textarea',
			'eval'						=> array('rte'=>'tinyMCE'),
		),
		'downloads_allowed' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_downloads']['downloads_allowed'],
			'exclude'					=> true,
			'inputType'					=> 'text',
			'eval'						=> array('mandatory'=>true, 'maxlength'=>5, 'rgxp'=>'digit', 'tl_class'=>'w50'),
		),
		'expires' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_downloads']['expires'],
			'exclude'					=> true,
			'inputType'					=> 'timePeriod',
			'options'					=> array('minutes', 'hours', 'days', 'weeks', 'months', 'years'),
			'reference'					=> &$GLOBALS['TL_LANG']['tl_iso_downloads'],
			'eval'						=> array('rgxp'=>'digit', 'tl_class'=>'w50'),
		),
	)
);


/**
 * Class tl_iso_downloads
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_iso_downloads extends Backend
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

