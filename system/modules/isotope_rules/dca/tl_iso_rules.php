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
 * Table tl_iso_rules
 */
$GLOBALS['TL_DCA']['tl_iso_rules'] = array
(
	// Config
	'config' => array
	(
		'dataContainer'					=> 'Table',
		'ctable'						=> array('tl_iso_rule_restrictions'),
		'enableVersioning'				=> false,
		'onload_callback' => array
		(
			array('IsotopeBackend', 'hideArchivedRecords'),
		),
	),
	
	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'						=> 1,
			'panelLayout'				=> 'filter;search,limit',
			'fields'					=> array('type', 'name'),
		),
		'label'	  => array
		(
			'fields'					=> array('name', 'code'),
			'label'						=> '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_rules']['edit'],
				'href'					=> 'act=edit',
				'icon'					=> 'edit.gif',
				'button_callback'		=> array('IsotopeBackend', 'disableArchivedRecord'),
			),
			'copy' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_rules']['copy'],
				'href'					=> 'act=copy',
				'icon'					=> 'copy.gif'
			),
			'delete' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_rules']['delete'],
				'href'					=> 'key=delete',
				'icon'					=> 'delete.gif',
				'attributes'			=> 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'toggle' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_page']['toggle'],
				'icon'                => 'visible.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset(); return AjaxRequest.toggleVisibility(this, %s);"',
				'button_callback'     => array('tl_iso_rules', 'toggleIcon'),
			),
			'show' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_rules']['show'],
				'href'					=> 'act=show',
				'icon'					=> 'show.gif'
			)
		)
	),
	
	// Palettes
	'palettes' => array
	(
		'__selector__'			=> array('type', 'enableCode', 'configRestrictions', 'memberRestrictions', 'productRestrictions'),
		'default'				=> '{basic_legend},type',
		'product'				=> '{basic_legend},type,name,discount;{limit_legend:hide},limitPerMember,limitPerConfig;{datim_legend:hide},startDate,endDate,startTime,endTime;{advanced_legend:hide},configRestrictions,memberRestrictions,productRestrictions,minItemQuantity,maxItemQuantity;{enabled_legend},enabled',
		'cart'					=> '{basic_legend},type,name,label,discount,applyTo;{coupon_legend:hide},enableCode;{limit_legend:hide},limitPerMember,limitPerConfig;{datim_legend:hide},startDate,endDate,startTime,endTime;{advanced_legend:hide},configRestrictions,memberRestrictions,productRestrictions,minItemQuantity,maxItemQuantity;{enabled_legend},enabled'
	),
	'subpalettes' => array
	(
		'enableCode'						=> 'code',
		'configRestrictions'				=> 'configs',
		'memberRestrictions_groups'			=> 'groups',
		'memberRestrictions_members'		=> 'members',
		'productRestrictions_producttypes'	=> 'producttypes',
		'productRestrictions_pages'			=> 'pages',
		'productRestrictions_products'		=> 'products',
	),
	
	// Fields
	'fields' => array
	(
		'type' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rules']['type'],
			'exclude'					=> true,
			'filter'					=> true,
			'default'					=> 'product',
			'inputType'					=> 'select',
			'options'					=> array('product', 'cart'),
			'reference'					=> &$GLOBALS['TL_LANG']['tl_iso_rules']['type'],
			'eval'						=> array('mandatory'=>true, 'submitOnChange'=>true),
		),
		'name' => array
        (
            'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rules']['name'],
            'exclude'					=> true,
            'search'					=> true,
            'inputType'					=> 'text',
            'eval'						=> array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50')
        ),
		'label' => array
        (
            'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rules']['label'],
            'exclude'					=> true,
            'search'					=> true,
            'inputType'					=> 'text',
            'eval'						=> array('maxlength'=>255, 'tl_class'=>'w50')
        ),
		'discount' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rules']['discount'],
			'exclude'					=> true,
			'search'					=> true,
			'inputType'					=> 'text',
			'eval'						=> array('mandatory'=>true, 'maxlength'=>16, 'rgxp'=>'discount', 'tl_class'=>'w50')
		),
		'applyTo' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rules']['applyTo'],
			'exclude'					=> true,
			'default'					=> 'product',
			'inputType'					=> 'select',
			'options'					=> array('product', 'item', 'cart'),
			'reference'					=> &$GLOBALS['TL_LANG']['tl_iso_rules']['applyTo'],
			'eval'						=> array('mandatory'=>true, 'tl_class'=>'w50')
		),
		'enableCode' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rules']['enableCode'],
			'exclude'					=> true,
			'filter'					=> true,
			'inputType'					=> 'checkbox',
			'eval'						=> array('submitOnChange'=>true)
		),
		'code' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rules']['code'],
			'exclude'					=> true,
			'search'					=> true,
			'inputType'					=> 'text',
			'eval'						=> array('mandatory'=>true, 'maxlength'=>255)
		),
		'limitPerMember' => array
        (
            'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rules']['limitPerMember'],
            'exclude'					=> true,
			'inputType'					=> 'text',
            'eval'						=> array('rgxp'=>'digit', 'maxlength'=>10, 'tl_class'=>'w50'),
	   	),
	   	'limitPerConfig' => array
        (
            'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rules']['limitPerConfig'],
            'exclude'					=> true,
			'inputType'					=> 'text',
            'eval'						=> array('rgxp'=>'digit', 'maxlength'=>10, 'tl_class'=>'w50'),
	   	),
		'startDate' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rules']['startDate'],
			'exclude'					=> true,
			'inputType'					=> 'text',
			'eval'						=> array('rgxp'=>'date', 'datepicker'=>$this->getDatePickerString(), 'tl_class'=>'w50 wizard')
		),
		'endDate' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rules']['endDate'],
			'exclude'					=> true,
			'inputType'					=> 'text',
			'eval'						=> array('rgxp'=>'date', 'datepicker'=>$this->getDatePickerString(), 'tl_class'=>'w50 wizard')
		),
		'startTime' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rules']['startTime'],
			'exclude'					=> true,
			'inputType'					=> 'text',
			'eval'						=> array('rgxp'=>'time', 'tl_class'=>'w50')
		),
		'endTime' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rules']['endTime'],
			'exclude'					=> true,
			'inputType'					=> 'text',
			'eval'						=> array('rgxp'=>'time', 'tl_class'=>'w50'),
		),
        'configRestrictions' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rules']['configRestrictions'],
			'inputType'					=> 'checkbox',
			'exclude'					=> true,
			'filter'					=> true,
			'eval'						=> array('submitOnChange'=>true, 'tl_class'=>'clr'),
		),
		'configs' => array
        (
            'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rules']['configs'],
            'exclude'					=> true,
			'inputType'					=> 'checkbox',
			'foreignKey'				=> 'tl_iso_config.name',
			'eval'						=> array('mandatory'=>true, 'multiple'=>true, 'doNotSaveEmpty'=>true),
			'load_callback' => array
			(
				array('tl_iso_rules', 'loadRestrictions'),
			),
			'save_callback' => array
			(
				array('tl_iso_rules', 'saveRestrictions'),
			),
        ),	
        'memberRestrictions' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rules']['memberRestrictions'],
			'inputType'					=> 'radio',
			'default'					=> 'none',
			'exclude'					=> true,
			'filter'					=> true,
			'options'					=> array('none', 'guest', 'groups', 'members'),
			'eval'						=> array('submitOnChange'=>true, 'tl_class'=>'clr'),
			'reference'					=> &$GLOBALS['TL_LANG']['tl_iso_rules']['memberRestrictions']
		),
		'groups' => array
        (
            'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rules']['groups'],
            'exclude'					=> true,
			'inputType'					=> 'checkbox',
			'foreignKey'				=> 'tl_member_group.name',
			'eval'						=> array('mandatory'=>true, 'multiple'=>true, 'doNotSaveEmpty'=>true),
			'load_callback' => array
			(
				array('tl_iso_rules', 'loadRestrictions'),
			),
			'save_callback' => array
			(
				array('tl_iso_rules', 'saveRestrictions'),
			),
        ),		
		'members' => array
        (
            'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rules']['members'],
            'exclude'					=> true,
            'inputType'					=> 'tableLookup',
			'eval' => array
			(
				'mandatory'				=> true,
				'doNotSaveEmpty'		=> true,
				'tl_class'				=> 'clr',
				'foreignTable'			=> 'tl_member',
				'fieldType'				=> 'checkbox',
				'listFields'			=> array('firstname', 'lastname', 'username', 'email'),
				'searchFields'			=> array('firstname', 'lastname', 'username', 'email'),
				'sqlWhere'				=> '',
				'searchLabel'			=> 'Search members',
			),
			'load_callback' => array
			(
				array('tl_iso_rules', 'loadRestrictions'),
			),
			'save_callback' => array
			(
				array('tl_iso_rules', 'saveRestrictions'),
			),
        ),
        'productRestrictions' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rules']['productRestrictions'],
			'inputType'					=> 'radio',
			'default'					=> 'none',
			'exclude'					=> true,
			'filter'					=> true,
			'options'					=> array('none', 'producttypes', 'pages', 'products'),
			'eval'						=> array('submitOnChange'=>true, 'tl_class'=>'clr'),
			'reference'					=> &$GLOBALS['TL_LANG']['tl_iso_rules']['productRestrictions']
		),
        'producttypes' => array
        (
            'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rules']['producttypes'],
            'exclude'					=> true,
			'inputType'					=> 'checkbox',
			'foreignKey'				=> 'tl_iso_producttypes.name',
			'eval'						=> array('mandatory'=>true, 'multiple'=>true, 'doNotSaveEmpty'=>true),
			'load_callback' => array
			(
				array('tl_iso_rules', 'loadRestrictions'),
			),
			'save_callback' => array
			(
				array('tl_iso_rules', 'saveRestrictions'),
			),
        ),
		'pages' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rules']['pages'],
			'exclude'					=> true,
			'inputType'					=> 'pageTree',
			'foreignKey'				=> 'tl_page.title',
			'eval'						=> array('mandatory'=>true, 'multiple'=>true, 'fieldType'=>'checkbox', 'doNotSaveEmpty'=>true),
			'load_callback' => array
			(
				array('tl_iso_rules', 'loadRestrictions'),
			),
			'save_callback' => array
			(
				array('tl_iso_rules', 'saveRestrictions'),
			),
		),
		'products' 	=> array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rules']['products'],
			'exclude'					=> true,
			'inputType'					=> 'tableLookup',
			'eval' => array
			(
				'mandatory'				=> true,
				'doNotSaveEmpty'		=> true,
				'tl_class'				=> 'clr',
				'foreignTable'			=> 'tl_iso_products',
				'fieldType'				=> 'checkbox',
				'listFields'			=> array('type'=>'(SELECT name FROM tl_iso_producttypes WHERE tl_iso_products.type=tl_iso_producttypes.id)', 'name', 'sku'),
				'searchFields'			=> array('name', 'alias', 'sku', 'description'),
				'sqlWhere'				=> 'pid=0',
				'searchLabel'			=> 'Search products',
			),
			'load_callback' => array
			(
				array('tl_iso_rules', 'loadRestrictions'),
			),
			'save_callback' => array
			(
				array('tl_iso_rules', 'saveRestrictions'),
			),
		),
	  	'minItemQuantity' => array
      	(
            'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rules']['minItemQuantity'],
            'exclude'					=> true,
            'inputType'					=> 'text',
            'eval'						=> array('maxlength'=>10, 'rgxp'=>'digit', 'tl_class'=>'w50')
      	),
	  	'maxItemQuantity' => array
      	(
            'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rules']['maxItemQuantity'],
            'exclude'					=> true,
            'inputType'					=> 'text',
            'eval'						=> array('maxlength'=>10, 'rgxp'=>'digit', 'tl_class'=>'w50')
      	),
		'enabled'	=> array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rules']['enabled'],
			'inputType'					=> 'checkbox',
			'exclude'					=> true,
			'filter'					=> true,
		)
	)
);


