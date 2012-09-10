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
$GLOBALS['TL_LANG']['tl_iso_addresses']['store_id'][0]          = 'ID de la boutique';
$GLOBALS['TL_LANG']['tl_iso_addresses']['store_id'][1]          = 'Utiliser des ID de boutique différents pour regrouper un ensemble de configurations de magasin. Un panier de commande sera partagé entre les mêmes ID de boutique.';
$GLOBALS['TL_LANG']['tl_iso_addresses']['label']                = array('Libellé', 'Veuillez, s\'il vous plaît, entrer un libellé personnalisé pour cette adresse.');
$GLOBALS['TL_LANG']['tl_iso_addresses']['salutation']           = array('Civilité', 'Veuillez, s\'il vous plaît, entrer un titre de civilité (M., Mme, Dr, Pr).');
$GLOBALS['TL_LANG']['tl_iso_addresses']['firstname'][0]         = 'Prénom';
$GLOBALS['TL_LANG']['tl_iso_addresses']['firstname'][1]         = 'Saisir le prénom.';
$GLOBALS['TL_LANG']['tl_iso_addresses']['lastname'][0]          = 'Nom de famille';
$GLOBALS['TL_LANG']['tl_iso_addresses']['lastname'][1]          = 'Saisir le nom de famille.';
$GLOBALS['TL_LANG']['tl_iso_addresses']['company'][0]           = 'Entreprise';
$GLOBALS['TL_LANG']['tl_iso_addresses']['company'][1]           = 'Saisir un nom d\'entreprise.';
$GLOBALS['TL_LANG']['tl_iso_addresses']['street_1'][0]          = 'Rue';
$GLOBALS['TL_LANG']['tl_iso_addresses']['street_1'][1]          = 'Saisir la rue et le numéro de rue.';
$GLOBALS['TL_LANG']['tl_iso_addresses']['street_2'][0]          = 'Rue 2';
$GLOBALS['TL_LANG']['tl_iso_addresses']['street_2'][1]          = 'Saisir un complément d\'adresse s\'il y a lieu.';
$GLOBALS['TL_LANG']['tl_iso_addresses']['street_3'][0]          = 'Rue 3';
$GLOBALS['TL_LANG']['tl_iso_addresses']['street_3'][1]          = 'Saisir un complément d\'adresse s\'il y a lieu.';
$GLOBALS['TL_LANG']['tl_iso_addresses']['postal'][0]            = 'Code postal';
$GLOBALS['TL_LANG']['tl_iso_addresses']['postal'][1]            = 'Saisir le code postal.';
$GLOBALS['TL_LANG']['tl_iso_addresses']['city'][0]              = 'Ville';
$GLOBALS['TL_LANG']['tl_iso_addresses']['city'][1]              = 'Saisir le nom de la ville.';
$GLOBALS['TL_LANG']['tl_iso_addresses']['subdivision'][0]       = 'État';
$GLOBALS['TL_LANG']['tl_iso_addresses']['subdivision'][1]       = 'Saisir le nom de l\'état.';
$GLOBALS['TL_LANG']['tl_iso_addresses']['country'][0]           = 'Pays';
$GLOBALS['TL_LANG']['tl_iso_addresses']['country'][1]           = 'Choisir un pays.';
$GLOBALS['TL_LANG']['tl_iso_addresses']['phone'][0]             = 'Numéro de téléphone';
$GLOBALS['TL_LANG']['tl_iso_addresses']['phone'][1]             = 'Saisir le numéro de téléphone.';
$GLOBALS['TL_LANG']['tl_iso_addresses']['email'][0]             = 'Adresse e-mail';
$GLOBALS['TL_LANG']['tl_iso_addresses']['email'][1]             = 'Saisir une adresse e-mail valide.';
$GLOBALS['TL_LANG']['tl_iso_addresses']['isDefaultBilling'][0]  = 'Adresse de facturation par défaut';
$GLOBALS['TL_LANG']['tl_iso_addresses']['isDefaultBilling'][1]  = 'Est-ce l\'adresse de facturation par défaut ?';
$GLOBALS['TL_LANG']['tl_iso_addresses']['isDefaultShipping'][0] = 'Adresse de livraison par défaut';
$GLOBALS['TL_LANG']['tl_iso_addresses']['isDefaultShipping'][1] = 'Est-ce l\'adresse de livraison par défaut ?';


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_addresses']['store_legend']    = 'Boutique';
$GLOBALS['TL_LANG']['tl_iso_addresses']['personal_legend'] = 'Données personnelles';
$GLOBALS['TL_LANG']['tl_iso_addresses']['address_legend']  = 'Détails de l\'adresse';
$GLOBALS['TL_LANG']['tl_iso_addresses']['contact_legend']  = 'Détails du contact';
$GLOBALS['TL_LANG']['tl_iso_addresses']['default_legend']  = 'Adresse par défaut';


/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_iso_addresses']['personalData']   = 'Données personnelles';
$GLOBALS['TL_LANG']['tl_iso_addresses']['addressDetails'] = 'Détails de l\'adresse';
$GLOBALS['TL_LANG']['tl_iso_addresses']['contactDetails'] = 'Détails du contact';
$GLOBALS['TL_LANG']['tl_iso_addresses']['loginDetails']   = 'Adresse par défaut';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_addresses']['new'][0]    = 'Nouvelle adresse';
$GLOBALS['TL_LANG']['tl_iso_addresses']['new'][1]    = 'Créer une nouvelle adresse';
$GLOBALS['TL_LANG']['tl_iso_addresses']['show'][0]   = 'Détails de l\'adresse';
$GLOBALS['TL_LANG']['tl_iso_addresses']['show'][1]   = 'Afficher les détails de l\'adresse ID %s';
$GLOBALS['TL_LANG']['tl_iso_addresses']['edit'][0]   = 'Éditer une adresse';
$GLOBALS['TL_LANG']['tl_iso_addresses']['edit'][1]   = 'Éditer l\'adresse ID %s';
$GLOBALS['TL_LANG']['tl_iso_addresses']['copy'][0]   = 'Dupliquer une adresse';
$GLOBALS['TL_LANG']['tl_iso_addresses']['copy'][1]   = 'Dupliquer l\'adresse ID %s';
$GLOBALS['TL_LANG']['tl_iso_addresses']['delete'][0] = 'Supprimer une adresse';
$GLOBALS['TL_LANG']['tl_iso_addresses']['delete'][1] = 'Supprimer l\'adresse ID %s';

