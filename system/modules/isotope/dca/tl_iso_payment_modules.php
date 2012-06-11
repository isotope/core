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
 * Table tl_iso_payment_modules
 */
$GLOBALS['TL_DCA']['tl_iso_payment_modules'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'enableVersioning'            => true,
		'closed'					  => true,
		'onload_callback'			  => array
		(
			array('IsotopeBackend', 'initializeSetupModule'),
			array('tl_iso_payment_modules', 'checkPermission'),
			array('tl_iso_payment_modules', 'loadShippingModules'),
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
			'panelLayout'             => 'sort,filter;search,limit'
		),
		'label' => array
		(
			'fields'                  => array('name', 'type'),
			'format'                  => '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
			'label_callback'		  => array('IsotopeBackend', 'addPublishIcon'),

		),
		'global_operations' => array
		(
			'back' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['backBT'],
				'href'                => 'mod=&table=',
				'class'               => 'header_back',
				'attributes'          => 'onclick="Backend.getScrollOffset();"',
			),
			'new' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['new'],
				'href'                => 'act=create',
				'class'               => 'header_new',
				'attributes'          => 'onclick="Backend.getScrollOffset();"',
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
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif',
				'button_callback'     => array('tl_iso_payment_modules', 'copyPaymentModule'),
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
				'button_callback'     => array('tl_iso_payment_modules', 'deletePaymentModule'),
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),
		)
	),

	// Palettes
	'palettes' => array
	(
		'__selector__'			=> array('type', 'protected'),
		'default'				=> '{type_legend},name,type',
		'cash'					=> '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,minimum_total,maximum_total,countries,shipping_modules,product_types;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},enabled',
		'paypal'				=> '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,minimum_total,maximum_total,countries,shipping_modules,product_types;{gateway_legend},paypal_account;{price_legend:hide},price,tax_class;{template_legend},button;{expert_legend:hide},guests,protected;{enabled_legend},debug,enabled',
		'paypalpayflowpro'		=> '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,allowed_cc_types,requireCCV,minimum_total,maximum_total,countries,shipping_modules,product_types;{gateway_legend},payflowpro_user,payflowpro_vendor,payflowpro_partner,payflowpro_password,payflowpro_transType;{price_legend:hide},price,tax_class;{template_legend},button;{expert_legend:hide},guests,protected;{enabled_legend},debug,enabled',
		'postfinance'			=> '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,minimum_total,maximum_total,countries,shipping_modules,product_types;{gateway_legend},postfinance_pspid,postfinance_secret,postfinance_method;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},debug,enabled',
		'authorizedotnet'		=> '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,allowed_cc_types,requireCCV,minimum_total,maximum_total,countries,shipping_modules,product_types;{gateway_legend},authorize_login,authorize_trans_key,authorize_trans_type,authorize_delimiter;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},debug,enabled',
		'cybersource'			=> '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,minimum_total,maximum_total,countries,shipping_modules,product_types;{gateway_legend},cybersource_merchant_id,cybersource_trans_key,cybersource_trans_type;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},debug,enabled'
	),

	// Subpalettes
	'subpalettes' => array
	(
		'protected'				=> 'groups',
	),

	// Fields
	'fields' => array
	(
		'type' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['type'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'select',
			'default'				  => 'cash',
			'options_callback'        => array('tl_iso_payment_modules', 'getModules'),
			'reference'               => &$GLOBALS['ISO_LANG']['PAY'],
			'eval'                    => array('helpwizard'=>true, 'submitOnChange'=>true, 'tl_class'=>'clr')
		),
		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['name'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50')
		),
		'label' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['label'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
		),
		'note' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['note'],
			'exclude'                 => true,
			'inputType'               => 'textarea',
			'eval'                    => array('rte'=>'tinyMCE'),
		),
		'new_order_status' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['new_order_status'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => IsotopeBackend::getOrderStatus(),
			'eval'                    => array('mandatory'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50'),
		),
		'price' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['price'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>16, 'rgxp'=>'surcharge', 'tl_class'=>'w50'),
		),
		'tax_class' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['tax_class'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'select',
			'options'                 => IsotopeBackend::getTaxClassesWithSplit(),
			'eval'                    => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
		),
		'allowed_cc_types' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['allowed_cc_types'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'eval'					  => array('multiple'=>true, 'tl_class'=>'clr'),
			'options_callback'		  => array('tl_iso_payment_modules', 'getAllowedCCTypes')
		),
		'trans_type' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['trans_type'],
			'exclude'                 => true,
			'default'				  => 'capture',
			'inputType'				  => 'select',
			'options'				  => array('capture', 'auth'),
			'eval'                    => array('mandatory'=>true, 'tl_class'=>'w50'),
			'reference'				  => $GLOBALS['TL_LANG']['tl_iso_payment_modules'],
		),
		'minimum_total' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['minimum_total'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'default'                 => 0,
			'eval'                    => array('maxlength'=>255, 'rgxp'=>'price', 'tl_class'=>'clr w50'),
		),
		'maximum_total' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['maximum_total'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'default'                 => 0,
			'eval'                    => array('maxlength'=>255, 'rgxp'=>'price', 'tl_class'=>'w50'),
		),
		'countries' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['countries'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => $this->getCountries(),
			'eval'                    => array('multiple'=>true, 'size'=>8, 'tl_class'=>'clr', 'chosen'=>true)
		),
		'shipping_modules' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['shipping_modules'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'eval'                    => array('multiple'=>true, 'size'=>8, 'tl_class'=>'clr', 'chosen'=>true)
		),
		'product_types' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['product_types'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'foreignKey'			  => 'tl_iso_producttypes.name',
			'eval'                    => array('multiple'=>true, 'size'=>8, 'tl_class'=>'clr', 'chosen'=>true)
		),
		'paypal_account' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['paypal_account'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'rgxp'=>'email', 'tl_class'=>'w50'),
		),
		'payflowpro_user' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_user'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
		),
		'payflowpro_vendor' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_vendor'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
		),
		'payflowpro_partner' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_partner'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
		),
		'payflowpro_password' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_password'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'hideInput'=>true),
		),
		'payflowpro_transType' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_transType'],
			'exclude'                 => true,
			'default'				  => 'Sale',
			'inputType'               => 'select',
			'options'				  => array('Sale', 'Authorization'),
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
			'reference'				  => &$GLOBALS['TL_LANG']['tl_payment_module']['payflowpro_transTypes']
		),
		'postfinance_pspid' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['postfinance_pspid'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
		),
		'postfinance_secret' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['postfinance_secret'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
		),
		'postfinance_method' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['postfinance_method'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'default'                 => 'POST',
			'options'                 => array('POST', 'GET'),
			'eval'                    => array('mandatory'=>true, 'tl_class'=>'w50'),
		),
		'authorize_login' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['authorize_login'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255)
		),
		'authorize_trans_key' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['authorize_trans_key'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255)
		),
		'authorize_trans_type' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['authorize_trans_type'],
			'exclude'                 => true,
			'default'				  => 'AUTH_CAPTURE',
			'inputType'               => 'select',
			'options'				  => array('AUTH_CAPTURE', 'AUTH_ONLY'),
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
			'reference'				  => $GLOBALS['TL_LANG']['ISO_PAY']['authorizedotnet']['modes']
		),
		'cybersource_merchant_id' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['cybersource_merchant_id'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255)
		),
		'cybersource_trans_key' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['cybersource_trans_key'],
			'exclude'                 => true,
			'inputType'               => 'textarea',
			'eval'                    => array('mandatory'=>true, 'style'=>'height: 60px;')
		),
		'cybersource_trans_type' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['cybersource_trans_type'],
			'exclude'                 => true,
			'default'				  => 'AUTH_ONLY',
			'inputType'               => 'select',
			'options'				  => array('AUTH_CAPTURE', 'AUTH_ONLY'),
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
			'reference'				  => $GLOBALS['TL_LANG']['ISO_PAY']['authorizedotnet']['modes']
		),
		'authorize_delimiter' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['authorize_delimiter'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>1)
		),
		'requireCCV' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['requireCCV'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
		),
		'button' => array
		(
			'label'					  => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['button'],
			'exclude'				  => true,
			'inputType'				  => 'fileTree',
			'eval'					  => array('fieldType'=>'radio', 'files'=>true, 'filesOnly'=>true, 'extensions'=>'jpg,jpeg,png,gif'),
		),
		'guests' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['guests'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
		),
		'protected' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['protected'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange'=>true)
		),
		'groups' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['groups'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'foreignKey'              => 'tl_member_group.name',
			'eval'                    => array('multiple'=>true)
		),
		'debug' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['debug'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
		),
		'enabled' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['enabled'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
		),
	)
);


