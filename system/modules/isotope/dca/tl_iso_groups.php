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
 * Include the callback provider
 */
require_once(TL_ROOT . '/system/modules/isotope/providers/ProductCallbacks.php');

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
		'default'						=> '{group_legend},name,product_type;',
	),

	// Fields
	'fields' => array
	(
		'name' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_groups']['name'],
			'exclude'					=> true,
			'inputType'					=> 'text',
			'eval'						=> array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
		),
		'product_type' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_groups']['product_type'],
			'exclude'					=> true,
			'inputType'					=> 'select',
			'options_callback'			=> array('ProductCallbacks', 'getProductTypes'),
			'eval'						=> array('includeBlankOption'=>true, 'tl_class'=>'w50')
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
    		$arrGroups = $this->User->iso_groups;

    		if (!is_array($arrGroups) || empty($arrGroups))
    		{
        		$GLOBALS['TL_DCA']['tl_iso_groups']['list']['sorting']['filter'][] = array('id=?', 0);
    		}
    		else
    		{
    			$GLOBALS['TL_DCA']['tl_iso_groups']['list']['sorting']['root'] = $arrGroups;
    		}

			return;
		}

        if (!is_array($this->User->iso_groupp) || empty($this->User->iso_groupp))
		{
			$this->log('Unallowed access to product groups!', __METHOD__, TL_ERROR);
			$this->redirect('contao/main.php?act=error');
		}

		// Set root IDs
		if (!is_array($this->User->iso_groups) || count($this->User->iso_groups) < 1) // Can't use empty() because its an object property (using __get)
		{
			$root = array();
		}
		else
		{
    		try {
        		$root = $this->eliminateNestedPages($this->User->iso_groups, 'tl_iso_groups');
    		}
    		catch (Exception $e) {
        		$root = array();
    		}
		}

		$GLOBALS['TL_DCA']['tl_iso_groups']['list']['sorting']['root'] = (empty($root) ? true : $root);

		if (in_array('rootPaste', $this->User->iso_groupp))
		{
			$GLOBALS['TL_DCA']['tl_iso_groups']['list']['sorting']['rootPaste'] = true;
		}

		// Check permissions to add product group
		if (!in_array('create', $this->User->iso_groupp))
		{
			$GLOBALS['TL_DCA']['tl_iso_groups']['config']['closed'] = true;
		}

		// Check current action
		switch ($this->Input->get('act'))
		{
			case 'create':
			case 'select':
			case 'paste':
				// Allow
				break;

			case 'edit':
				// Dynamically add the record to the user profile
				if (!in_array($this->Input->get('id'), $root))
				{
					$arrNew = $this->Session->get('new_records');

					if (is_array($arrNew['tl_iso_groups']) && in_array($this->Input->get('id'), $arrNew['tl_iso_groups']))
					{
						// Add permissions on user level
						if ($this->User->inherit == 'custom' || !$this->User->groups[0])
						{
							$objUser = $this->Database->prepare("SELECT iso_groups, iso_groupp FROM tl_user WHERE id=?")
													   ->limit(1)
													   ->executeUncached($this->User->id);

							$arrPermissions = deserialize($objUser->iso_groupp);

							if (is_array($arrPermissions) && in_array('create', $arrPermissions))
							{
								$arrAccess = deserialize($objUser->iso_groups);
								$arrAccess[] = $this->Input->get('id');

								$this->Database->prepare("UPDATE tl_user SET iso_groups=? WHERE id=?")
											   ->execute(serialize($arrAccess), $this->User->id);
							}
						}

						// Add permissions on group level
						elseif ($this->User->groups[0] > 0)
						{
							$objGroup = $this->Database->prepare("SELECT iso_groups, iso_groupp FROM tl_user_group WHERE id=?")
													   ->limit(1)
													   ->executeUncached($this->User->groups[0]);

							$arrPermissions = deserialize($objGroup->iso_groupp);

							if (is_array($arrPermissions) && in_array('create', $arrPermissions))
							{
								$arrAccess = deserialize($objGroup->iso_groups);
								$arrAccess[] = $this->Input->get('id');

								$this->Database->prepare("UPDATE tl_user_group SET iso_groups=? WHERE id=?")
											   ->execute(serialize($arrAccess), $this->User->groups[0]);
							}
						}

						// Add new element to the user object
						$root[] = $this->Input->get('id');
						$this->User->iso_groups = $root;
					}
				}
				// No break;

			case 'copy':
			case 'delete':
			case 'show':
				if (!in_array($this->Input->get('id'), $root) || ($this->Input->get('act') == 'delete' && !$this->User->hasAccess('delete', 'iso_groupp')))
				{
					$this->log('Not enough permissions to '.$this->Input->get('act').' group ID "'.$this->Input->get('id').'"', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
				}
				break;

			case 'editAll':
			case 'deleteAll':
			case 'overrideAll':
				$session = $this->Session->getData();
				if ($this->Input->get('act') == 'deleteAll' && !$this->User->hasAccess('delete', 'iso_groupp'))
				{
					$session['CURRENT']['IDS'] = array();
				}
				else
				{
					$session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $root);
				}
				$this->Session->setData($session);
				break;

			default:
				if (strlen($this->Input->get('act')))
				{
					$this->log('Not enough permissions to '.$this->Input->get('act').' groups', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
				}
				break;
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
		if ($dc->table == 'tl_iso_products')
		{
			return $this->generateImage('system/modules/isotope/html/folder-network.png', '', $imageAttribute) . ' <span style="font-weight:bold">' . $label . '</span>';
		}
		else
		{
			$strProductType = '';

			if (($intProductType = IsotopeBackend::getProductTypeForGroup($row['id'])) !== false)
			{
				$strProductType = $this->Database->execute("SELECT name FROM tl_iso_producttypes WHERE id=" . $intProductType)->name;
				$strProductType = ' <span style="color:#b3b3b3; padding-left:3px;">[' . $strProductType . ']</span>';
			}

			return $this->generateImage('system/modules/isotope/html/folder-network.png', '', $imageAttribute) . ' ' . $label . $strProductType;
		}

		return ;
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

