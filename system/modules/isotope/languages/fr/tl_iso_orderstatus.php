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

