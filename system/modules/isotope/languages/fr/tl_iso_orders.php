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
 * @author     Rebecca Jutzi <rebecca.jutzi@bluewin.ch>
 * @author     Simon Moos <cc@simonmoos.com>
 * @author     Cyril Ponce <cyril@contao.fr>
 * @author     Stéphane Cagni <stephane@cagni.fr>
 * @author     Katelle Ave <contact@graphikat.net>
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_orders']['config_id']           = array('Configuration de boutique');
$GLOBALS['TL_LANG']['tl_iso_orders']['order_id'][0]         = 'ID de la commande';
$GLOBALS['TL_LANG']['tl_iso_orders']['uniqid'][0]           = 'ID unique';
$GLOBALS['TL_LANG']['tl_iso_orders']['status'][0]           = 'État de la commande';
$GLOBALS['TL_LANG']['tl_iso_orders']['status'][1]           = 'Sélectionner l\'état de cette commande';
$GLOBALS['TL_LANG']['tl_iso_orders']['date_paid'][0]        = 'Date de paiement';
$GLOBALS['TL_LANG']['tl_iso_orders']['date_paid'][1]        = 'Saisir la date à laquelle la commande a été payée.';
$GLOBALS['TL_LANG']['tl_iso_orders']['date_shipped'][0]     = 'Date de livraison';
$GLOBALS['TL_LANG']['tl_iso_orders']['date_shipped'][1]     = 'Saisir la date à laquelle la commande a été livrée.';
$GLOBALS['TL_LANG']['tl_iso_orders']['date'][0]             = 'Date';
$GLOBALS['TL_LANG']['tl_iso_orders']['payment_id'][0]       = 'Mode de paiement';
$GLOBALS['TL_LANG']['tl_iso_orders']['shipping_id'][0]      = 'Mode de livraison';
$GLOBALS['TL_LANG']['tl_iso_orders']['shipping_address'][0] = 'Adresse de livraison';
$GLOBALS['TL_LANG']['tl_iso_orders']['billing_address'][0]  = 'Adresse de facturation';

$GLOBALS['TL_LANG']['tl_iso_orders']['order_subtotal'][0]   = 'Sous-total';
$GLOBALS['TL_LANG']['tl_iso_orders']['order_tax'][0]        = 'Coût des taxes';
$GLOBALS['TL_LANG']['tl_iso_orders']['shippingTotal'][0]    = 'Coût de la livrasion';
$GLOBALS['TL_LANG']['tl_iso_orders']['shipping_method'][0]  = 'Mode de livraison';
$GLOBALS['TL_LANG']['tl_iso_orders']['surcharges'][0]       = 'Surtaxes';
$GLOBALS['TL_LANG']['tl_iso_orders']['cc_num'][0]           = 'Numéro de carte';
$GLOBALS['TL_LANG']['tl_iso_orders']['cc_num'][1]           = 'Le numéro de la carte de paiement';
$GLOBALS['TL_LANG']['tl_iso_orders']['cc_cvv'][0]           = 'Cryptogramme visuel';
$GLOBALS['TL_LANG']['tl_iso_orders']['cc_cvv'][1]           = '3 ou 4 derniers chiffres présents au dos de la carte de paiement.';
$GLOBALS['TL_LANG']['tl_iso_orders']['cc_type'][0]          = 'Type de carte';
$GLOBALS['TL_LANG']['tl_iso_orders']['cc_type'][1]          = 'Le type de la carte de paiement.';
$GLOBALS['TL_LANG']['tl_iso_orders']['cc_exp'][0]           = 'Expiration';
$GLOBALS['TL_LANG']['tl_iso_orders']['cc_exp'][1]           = 'La date d\'expiration de la carte de paiement.';
$GLOBALS['TL_LANG']['tl_iso_orders']['notes'][0]            = 'Informations sur la commande';
$GLOBALS['TL_LANG']['tl_iso_orders']['notes'][1]            = 'Permet de transmettre des informations à d\'autres utilisateurs du back office.';


/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_iso_orders']['orderStatusEmail'] = 'L\'état de votre commande a été mis à jour et le client a été notifié par e-mail.';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_orders']['edit'][0]           = 'Éditer une commande';
$GLOBALS['TL_LANG']['tl_iso_orders']['edit'][1]           = 'Éditer la commande ID %s';
$GLOBALS['TL_LANG']['tl_iso_orders']['copy'][0]           = 'Dupliquer une commande';
$GLOBALS['TL_LANG']['tl_iso_orders']['copy'][1]           = 'Dupliquer la commande ID %s';
$GLOBALS['TL_LANG']['tl_iso_orders']['delete'][0]         = 'Supprimer une commande';
$GLOBALS['TL_LANG']['tl_iso_orders']['delete'][1]         = 'Supprimer la commande ID %s';
$GLOBALS['TL_LANG']['tl_iso_orders']['show'][0]           = 'Détails de la commande';
$GLOBALS['TL_LANG']['tl_iso_orders']['show'][1]           = 'Afficher les détails de la commande ID %s';
$GLOBALS['TL_LANG']['tl_iso_orders']['payment']           = array('Payment details', 'Show payment details of order ID %s');
$GLOBALS['TL_LANG']['tl_iso_orders']['shipping']          = array('Shipping details', 'Show shipping details of order ID %s');
$GLOBALS['TL_LANG']['tl_iso_orders']['print_order'][0]    = 'Imprimer cette commande';
$GLOBALS['TL_LANG']['tl_iso_orders']['print_order'][1]    = 'Imprimer une facture pour la commande en cours';
$GLOBALS['TL_LANG']['tl_iso_orders']['tools'][0]          = 'Outils';
$GLOBALS['TL_LANG']['tl_iso_orders']['tools'][1]          = 'Plus d\'options pour la gestion des commandes.';
$GLOBALS['TL_LANG']['tl_iso_orders']['export_emails'][0]  = 'Exporter les e-mails des commandes';
$GLOBALS['TL_LANG']['tl_iso_orders']['export_emails'][1]  = 'Exporter les e-mails des personnes qui ont commandé.';
$GLOBALS['TL_LANG']['tl_iso_orders']['print_invoices'][0] = 'Impression des factures';
$GLOBALS['TL_LANG']['tl_iso_orders']['print_invoices'][1] = 'Imprimer une ou plusieurs factures dans un seul document pour un état de commande définit.';


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_orders']['status_legend']           = 'État de la commande';
$GLOBALS['TL_LANG']['tl_iso_orders']['details_legend']          = 'Détails de la commande';
$GLOBALS['TL_LANG']['tl_iso_orders']['email_legend']            = 'Email data';
$GLOBALS['TL_LANG']['tl_iso_orders']['billing_address_legend']  = 'Billing address data';
$GLOBALS['TL_LANG']['tl_iso_orders']['shipping_address_legend'] = 'Shipping address data';
