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
 * @copyright  Isotope eCommerce Workgroup 2009-2011
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_mail']['name']				= array('Name', 'Please enter a name for the email. Used as a reference in the system.');
$GLOBALS['TL_LANG']['tl_iso_mail']['senderName']		= array('Sender Name', 'Enter the name of the sender.');
$GLOBALS['TL_LANG']['tl_iso_mail']['sender']			= array('Sender Email', 'Enter the e-mail address of the sender. The recipient will reply to this address.');
$GLOBALS['TL_LANG']['tl_iso_mail']['cc']				= array('Send a CC to', 'Recipients that should receive a carbon copy of the mail. Separate multiple addresses with a comma.');
$GLOBALS['TL_LANG']['tl_iso_mail']['bcc']				= array('Send a BCC to', 'Recipients that should receive a blind carbon copy of the mail. Separate multiple addresses with a comma.');
$GLOBALS['TL_LANG']['tl_iso_mail']['template']			= array('Email Template', 'Here you can select an HTML e-mail template to use.');
$GLOBALS['TL_LANG']['tl_iso_mail']['priority']			= array('Priority', 'Please select a priority.');
$GLOBALS['TL_LANG']['tl_iso_mail']['attachDocument']	= array('Attach an order document', 'Allows you to generate an additional document as a PDF attachment for this email.');
$GLOBALS['TL_LANG']['tl_iso_mail']['documentTemplate']	= array('Document template', 'Select an document template to override the default collection template.');
$GLOBALS['TL_LANG']['tl_iso_mail']['documentTitle']		= array('Document title', 'Please specify a title for the attached document.');
$GLOBALS['TL_LANG']['tl_iso_mail']['source']			= array('Source files', 'Please choose one or more .imt files from the files directory.');


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_mail']['new']			= array('New Template', 'Create a new e-mail template');
$GLOBALS['TL_LANG']['tl_iso_mail']['edit']			= array('Edit Template', 'Edit e-mail template ID %s');
$GLOBALS['TL_LANG']['tl_iso_mail']['copy']			= array('Copy Template', 'Copy e-mail template ID %s');
$GLOBALS['TL_LANG']['tl_iso_mail']['delete']		= array('Delete Template', 'Delete e-mail template ID %s');
$GLOBALS['TL_LANG']['tl_iso_mail']['show']			= array('Details', 'Details for e-mail template ID %s');
$GLOBALS['TL_LANG']['tl_iso_mail']['importMail']	= array('Import', 'Import email template');
$GLOBALS['TL_LANG']['tl_iso_mail']['exportMail']	= array('Export', 'Export e-mail template ID %s');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_mail']['name_legend']		= 'Name';
$GLOBALS['TL_LANG']['tl_iso_mail']['address_legend']	= 'Address';
$GLOBALS['TL_LANG']['tl_iso_mail']['expert_legend']		= 'Expert Legend';


/**
 * References
 */
$GLOBALS['TL_LANG']['tl_iso_mail']['xml_error']			= 'Template "%s" is corrupt and cannot be imported.';
$GLOBALS['TL_LANG']['tl_iso_mail']['mail_imported']		= 'Template "%s" has been imported.';
$GLOBALS['TL_LANG']['tl_iso_mail']['priority_ref']['1'] = 'very high';
$GLOBALS['TL_LANG']['tl_iso_mail']['priority_ref']['2'] = 'high';
$GLOBALS['TL_LANG']['tl_iso_mail']['priority_ref']['3'] = 'normal';
$GLOBALS['TL_LANG']['tl_iso_mail']['priority_ref']['4'] = 'low';
$GLOBALS['TL_LANG']['tl_iso_mail']['priority_ref']['5'] = 'very low';