class tl_iso_rules extends Backend
{
	
	public function __construct()
	{
		parent::__construct();
		
		$this->import('BackendUser', 'User');
	}
	
	/**
	 * Return an array of enabled rules but not the active one.
	 */
	public function getRules($dc)
	{
		$arrRules = array();
		$objRules = $this->Database->execute("SELECT * FROM tl_iso_rules WHERE enabled='1' AND id!={$dc->id}");
		
		while( $objRules->next() )
		{
			$arrRules[$objRules->id] = $objRules->name;
		}
		
		return $arrRules;
	}
	

	/**
	 * Load rule restrictions from linked table
	 */
	public function loadRestrictions($varValue, $dc)
	{
		return $this->Database->execute("SELECT object_id FROM tl_iso_rule_restrictions WHERE pid={$dc->activeRecord->id} AND type='{$dc->field}'")->fetchEach('object_id');
	}
	
	
	/**
	 * Save rule restrictions to linked table. Only update what necessary to prevent the IDs from increasing on every save_callback
	 */
	public function saveRestrictions($varValue, $dc)
	{
		$arrNew = deserialize($varValue);
		
		if (!is_array($arrNew) || !count($arrNew))
		{
			$this->Database->query("DELETE FROM tl_iso_rule_restrictions WHERE pid={$dc->activeRecord->id} AND type='{$dc->field}'");
		}
		else
		{
			$arrOld = $this->Database->execute("SELECT object_id FROM tl_iso_rule_restrictions WHERE pid={$dc->activeRecord->id} AND type='{$dc->field}'")->fetchEach('object_id');
			
			$arrInsert = array_diff($arrNew, $arrOld);
			$arrDelete = array_diff($arrOld, $arrNew);
			
			if (count($arrDelete))
			{
				$this->Database->query("DELETE FROM tl_iso_rule_restrictions WHERE pid={$dc->activeRecord->id} AND type='{$dc->field}' AND object_id IN (" . implode(',', $arrDelete) . ")");
			}
			
			if (count($arrInsert))
			{
				$time = time();
				$this->Database->query("INSERT INTO tl_iso_rule_restrictions (pid,tstamp,type,object_id) VALUES ({$dc->id}, $time, '{$dc->field}', " . implode("), ({$dc->id}, $time, '{$dc->field}', ", $arrInsert) . ")");
			}
		}
		
		return '';
	}
	
	
	/**
	 * Return the "toggle visibility" button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
	{
		if (strlen($this->Input->get('tid')))
		{
			$this->toggleVisibility($this->Input->get('tid'), ($this->Input->get('state') == 1));
			$this->redirect($this->getReferer());
		}

		// Check permissions AFTER checking the tid, so hacking attempts are logged
		if (!$this->User->isAdmin && !$this->User->hasAccess('tl_iso_rules::enabled', 'alexf') || $row['archive'] > 0)
		{
			return $this->generateImage($icon, $label).' ';
		}

		$href .= '&amp;tid='.$row['id'].'&amp;state='.($row['enabled'] ? '' : 1);

		if (!$row['enabled'])
		{
			$icon = 'invisible.gif';
		}		

		return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
	}


	/**
	 * Disable/enable a user group
	 * @param integer
	 * @param boolean
	 */
	public function toggleVisibility($intId, $blnVisible)
	{
//		// Check permissions to edit
//		$this->Input->setGet('id', $intId);
//		$this->Input->setGet('act', 'toggle');
//		$this->checkPermission();

		// Check permissions to publish
		if (!$this->User->isAdmin && !$this->User->hasAccess('tl_iso_rules::enabled', 'alexf'))
		{
			$this->log('Not enough permissions to enable/disable rule ID "'.$intId.'"', 'tl_iso_rules toggleVisibility', TL_ERROR);
			$this->redirect('contao/main.php?act=error');
		}

//		$this->createInitialVersion('tl_iso_rules', $intId);
	
		// Trigger the save_callback
		if (is_array($GLOBALS['TL_DCA']['tl_iso_rules']['fields']['enabled']['save_callback']))
		{
			foreach ($GLOBALS['TL_DCA']['tl_iso_rules']['fields']['enabled']['save_callback'] as $callback)
			{
				$this->import($callback[0]);
				$blnVisible = $this->$callback[0]->$callback[1]($blnVisible, $this);
			}
		}

		// Update the database
		$this->Database->prepare("UPDATE tl_iso_rules SET tstamp=". time() .", enabled='" . ($blnVisible ? 1 : '') . "' WHERE id=?")
					   ->execute($intId);

//		$this->createNewVersion('tl_iso_rules', $intId);
	}
}

