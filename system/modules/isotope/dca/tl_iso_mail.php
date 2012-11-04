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


/**
 * Table tl_iso_mail
 */
$GLOBALS['TL_DCA']['tl_iso_mail'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'				=> 'Table',
		'enableVersioning'			=> true,
		'closed'					=> true,
		'switchToEdit'				=> true,
		'ctable'					=> array('tl_iso_mail_content'),
		'onload_callback' => array
		(
			array('IsotopeBackend', 'initializeSetupModule'),
			array('tl_iso_mail', 'checkPermission'),
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
			'fields'                  => array('name', 'sender'),
			'format'                  => '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
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
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_mail']['new'],
				'href'                => 'act=create',
				'class'               => 'header_new',
				'attributes'          => 'onclick="Backend.getScrollOffset();"',
			),
			'importMail' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_mail']['importMail'],
				'href'                => 'key=importMail',
				'class'               => 'header_import_mail',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
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
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_mail']['edit'],
				'href'                => 'table=tl_iso_mail_content',
				'icon'                => 'edit.gif',
				'attributes'          => 'class="contextmenu"'
			),
			'editheader' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_mail']['editheader'],
				'href'                => 'act=edit',
				'icon'                => 'header.gif',
				'attributes'          => 'class="edit-header"'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_mail']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif',
				'button_callback'     => array('tl_iso_mail', 'copyMail'),
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_mail']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
				'button_callback'     => array('tl_iso_mail', 'deleteMail'),
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_mail']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),
			'exportMail' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_mail']['exportMail'],
				'href'                => 'key=exportMail',
				'icon'                => 'system/modules/isotope/html/drive-download.png'
			)
		)
	),

	// Palettes
	'palettes' => array
	(
		'__selector__'				  => array('attachDocument'),
		'default'                     => '{name_legend},name;{address_legend},senderName,sender,cc,bcc;{document_legend:hide},attachDocument;{expert_legend:hide},template,priority',
	),

	// Subpalettes
	'subpalettes' => array
	(
		'attachDocument'			  => 'documentTemplate,documentTitle',
	),

	// Fields
	'fields' => array
	(
		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_mail']['name'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'long'),
		),
		'senderName' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_mail']['senderName'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
		),
		'sender' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_mail']['sender'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'rgxp'=>'email', 'tl_class'=>'w50'),
		),
		'cc' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_mail']['cc'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'rgxp'=>'extnd', 'tl_class'=>'w50'),
		),
		'bcc' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_mail']['bcc'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'rgxp'=>'extnd', 'tl_class'=>'w50'),
		),
		'template' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_mail']['template'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'default'                 => 'mail_default',
			'options'                 => IsotopeBackend::getTemplates('mail_'),
			'eval'                    => array('mandatory'=>true, 'tl_class'=>'w50', 'chosen'=>true)
		),
		'priority' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_mail']['priority'],
			'exclude'					=> true,
			'inputType'					=> 'select',
			'options'					=> array(1,2,3,4,5),
			'default'					=> 3,
			'reference'					=> &$GLOBALS['TL_LANG']['tl_iso_mail']['priority_ref'],
			'eval'						=> array('tl_class'=>'w50'),
		),
		'attachDocument' => array
		(
			'label'					  => &$GLOBALS['TL_LANG']['tl_iso_mail']['attachDocument'],
			'exclude'                 => true,
			'inputType'				  => 'checkbox',
			'eval'					  => array('submitOnChange'=>true, 'tl_class'=>'clr'),
		),
		'documentTemplate'	=> array
		(
			'label'					  => &$GLOBALS['TL_LANG']['tl_iso_mail']['documentTemplate'],
			'exclude'                 => true,
			'inputType'				  => 'select',
			'options'				  => IsotopeBackend::getTemplates('iso_invoice'),
			'eval'					  => array('includeBlankOption'=>true, 'tl_class'=>'w50', 'chosen'=>true)
		),
		'documentTitle'		=> array
		(
			'label'					  => &$GLOBALS['TL_LANG']['tl_iso_mail']['documentTitle'],
			'exclude'                 => true,
			'inputType'				  => 'text',
			'eval'					  => array('mandatory'=>true, 'maxlength'=>255, 'decodeEntities'=>true, 'tl_class'=>'w50'),
		),
		'source' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_mail']['source'],
			'eval'                    => array('fieldType'=>'checkbox', 'files'=>true, 'filesOnly'=>true, 'extensions'=>'imt', 'class'=>'mandatory')
		),
	)
);


