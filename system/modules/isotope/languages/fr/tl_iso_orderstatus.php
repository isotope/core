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
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['name']          = array('Nom', 'Veuillez, s\'il vous plaît, entrer un nom pour cet état.');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['paid']          = array('La commande est payée', 'On suppose que la commande est payée quand elle a cet état.');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['welcomescreen'] = array('Afficher dans le back office', 'Afficher le nombre de commandes avec cet état dans le back office.');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['mail_customer'] = array('E-mail à la clientèle', 'Sélectionnez un modèle d\'e-mail pour informer le client lorsque cet état est attribué à une commande.');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['mail_admin']    = array('E-mail à l\'administrateur', 'Sélectionnez un modèle d\'e-mail pour informer l\'administrateur lorsque cet état est attribué à une commande.');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['sales_email']   = array('Sales admin email address', 'Enter an email address for status notifications to be sent to. If you dont enter anything, the checkout modules sales admin or system admin will be notified.');


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['new']        = array('Nouvel état des commandes', 'Créer un nouvel état des commandes');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['edit']       = array('Éditer un état des commandes', 'Éditer l\'état des commandes ID %s');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['copy']       = array('Dupliquer un état des commandes', 'Dupliquer l\'état des commandes ID %s');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['cut']        = array('Déplacer un état des commandes', 'Déplacer l\'état des commandes ID %s');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['delete']     = array('Supprimer un état des commandes', 'Supprimer l\'état des commandes ID %s');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['show']       = array('Détails de l\'état des commandes', 'Afficher les détails de l\'état des commandes ID %s');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['pasteafter'] = array('Coller après', 'Coller après l\'état des commandes ID %s');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['pasteinto']  = array('Coller dans', 'Coller dans l\'état des commandes ID %s');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['name_legend']  = 'Nom';
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['email_legend'] = 'Notification par e-mail';

