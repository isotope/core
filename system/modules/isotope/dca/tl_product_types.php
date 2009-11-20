<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 * Copyright (C) 2005 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at http://www.gnu.org/licenses/.
 *
 * PHP version 5
 * @copyright  Winans Creative 2009
 * @author     Fred Bliss <fred@winanscreative.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Table tl_product_types 
 */
$GLOBALS['TL_DCA']['tl_product_types'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'notEditable'				  => false,
		'switchToEdit'                => true,
		'enableVersioning'            => true,
		'onload_callback'             => array
		(
			array('tl_product_types', 'checkPermission')
		)
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 1,
			'fields'                  => array('name'),
			'flag'                    => 1,
			'panelLayout'             => 'filter;search,limit'
		),

		'label' => array
		(
			'fields'                  => array('name'),
			'format'                  => '%s',
		),

		'global_operations' => array
		(
            
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
		),

		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_types']['edit'],
				'href'				  => 'act=edit',
				'icon'                => 'edit.gif',
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_types']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif',
				//'button_callback'			=> array('tl_product_types', 'copyBtn')
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_types']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				//'button_callback'			=> array('tl_product_types', 'deleteBtn'),
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'

			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_types']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			)
		)
	),

	// Palettes
	'palettes' => array
	(
		'default'					  => '{name_legend},name,description;{attributes_legend},attributes;{download_legend:hide},downloads',
	),

	// Fields
	'fields' => array
	(
		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_types']['name'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>61)
		),
		'description' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_types']['description'],
			'exclude'                 => true,
			'inputType'               => 'textarea',
			'eval'                    => array('mandatory'=>false)

		),
        'attributes' => array
		(
			'label'					  => &$GLOBALS['TL_LANG']['tl_product_types']['attributes'],
			'exclude'				  => true,
			'inputType'				  => 'attributeWizard',
			'eval'					  => array('mandatory'=>true),
		),
		'downloads' => array
		(
			'label'					  => &$GLOBALS['TL_LANG']['tl_product_types']['downloads'],
			'exclude'				  => true,
			'inputType'				  => 'checkbox',
			'eval'					  => array(),
		),
	)
);


/**
 * tl_product_types class.
 * 
 * @extends Backend
 */
class tl_product_types extends Backend
{

	/**
	 * Check permissions to edit table tl_product_types.
	 * 
	 * @access public
	 * @return void
	 */
	public function checkPermission()
	{
		$this->import('BackendUser', 'User');

		if ($this->User->isAdmin)
		{
			return;
		}

		// Set root IDs
		if (!is_array($this->User->iso_product_types) || count($this->User->iso_product_types) < 1)
		{
			$this->User->iso_product_types = array(0);
		}

		$GLOBALS['TL_DCA']['tl_product_types']['config']['closed'] = true;
		$GLOBALS['TL_DCA']['tl_product_types']['list']['sorting']['root'] = $this->User->iso_product_types;

		// Check current action
		switch ($this->Input->get('act'))
		{
			case 'select':
				// Allow
				break;

			case 'edit':
			case 'show':
				if (!in_array($this->Input->get('id'), $this->User->iso_product_types))
				{
					$this->log('Not enough permissions to '.$this->Input->get('act').' product type ID "'.$this->Input->get('id').'"', 'tl_product_types checkPermission', 5);
					$this->redirect('typolight/main.php?act=error');
				}
				break;

			case 'editAll':
				$session = $this->Session->getData();
				$session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $this->User->iso_product_types);
				$this->Session->setData($session);
				break;

			default:
				if (strlen($this->Input->get('act')))
				{
					$this->log('Not enough permissions to '.$this->Input->get('act').' product types', 'tl_product_types checkPermission', 5);
					$this->redirect('typolight/main.php?act=error');
				}
				break;
		}
	}
}

