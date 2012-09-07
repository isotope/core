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
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Rebecca Jutzi <rebecca.jutzi@bluewin.ch>
 * @author     Simon Moos <cc@simonmoos.com>
 * @author     Cyril Ponce <cyril@contao.fr>
 * @author     Stéphane Cagni <stephane@cagni.fr>
 * @author     Katelle Ave <contact@graphikat.net>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_mail']['name'][0]          = 'Titre';
$GLOBALS['TL_LANG']['tl_iso_mail']['name'][1]          = 'Saisir un titre pour cet e-mail. Il sera utilisé comme référence par le système.';
$GLOBALS['TL_LANG']['tl_iso_mail']['senderName'][0]    = 'Nom de l\'expéditeur';
$GLOBALS['TL_LANG']['tl_iso_mail']['senderName'][1]    = 'Saisir le nom de l\'expéditeur';
$GLOBALS['TL_LANG']['tl_iso_mail']['sender'][0]        = 'E-mail de l\'expéditeur';
$GLOBALS['TL_LANG']['tl_iso_mail']['sender'][1]        = 'Saisir l\'adresse e-mail de l\'expéditeur. Le destinataire répondra à cette adresse.';
$GLOBALS['TL_LANG']['tl_iso_mail']['cc'][0]            = 'Envoyer un copie carbone (CC)';
$GLOBALS['TL_LANG']['tl_iso_mail']['cc'][1]            = 'Destinataires qui recevront recevoir une copie carbone de l\'e-mail. Séparer les adresses par des virgules.';
$GLOBALS['TL_LANG']['tl_iso_mail']['bcc'][0]           = 'Envoyer un copie carbone invisible (BCC)';
$GLOBALS['TL_LANG']['tl_iso_mail']['bcc'][1]           = 'Destinataires qui recevront recevoir une copie carbone invisible de l\'e-mail. Séparer les adresses par des virgules.';
$GLOBALS['TL_LANG']['tl_iso_mail']['template'][0]      = 'Modèle d\'\'e-mail';
$GLOBALS['TL_LANG']['tl_iso_mail']['template'][1]      = 'Sélectionner un modèle HTML à utiliser.';
$GLOBALS['TL_LANG']['tl_iso_mail']['priority']         = array('Priorité', 'Veuillez, s\'il vous plaît, sélectionner une priorité.');
$GLOBALS['TL_LANG']['tl_iso_mail']['attachDocument']   = array('Attach an order document', 'Allows you to generate an additional document as a PDF attachment for this email.');
$GLOBALS['TL_LANG']['tl_iso_mail']['documentTemplate'] = array('Document template', 'Select an document template to override the default collection template.');
$GLOBALS['TL_LANG']['tl_iso_mail']['documentTitle']    = array('Titre du document', 'Veuillez, s\'il vous plaît spécifier un titre pour le document joint.');
$GLOBALS['TL_LANG']['tl_iso_mail']['source']           = array('Fichiers sources', 'Please choose one or more .imt files from the files directory.');

//$GLOBALS['TL_LANG']['tl_iso_mail']['originateFromCustomerEmail'][0] = 'Envoyer à partir de l\'e-mail d\'un client';
//$GLOBALS['TL_LANG']['tl_iso_mail']['originateFromCustomerEmail'][1] = 'Envoyer cet e-mail en utilisant l\'e-mail du client comme expéditeur.';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_mail']['new'][0]     = 'Nouveau modèle d\'e-mail';
$GLOBALS['TL_LANG']['tl_iso_mail']['new'][1]     = 'Créer un nouveau modèle d\'e-mail';
$GLOBALS['TL_LANG']['tl_iso_mail']['edit'][0]    = 'Éditer le modèle d\'e-mail';
$GLOBALS['TL_LANG']['tl_iso_mail']['edit'][1]    = 'Éditer le modèle d\'e-mail ID %s';
$GLOBALS['TL_LANG']['tl_iso_mail']['editheader'] = array('Edit template settings', 'Edit the settings for e-mail template ID %s');
$GLOBALS['TL_LANG']['tl_iso_mail']['copy'][0]    = 'Dupliquer le modèle d\'e-mail';
$GLOBALS['TL_LANG']['tl_iso_mail']['copy'][1]    = 'Dupliquer le modèle d\'e-mail ID %s';
$GLOBALS['TL_LANG']['tl_iso_mail']['delete'][0]  = 'Supprimer le modèle d\'e-mail';
$GLOBALS['TL_LANG']['tl_iso_mail']['delete'][1]  = 'Supprimer le modèle d\'e-mail ID %s';
$GLOBALS['TL_LANG']['tl_iso_mail']['show'][0]    = 'Afficher les détails du modèle d\'e-mail';
$GLOBALS['TL_LANG']['tl_iso_mail']['show'][1]    = 'Afficher les détails du modèle d\'e-mail ID %s';
$GLOBALS['TL_LANG']['tl_iso_mail']['importMail'] = array('Importer', 'Importer un modèle d\'e-mail');
$GLOBALS['TL_LANG']['tl_iso_mail']['exportMail'] = array('Exporter', 'Exporter le modèle d\'e-mail ID %s');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_mail']['name_legend']     = 'Nom';
$GLOBALS['TL_LANG']['tl_iso_mail']['address_legend']  = 'Adresse';
$GLOBALS['TL_LANG']['tl_iso_mail']['document_legend'] = 'Joindre un document';
$GLOBALS['TL_LANG']['tl_iso_mail']['expert_legend']   = 'Paramètres avancés';


/**
 * References
 */
$GLOBALS['TL_LANG']['tl_iso_mail']['xml_error']         = 'Le modèle "%s" est endommagé et ne peut pas être importé.';
$GLOBALS['TL_LANG']['tl_iso_mail']['mail_imported']     = 'Le modèle "%s" a été importé.';
$GLOBALS['TL_LANG']['tl_iso_mail']['priority_ref']['1'] = 'très élevée';
$GLOBALS['TL_LANG']['tl_iso_mail']['priority_ref']['2'] = 'élevée';
$GLOBALS['TL_LANG']['tl_iso_mail']['priority_ref']['3'] = 'normal';
$GLOBALS['TL_LANG']['tl_iso_mail']['priority_ref']['4'] = 'faible';
$GLOBALS['TL_LANG']['tl_iso_mail']['priority_ref']['5'] = 'très faible';