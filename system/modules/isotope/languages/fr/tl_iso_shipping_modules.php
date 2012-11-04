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
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['name'][0]                = 'Nom du mode de livraison';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['type'][0]                = 'Type de mode de livraison';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['price'][0]               = 'Prix';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['note'][0]                = 'Notes relatives au mode de livraison';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['note'][1]                = 'Elles seront affichées en front office en liaison avec les options de livraison.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['tax_class'][0]           = 'Catégorie fiscale';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['label'][0]               = 'Libellé';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['label'][1]               = 'Il est affiché en front office en liaison avec l\'option de livraison.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['flatCalculation'][0]     = 'Calcul forfaitaire';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['weight_unit'][0]         = 'Unité de poids';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['weight_unit'][1]         = 'L\'unité dans laquelle vous saisirez les règles de poids.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['countries'][0]           = 'Pays';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['countries'][1]           = 'Sélectionnez les pays auxquels ce mode de livraison s\'applique. Si vous ne sélectionnez rien, le mode de livraison sera appliqué à tous les pays.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['subdivisions'][0]        = 'État / Régions';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['subdivisions'][1]        = 'Sélectionnez les états/régions auxquels ce mode de livraison s\'applique. Si vous ne sélectionnez rien, le mode de livraison sera appliqué à tous les états/régions.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['minimum_total'][0]       = 'Total minimum';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['maximum_total'][0]       = 'Total maximum';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['product_types'][0]       = 'Types de produits';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['product_types'][1]       = 'Limiter le mode de livraison pour certains types de produits. Si le panier contient un type de produit non sélectionné, le module d\'expédition n\'est pas disponible.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['surcharge_field'][0]     = 'Supplément de livraison';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['surcharge_field'][1]     = 'Spécifier un supplément (par exemple, une surtaxe de carburant sur toutes les commandes) à appliquer pour ce mode de livraison, le cas échéant.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['groups'][0]              = 'Groupes de membres';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['groups'][1]              = 'Restreindre l\'option de livraison à certains groupes de membres.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['protected'][0]           = 'Proteger le module';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['protected'][1]           = 'Ne montrer le module qu\'à certains groupes de membres';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['guests'][0]              = 'Visible par les invités seulement';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['guests'][1]              = 'Cacher le module si un membre est connecté';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['enabled'][0]             = 'Activer';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['enabled'][1]             = 'Le module est-il disponible pour une utilisation dans la boutique ?';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_accessKey'][0]       = 'Clé d\'accès UPS XML/HTML';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_accessKey'][1]       = 'Il s\'agit d\'une clé spéciale alphanumérique émise par UPS lors de la création d\'un compte UPS et pour l\'accès à l\'API Outils en ligne d\'UPS.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_userName'][0]        = 'Nom d\'utilisateur UPS';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_userName'][1]        = 'Le nom du compte choisi lors de l\'inscription sur le site de UPS.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_password'][0]        = 'Mot de passe UPS';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_password'][1]        = 'Le mot de passe choisi lors de l\'inscription sur le site de UPS.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_enabledService'][0]  = 'Type de service UPS';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_enabledService'][1]  = 'Sélectionner un service d\'expédition UPS à offrir.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_enabledService'][0] = 'Type de service USPS';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_enabledService'][1] = 'Sélectionner un service d\'expédition USPS à offrir.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_userName'][0]       = 'Nom d\'utilisateur USPS';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_userName'][1]       = 'Le nom du compte choisi lors de l\'inscription sur le site de USPS.';


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['title_legend']   = 'Titre et type';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['note_legend']    = 'Note de livraison';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['config_legend']  = 'Configuration';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_legend']     = 'Paramètres de l\'API UPS';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_legend']    = 'Paramètres de l\'API USPS';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['price_legend']   = 'Seuil de prix et application de la catégorie fiscale';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['expert_legend']  = 'Paramètres avancés';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['enabled_legend'] = 'Paramètres activés';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['new'][0]            = 'Nouveau mode de livraison';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['new'][1]            = 'Créer un nouveau mode de livraison';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['edit'][0]           = 'Éditer un mode de livraison';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['edit'][1]           = 'Éditer le mode de livraison ID %s';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['copy'][0]           = 'Dupliquer un mode de livraison';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['copy'][1]           = 'Dupliquer le mode de livraison ID %s';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['delete'][0]         = 'Effacer un mode de livraison';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['delete'][1]         = 'Effacer le mode de livraison ID %s';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['show'][0]           = 'Détails du mode de livraison';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['show'][1]           = 'Afficher les détails du mode de livraison ID %s';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['shipping_rates'][0] = 'Modifier les règles';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['shipping_rates'][1] = 'Modifier les tarifs de livraison';


/**
 * References
 */
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['flat']                                   = 'Appartement';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['perProduct']                             = 'Par produit';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['perItem']                                = 'Par article';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['01']                      = 'Next Day Air';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['02']                      = '2nd Day Air';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['03']                      = 'UPS Ground';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['07']                      = 'Worldwide Express';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['08']                      = 'Worldwide Expedited';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['11']                      = 'International Standard';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['12']                      = '3 Day Select';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['13']                      = 'Next Day Air Saver';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['14']                      = 'Next Day Air Early AM';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['54']                      = 'Worldwide Express Plus';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['65']                      = 'International Saver';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['PARCEL']                 = 'USPS Parcel Post';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['PRIORITY']               = 'USPS Priority Mail (2-3 days average)';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['EXPRESS']                = 'USPS Express Mail (Overnight Guaranteed)';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['FIRST CLASS']            = 'USPS First Class';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['PRIORITY COMMERCIAL']    = 'USPS Priority Commercial';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['EXPRESS COMMERCIAL']     = 'USPS Express Commercial';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['EXPRESS SH']             = 'USPS Express Sundays &amp; Holidays';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['EXPRESS SH COMMERCIAL']  = 'USPS Express Sundays &amp; Holidays Commercial';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['EXPRESS HFP']            = 'USPS Express Hold For Pickup';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['EXPRESS HFP COMMERCIAL'] = 'USPS Express Hold For Pickup Commercial';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['BPM']                    = 'USPS Bound Printed Matter';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['MEDIA']                  = 'USPS Media Mail';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['LIBRARY']                = 'USPS Library Mail';
