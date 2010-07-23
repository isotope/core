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


/**
 * Table tl_iso_registry
 */
$GLOBALS['TL_DCA']['tl_iso_registry'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'closed'					  => true,
		'ctable'                      => array('tl_iso_registry_items'),
		'onload_callback' => array
		(
			array('tl_iso_registry', 'checkPermission'),
		),
		'ondelete_callback'			  => array
		(
			array('tl_iso_registry', 'archiveRecord'),
		),
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
			'format'                  => '%s'
		),
		'global_operations' => array
		(
			'back' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['MSC']['backBT'],
				'href'					=> 'table=',
				'class'					=> 'header_back',
				'attributes'			=> 'onclick="Backend.getScrollOffset();"',
			),
			'new' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_registry']['new'],
				'href'					=> 'act=create',
				'class'					=> 'header_new',
				'attributes'			=> 'onclick="Backend.getScrollOffset();"',
			),
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_registry']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_registry']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_registry']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_registry']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			)
		)
	),

	// Palettes
	'palettes' => array
	(
		'default'                     => '{name_legend},name,second_party_name;{rate_legend:hide},includes,label,rates',
	),

	// Fields
	'fields' => array
	(
		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_registry']['name'],
			'search'                  => true,
			'exclude'				  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'mandatory'=>true),
		),
		'second_party_name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_registry']['second_party_name'],
			'search'                  => true,
			'exclude'				  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255),
		),
		'date' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_registry']['date'],
			'filter'                  => true,
			'exclude'				  => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'date', 'mandatory'=>true, 'datepicker'=>$this->getDatePickerString(), 'tl_class'=>'w50 wizard')
		),
		'event_type' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_registry']['event_type'],
			'inputType'				  => 'select',
			'options'				  => array('wedding', 'bar_mitzvah', 'bat_mitzvah'),
			'reference'				  => &$GLOBALS['TL_LANG']['tl_iso_registry'],
			'eval'					  => array('mandatory'=>true, 'includeBlankOption'=>true),
		),
		'description' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_registry']['description'],
			'exclude'				  => true,
			'inputType'               => 'textarea',
		),
	)
);


class tl_iso_registry extends Backend
{
	
	public function checkPermission($dc)
	{
		if (strlen($this->Input->get('act')))
		{
			$GLOBALS['TL_DCA']['tl_iso_registry']['config']['closed'] = false;
		}
		
		// Hide archived (used and deleted) tax classes
		$arrModules = $this->Database->execute("SELECT id FROM tl_iso_registry WHERE archive<2")->fetchEach('id');
		
		if (!count($arrModules))
		{
			$arrModules = array(0);
		}

		$GLOBALS['TL_DCA']['tl_iso_registry']['list']['sorting']['root'] = $arrModules;

		// Check current action
		switch ($this->Input->get('act'))
		{
			case 'edit':
			case 'copy':
			case 'delete':
			case 'show':
				if (!in_array($this->Input->get('id'), $arrModules))
				{
					$this->log('Not enough permissions to '.$this->Input->get('act').' tax class ID "'.$this->Input->get('id').'"', 'tl_iso_registry checkPermission()', TL_ACCESS);
					$this->redirect('typolight/main.php?act=error');
				}
				break;

			case 'editAll':
			case 'copyAll':
			case 'deleteAll':
				$session = $this->Session->getData();
				$session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $arrModules);
				$this->Session->setData($session);
				break;
		}
	}
	
	
	/**
	 * Record is deleted, archive if necessary
	 */
	public function archiveRecord($dc)
	{
	}
	

}