/**
 * Class tl_iso_payment_modules
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_iso_payment_modules extends Backend
{

	/**
	 * Check permissions to edit table tl_iso_payment_modules
	 * @return void
	 */
	public function checkPermission()
	{
		// Do not run the permission check on other Isotope modules
		if ($this->Input->get('mod') != 'payment')
		{
			return;
		}

		$this->import('BackendUser', 'User');

		// Return if user is admin
		if ($this->User->isAdmin)
		{
			return;
		}

		// Set root IDs
		if (!is_array($this->User->iso_payment_modules) || count($this->User->iso_payment_modules) < 1)
		{
			$root = array(0);
		}
		else
		{
			$root = $this->User->iso_payment_modules;
		}

		$GLOBALS['TL_DCA']['tl_iso_payment_modules']['list']['sorting']['root'] = $root;

		// Check permissions to add payment modules
		if (!$this->User->hasAccess('create', 'iso_payment_modulep'))
		{
			$GLOBALS['TL_DCA']['tl_iso_payment_modules']['config']['closed'] = true;
			unset($GLOBALS['TL_DCA']['tl_iso_payment_modules']['list']['global_operations']['new']);
		}

		// Check current action
		switch ($this->Input->get('act'))
		{
			case 'create':
			case 'select':
				// Allow
				break;

			case 'edit':
				// Dynamically add the record to the user profile
				if (!in_array($this->Input->get('id'), $root))
				{
					$arrNew = $this->Session->get('new_records');

					if (is_array($arrNew['tl_iso_payment_modules']) && in_array($this->Input->get('id'), $arrNew['tl_iso_payment_modules']))
					{
						// Add permissions on user level
						if ($this->User->inherit == 'custom' || !$this->User->groups[0])
						{
							$objUser = $this->Database->prepare("SELECT iso_payment_modules, iso_payment_modulep FROM tl_user WHERE id=?")
													   ->limit(1)
													   ->execute($this->User->id);

							$arrPermissions = deserialize($objUser->iso_payment_modulep);

							if (is_array($arrPermissions) && in_array('create', $arrPermissions))
							{
								$arrAccess = deserialize($objUser->iso_payment_modules);
								$arrAccess[] = $this->Input->get('id');

								$this->Database->prepare("UPDATE tl_user SET iso_payment_modules=? WHERE id=?")
											   ->execute(serialize($arrAccess), $this->User->id);
							}
						}

						// Add permissions on group level
						elseif ($this->User->groups[0] > 0)
						{
							$objGroup = $this->Database->prepare("SELECT iso_payment_modules, iso_payment_modulep FROM tl_user_group WHERE id=?")
													   ->limit(1)
													   ->execute($this->User->groups[0]);

							$arrPermissions = deserialize($objGroup->iso_payment_modulep);

							if (is_array($arrPermissions) && in_array('create', $arrPermissions))
							{
								$arrAccess = deserialize($objGroup->iso_payment_modules);
								$arrAccess[] = $this->Input->get('id');

								$this->Database->prepare("UPDATE tl_user_group SET iso_payment_modules=? WHERE id=?")
											   ->execute(serialize($arrAccess), $this->User->groups[0]);
							}
						}

						// Add new element to the user object
						$root[] = $this->Input->get('id');
						$this->User->iso_payment_modules = $root;
					}
				}
				// No break;

			case 'copy':
			case 'delete':
			case 'show':
				if (!in_array($this->Input->get('id'), $root) || ($this->Input->get('act') == 'delete' && !$this->User->hasAccess('delete', 'iso_payment_modulep')))
				{
					$this->log('Not enough permissions to '.$this->Input->get('act').' payment module ID "'.$this->Input->get('id').'"', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
				}
				break;

			case 'editAll':
			case 'deleteAll':
			case 'overrideAll':
				$session = $this->Session->getData();
				if ($this->Input->get('act') == 'deleteAll' && !$this->User->hasAccess('delete', 'iso_payment_modulep'))
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
					$this->log('Not enough permissions to '.$this->Input->get('act').' payment modules', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
				}
				break;
		}
	}
	

	/**
	 * Get allowed CC types and return them as array
	 * @param DataContainer
	 * @return array
	 */
	public function getAllowedCCTypes(DataContainer $dc)
	{
		$objModuleType = $this->Database->prepare("SELECT * FROM tl_iso_payment_modules WHERE id=?")->limit(1)->execute($dc->id);

		if (!$objModuleType->numRows)
		{
			return array();
		}

		$strClass = $GLOBALS['ISO_PAY'][$objModuleType->type];

		if (!strlen($strClass) || !$this->classFileExists($strClass))
		{
			return array();
		}

		$arrCCTypes = array();
		$objModule = new $strClass($objModuleType->row());

		foreach ($objModule->getAllowedCCTypes() as $type)
		{
			$arrCCTypes[$type] = $GLOBALS['ISO_LANG']['CCT'][$type];
		}

		return $arrCCTypes;
	}


	/**
	 * Return a list of all payment modules available
	 * @return array
	 */
	public function getModules()
	{
		$arrModules = array();

		if (is_array($GLOBALS['ISO_PAY']) && count($GLOBALS['ISO_PAY']))
		{
			foreach ($GLOBALS['ISO_PAY'] as $module => $class)
			{
				$arrModules[$module] = (strlen($GLOBALS['ISO_LANG']['PAY'][$module][0]) ? $GLOBALS['ISO_LANG']['PAY'][$module][0] : $module);
			}
		}

		return $arrModules;
	}


	/**
	 * Load shipping modules into the DCA (options_callback would not work due to numeric array keys)
	 * @param object
	 * @return void
	 */
	public function loadShippingModules($dc)
	{
		$arrModules = array(-1=>$GLOBALS['TL_LANG']['tl_iso_payment_modules']['no_shipping']);
		$objShippings = $this->Database->execute("SELECT * FROM tl_iso_shipping_modules ORDER BY name");

		while ($objShippings->next())
		{
			$arrModules[$objShippings->id] = $objShippings->name;
		}

		$GLOBALS['TL_DCA']['tl_iso_payment_modules']['fields']['shipping_modules']['options'] = array_keys($arrModules);
		$GLOBALS['TL_DCA']['tl_iso_payment_modules']['fields']['shipping_modules']['reference'] = $arrModules;
	}


	/**
	 * Return the copy payment module button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function copyPaymentModule($row, $href, $label, $title, $icon, $attributes)
	{
		return ($this->User->isAdmin || $this->User->hasAccess('create', 'iso_payment_modulep')) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
	}


	/**
	 * Return the delete payment module button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function deletePaymentModule($row, $href, $label, $title, $icon, $attributes)
	{
		return ($this->User->isAdmin || $this->User->hasAccess('delete', 'iso_payment_modulep')) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
	}
}

