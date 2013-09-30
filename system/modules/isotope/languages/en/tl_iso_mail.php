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
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_mail']['name']              = array('Name', 'Please enter a name for the email. Used as a reference in the system.');
$GLOBALS['TL_LANG']['tl_iso_mail']['senderName']        = array('Sender name', 'Enter the name of the sender.');
$GLOBALS['TL_LANG']['tl_iso_mail']['sender']            = array('Sender email', 'Enter the e-mail address of the sender. The recipient will reply to this address.');
$GLOBALS['TL_LANG']['tl_iso_mail']['cc']                = array('Send a CC to', 'Recipients that should receive a carbon copy of the mail. Separate multiple addresses with a comma.');
$GLOBALS['TL_LANG']['tl_iso_mail']['bcc']               = array('Send a BCC to', 'Recipients that should receive a blind carbon copy of the mail. Separate multiple addresses with a comma.');
$GLOBALS['TL_LANG']['tl_iso_mail']['source']            = array('Source files', 'Please choose one or more .imt files from the files directory.');
$GLOBALS['TL_LANG']['tl_iso_mail']['template']          = array('Email template', 'Here you can select an HTML e-mail template to use.');
$GLOBALS['TL_LANG']['tl_iso_mail']['priority']          = array('Priority', 'Please select a priority.');

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_mail']['new']               = array('New template', 'Create a new e-mail template');
$GLOBALS['TL_LANG']['tl_iso_mail']['edit']              = array('Edit template', 'Edit e-mail template ID %s');
$GLOBALS['TL_LANG']['tl_iso_mail']['editheader']        = array('Edit template settings', 'Edit the settings for e-mail template ID %s');
$GLOBALS['TL_LANG']['tl_iso_mail']['copy']              = array('Copy template', 'Copy e-mail template ID %s');
$GLOBALS['TL_LANG']['tl_iso_mail']['delete']            = array('Delete template', 'Delete e-mail template ID %s');
$GLOBALS['TL_LANG']['tl_iso_mail']['show']              = array('Template details', 'Details for e-mail template ID %s');
$GLOBALS['TL_LANG']['tl_iso_mail']['importMail']        = array('Import', 'Import email template');
$GLOBALS['TL_LANG']['tl_iso_mail']['exportMail']        = array('Export', 'Export e-mail template ID %s');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_mail']['name_legend']       = 'Name';
$GLOBALS['TL_LANG']['tl_iso_mail']['address_legend']    = 'Address';
$GLOBALS['TL_LANG']['tl_iso_mail']['document_legend']   = 'Attachment';
$GLOBALS['TL_LANG']['tl_iso_mail']['expert_legend']     = 'Expert settings';

/**
 * References
 */
$GLOBALS['TL_LANG']['tl_iso_mail']['xml_error']         = 'Template "%s" is corrupt and cannot be imported.';
$GLOBALS['TL_LANG']['tl_iso_mail']['mail_imported']     = 'Template "%s" has been imported.';
$GLOBALS['TL_LANG']['tl_iso_mail']['priority_ref']['1'] = 'very high';
$GLOBALS['TL_LANG']['tl_iso_mail']['priority_ref']['2'] = 'high';
$GLOBALS['TL_LANG']['tl_iso_mail']['priority_ref']['3'] = 'normal';
$GLOBALS['TL_LANG']['tl_iso_mail']['priority_ref']['4'] = 'low';
$GLOBALS['TL_LANG']['tl_iso_mail']['priority_ref']['5'] = 'very low';
