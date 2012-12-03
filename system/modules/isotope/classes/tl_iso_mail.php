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
 * Class tl_iso_mail
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_iso_mail extends \Backend
{

	/**
	 * Check permissions to edit table tl_iso_mail
	 * @return void
	 */
	public function checkPermission()
	{
		// Do not run the permission check on other Isotope modules
		if (\Input::get('mod') != 'iso_mail')
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

			if (\Input::get('key') == 'importMail')
			{
				$this->log('Not enough permissions to import mail templates', __METHOD__, TL_ERROR);
				$this->redirect('contao/main.php?act=error');
			}
		}

		// Check current action
		switch (\Input::get('act'))
		{
			case 'create':
			case 'select':
				// Allow
				break;

			case 'edit':
				// Dynamically add the record to the user profile
				if (!in_array(\Input::get('id'), $root))
				{
					$arrNew = $this->Session->get('new_records');

					if (is_array($arrNew['tl_iso_mail']) && in_array(\Input::get('id'), $arrNew['tl_iso_mail']))
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
								$arrAccess[] = \Input::get('id');

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
								$arrAccess[] = \Input::get('id');

								$this->Database->prepare("UPDATE tl_user_group SET iso_mails=? WHERE id=?")
											   ->execute(serialize($arrAccess), $this->User->groups[0]);
							}
						}

						// Add new element to the user object
						$root[] = \Input::get('id');
						$this->User->iso_mails = $root;
					}
				}
				// No break;

			case 'copy':
			case 'delete':
			case 'show':
				if (!in_array(\Input::get('id'), $root) || (\Input::get('act') == 'delete' && !$this->User->hasAccess('delete', 'iso_mailp')))
				{
					$this->log('Not enough permissions to '.\Input::get('act').' mail template ID "'.\Input::get('id').'"', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
				}
				break;

			case 'editAll':
			case 'deleteAll':
			case 'overrideAll':
				$session = $this->Session->getData();
				if (\Input::get('act') == 'deleteAll' && !$this->User->hasAccess('delete', 'iso_mailp'))
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
				if (strlen(\Input::get('act')))
				{
					$this->log('Not enough permissions to '.\Input::get('act').' mail templates', __METHOD__, TL_ERROR);
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

