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
 * Table tl_iso_groups
 */
$GLOBALS['TL_DCA']['tl_iso_groups'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'					=> 'Table',
		'label'							=> &$GLOBALS['TL_LANG']['tl_iso_groups']['label'],
		'enableVersioning'				=> true,
		'onload_callback' => array
		(
			array('tl_iso_groups', 'checkPermission'),
		),
		'ondelete_callback' => array
		(
			array('tl_iso_groups', 'deleteGroup'),
		),
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'						=> 5,
			'fields'					=> array('sorting'),
			'flag'						=> 1,
			'icon'						=> 'system/modules/isotope/html/folders.png',
		),
		'label' => array
		(
			'fields'					=> array('name'),
			'format'					=> '%s',
			'label_callback'			=> array('tl_iso_groups', 'addIcon')
		),
		'global_operations' => array
		(
			'all' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'					=> 'act=select',
				'class'					=> 'header_edit_all',
				'attributes'			=> 'onclick="Backend.getScrollOffset();"'
			),
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_groups']['edit'],
				'href'					=> 'table=tl_iso_groups&amp;act=edit',
				'icon'					=> 'edit.gif'
			),
			'copy' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_groups']['copy'],
				'href'					=> 'table=tl_iso_groups&amp;act=paste&amp;mode=copy',
				'icon'					=> 'copy.gif',
				'button_callback'		=> array('tl_iso_groups', 'copyButton'),
			),
			'cut' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_groups']['cut'],
				'href'					=> 'table=tl_iso_groups&amp;act=paste&amp;mode=cut',
				'icon'					=> 'cut.gif',
				'attributes'			=> 'onclick="Backend.getScrollOffset();"'
			),
			'delete' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_groups']['delete'],
				'href'					=> 'table=tl_iso_groups&amp;act=delete',
				'icon'					=> 'delete.gif',
				'attributes'			=> 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
				'button_callback'		=> array('tl_iso_groups', 'deleteButton'),
			),
			'show' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_groups']['show'],
				'href'					=> 'act=show',
				'icon'					=> 'show.gif'
			),
		)
	),

	// Palettes
	'palettes' => array
	(
		'default'						=> '{name_legend},name',
	),

	// Fields
	'fields' => array
	(
		'name' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_groups']['name'],
			'exclude'					=> true,
			'inputType'					=> 'text',
			'eval'						=> array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'long'),
		),
	)
);


/**
 * Class tl_iso_groups
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_iso_groups extends Backend
{

	public function __construct()
	{
		parent::__construct();
		$this->import('BackendUser', 'User');
	}


	/**
	 * Check access permissions
	 */
	public function checkPermission($dc)
	{
		if ($this->User->isAdmin)
		{
			return;
		}

		// Load permissions in tl_iso_products
		if ($dc->table == 'tl_iso_products')
		{
			$GLOBALS['TL_DCA']['tl_iso_groups']['list']['sorting']['root'] = (array) $this->User->iso_groups;
			return;
		}

        if (!is_array($this->User->iso_groupp) || empty($this->User->iso_groupp) || !is_array($this->User->iso_groups) || empty($this->User->iso_groups))
		{
			$this->log('Unallowed access to product groups!', __METHOD__, TL_ERROR);
			$this->redirect('contao/main.php?act=error');
		}

		$GLOBALS['TL_DCA']['tl_iso_groups']['list']['sorting']['root'] = $this->User->iso_groups;

		if (!in_array('create', $this->User->iso_groupp))
		{
			$GLOBALS['TL_DCA']['tl_iso_groups']['config']['closed'] = true;
		}

		// Check permission to delete item
		if ($this->Input->get('act') == 'delete' && !in_array('delete', $this->User->iso_groupp))
		{
			$this->log('User is not allowed to delete groups', __METHOD__, TL_ERROR);
			$this->redirect('contao/main.php?act=error');
		}
	}


	/**
	 * Add an image to each group in the tree
	 * @param array
	 * @param string
	 * @param DataContainer
	 * @param string
	 * @param boolean
	 * @return string
	 * @todo add node filtering
	 */
	public function addIcon($row, $label, DataContainer $dc=null, $imageAttribute='', $blnReturnImage=false)
	{
		$bold = $dc->table == 'tl_iso_products' ? ' style="font-weight:bold"' : '';

		return $this->generateImage('system/modules/isotope/html/folder-network.png', '', $imageAttribute) . ' <span'.$bold.'>' . $label . '</span>';
		//return $this->generateImage('system/modules/isotope/html/folder-network.png', '', $imageAttribute) . ' <a href="' . $this->addToUrl('node='.$row['id']) . '"'.$bold.'>' . $label . '</a>';
	}


	/**
	 * Reassign products to no group when group is deleted
	 * @param object
	 * @return void
	 */
	public function deleteGroup($dc)
	{
		$arrGroups = $this->getChildRecords($dc->id, 'tl_iso_groups');
		$arrGroups[] = $dc->id;

		$this->Database->query("UPDATE tl_iso_products SET gid=0 WHERE gid IN (" . implode(',', $arrGroups) . ")");

		IsotopeBackend::createGeneralGroup();
	}


	/**
	 * Disable copy button if user has no permission to create groups
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function copyButton($row, $href, $label, $title, $icon, $attributes)
	{
		if (!$this->User->isAdmin && (!is_array($this->User->iso_groupp) || !in_array('create', $this->User->iso_groupp)))
		{
			return $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';
		}

		return '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
	}


	/**
	 * Disable delete button if user has no permission to delete groups
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function deleteButton($row, $href, $label, $title, $icon, $attributes)
	{
		if (!$this->User->isAdmin && (!is_array($this->User->iso_groupp) || !in_array('delete', $this->User->iso_groupp)))
		{
			return $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';
		}

		return '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
	}
}