/**
 * Class tl_iso_mail
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_iso_mail extends Backend
{

	/**
	 * Check permissions to edit table tl_iso_mail
	 * @return void
	 */
	public function checkPermission()
	{
		// Do not run the permission check on other Isotope modules
		if ($this->Input->get('mod') != 'iso_mail')
		{
			return;
		}

		$this->import('BackendUser', 'User');

		// Return if admin is user
		if ($this->User->isAdmin)
		{
			return;
		}

		// Set root IDs
		if (!is_array($this->User->iso_mails) || count($this->User->iso_mails) < 1) // Can't use empty() because its an object property (using __get)
		{
			$root = array(0);
		}
		else
		{
			$root = $this->User->iso_mails;
		}

		$GLOBALS['TL_DCA']['tl_iso_mail']['list']['sorting']['root'] = $root;

		// Check permissions to add mail templates
		if (!$this->User->hasAccess('create', 'iso_mailp'))
		{
			$GLOBALS['TL_DCA']['tl_iso_mail']['config']['closed'] = true;
			unset($GLOBALS['TL_DCA']['tl_iso_mail']['list']['global_operations']['new']);
			unset($GLOBALS['TL_DCA']['tl_iso_mail']['list']['global_operations']['importMail']);

			if ($this->Input->get('key') == 'importMail')
			{
				$this->log('Not enough permissions to import mail templates', __METHOD__, TL_ERROR);
				$this->redirect('contao/main.php?act=error');
			}
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

					if (is_array($arrNew['tl_iso_mail']) && in_array($this->Input->get('id'), $arrNew['tl_iso_mail']))
					{
						// Add permissions on user level
						if ($this->User->inherit == 'custom' || !$this->User->groups[0])
						{
							$objUser = $this->Database->prepare("SELECT iso_mails, iso_mailp FROM tl_user WHERE id=?")
													   ->limit(1)
													   ->execute($this->User->id);

							$arrPermissions = deserialize($objUser->iso_mailp);

							if (is_array($arrPermissions) && in_array('create', $arrPermissions))
							{
								$arrAccess = deserialize($objUser->iso_mails);
								$arrAccess[] = $this->Input->get('id');

								$this->Database->prepare("UPDATE tl_user SET iso_mails=? WHERE id=?")
											   ->execute(serialize($arrAccess), $this->User->id);
							}
						}

						// Add permissions on group level
						elseif ($this->User->groups[0] > 0)
						{
							$objGroup = $this->Database->prepare("SELECT iso_mails, iso_mailp FROM tl_user_group WHERE id=?")
													   ->limit(1)
													   ->execute($this->User->groups[0]);

							$arrPermissions = deserialize($objGroup->iso_mailp);

							if (is_array($arrPermissions) && in_array('create', $arrPermissions))
							{
								$arrAccess = deserialize($objGroup->iso_mails);
								$arrAccess[] = $this->Input->get('id');

								$this->Database->prepare("UPDATE tl_user_group SET iso_mails=? WHERE id=?")
											   ->execute(serialize($arrAccess), $this->User->groups[0]);
							}
						}

						// Add new element to the user object
						$root[] = $this->Input->get('id');
						$this->User->iso_mails = $root;
					}
				}
				// No break;

			case 'copy':
			case 'delete':
			case 'show':
				if (!in_array($this->Input->get('id'), $root) || ($this->Input->get('act') == 'delete' && !$this->User->hasAccess('delete', 'iso_mailp')))
				{
					$this->log('Not enough permissions to '.$this->Input->get('act').' mail template ID "'.$this->Input->get('id').'"', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
				}
				break;

			case 'editAll':
			case 'deleteAll':
			case 'overrideAll':
				$session = $this->Session->getData();
				if ($this->Input->get('act') == 'deleteAll' && !$this->User->hasAccess('delete', 'iso_mailp'))
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
					$this->log('Not enough permissions to '.$this->Input->get('act').' mail templates', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
				}
				break;
		}
	}


	/**
	 * Return the copy mail button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function copyMail($row, $href, $label, $title, $icon, $attributes)
	{
		return ($this->User->isAdmin || $this->User->hasAccess('create', 'iso_mailp')) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
	}


	/**
	 * Return the delete mail button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function deleteMail($row, $href, $label, $title, $icon, $attributes)
	{
		return ($this->User->isAdmin || $this->User->hasAccess('delete', 'iso_mailp')) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
	}
}

