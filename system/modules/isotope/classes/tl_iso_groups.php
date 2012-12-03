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
 * Class tl_iso_groups
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_iso_groups extends \Backend
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
		if (\Input::get('act') == 'delete' && !in_array('delete', $this->User->iso_groupp))
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
	public function addIcon($row, $label, \DataContainer $dc=null, $imageAttribute='', $blnReturnImage=false)
	{
		$bold = $dc->table == 'tl_iso_products' ? ' style="font-weight:bold"' : '';

		return $this->generateImage('system/modules/isotope/assets/folder-network.png', '', $imageAttribute) . ' <span'.$bold.'>' . $label . '</span>';
		//return $this->generateImage('system/modules/isotope/assets/folder-network.png', '', $imageAttribute) . ' <a href="' . $this->addToUrl('node='.$row['id']) . '"'.$bold.'>' . $label . '</a>';
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

		\Isotope\Backend::createGeneralGroup();
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

