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

$GLOBALS['TL_LANG']['tl_iso_config']['name'][0] = 'Nom de la configuration';
$GLOBALS['TL_LANG']['tl_iso_config']['label'][0] = 'Libellé';
$GLOBALS['TL_LANG']['tl_iso_config']['label'][1] = 'Ce libellé est utilisé en front office, ex. : l\'outil de changement de configuration';
$GLOBALS['TL_LANG']['tl_iso_config']['fallback'][0] = 'Boutique par défaut';
$GLOBALS['TL_LANG']['tl_iso_config']['fallback'][1] = 'Définir cette boutique par défaut pour le formatage de monnaies et d\'autres informations spécifiques aux paramètres régionaux du back office.';
$GLOBALS['TL_LANG']['tl_iso_config']['store_id'][0] = 'ID de la boutique';
$GLOBALS['TL_LANG']['tl_iso_config']['store_id'][1] = 'Utiliser des ID de boutique différents pour regrouper un ensemble de configurations de magasin. Un panier de commande sera partagé entre les mêmes ID de boutique.';
$GLOBALS['TL_LANG']['tl_iso_config']['missing_image_placeholder'][0] = 'Image par défaut';
$GLOBALS['TL_LANG']['tl_iso_config']['missing_image_placeholder'][1] = 'Cette image sera utilisée si un fichier image ne peut être trouvé ou si aucune image n\'est associée à un produit.';
$GLOBALS['TL_LANG']['tl_iso_config']['priceCalculateFactor'][0] = 'Taux de conversion';
$GLOBALS['TL_LANG']['tl_iso_config']['priceCalculateFactor'][1] = 'La valeur par défaut est 1. Le taux de conversion est utiliser pour convertir plusieurs devises.';
$GLOBALS['TL_LANG']['tl_iso_config']['priceCalculateMode'][0] = 'Mode de calcul';
$GLOBALS['TL_LANG']['tl_iso_config']['priceCalculateMode'][1] = 'Divise ou multiplie en utilisant le taux de conversion.';
$GLOBALS['TL_LANG']['tl_iso_config']['priceRoundPrecision'][0] = 'Précision de l\'arrondi';
$GLOBALS['TL_LANG']['tl_iso_config']['priceRoundPrecision'][1] = 'Définir la précision de l\'arrondi. Une valeur entre 0 et 2 fonctionne correctement sur toutes les passerelles de paiement. Voir le manuel de PHP pour la fonction round().';
$GLOBALS['TL_LANG']['tl_iso_config']['priceRoundIncrement'][0] = 'Augmentation de l\'arrondi';
$GLOBALS['TL_LANG']['tl_iso_config']['priceRoundIncrement'][1] = 'Certaines monnaies (ex. : franc suisse) ne prennent pas en charge la précision 0.01.';
$GLOBALS['TL_LANG']['tl_iso_config']['cartMinSubtotal'][0] = 'Sous-total minimum';
$GLOBALS['TL_LANG']['tl_iso_config']['cartMinSubtotal'][1] = 'Le sous-total minimum du panier requis lors d\'une commande. Saisir 0 pour désactiver.';
$GLOBALS['TL_LANG']['tl_iso_config']['currency'][0] = 'Monnaie';
$GLOBALS['TL_LANG']['tl_iso_config']['currency'][1] = 'Sélectionner une monnaie pour cette boutique.';
$GLOBALS['TL_LANG']['tl_iso_config']['currencySymbol'][0] = 'Utiliser le symbole monétaire';
$GLOBALS['TL_LANG']['tl_iso_config']['currencySymbol'][1] = 'Utiliser le symbole monétaire ($, €) si disponnible.';
$GLOBALS['TL_LANG']['tl_iso_config']['currencySpace'][0] = 'Inclure un espace vide';
$GLOBALS['TL_LANG']['tl_iso_config']['currencySpace'][1] = 'Ajouter un espace vide entre le prix et le symbole monétaire.';
$GLOBALS['TL_LANG']['tl_iso_config']['currencyPosition'][0] = 'Position du symbole monétaire';
$GLOBALS['TL_LANG']['tl_iso_config']['currencyPosition'][1] = 'Sélectionner si la devise doit s\'afficher à gauche ou à droite du prix.';
$GLOBALS['TL_LANG']['tl_iso_config']['currencyFormat'][0] = 'Formatage de la monnaie';
$GLOBALS['TL_LANG']['tl_iso_config']['currencyFormat'][1] = 'Choisir un formatage de la monnaie pour les prix.';
$GLOBALS['TL_LANG']['tl_iso_config']['currencyAutomator'] = array('Automatic currency conversion', 'Enable automatic update of price calcuation factors by using an online currency conversion provider.');
$GLOBALS['TL_LANG']['tl_iso_config']['currencyOrigin'] = array('Currency origin', 'Please select the source currency for calculation.');
$GLOBALS['TL_LANG']['tl_iso_config']['currencyProvider'] = array('Currency Data Provider', 'Select an available online provider of currency calculation data.');
$GLOBALS['TL_LANG']['tl_iso_config']['orderstatus_new'] = array('Status for new orders', 'Choose a matching status for new orders. A payment module can override this.');
$GLOBALS['TL_LANG']['tl_iso_config']['orderstatus_error'] = array('Order status on error', 'Select an order status if something goes wrong (e.g. payment).');
$GLOBALS['TL_LANG']['tl_iso_config']['invoiceLogo'][0] = 'Logo des factures';
$GLOBALS['TL_LANG']['tl_iso_config']['invoiceLogo'][1] = 'Sélectionner un logo qui apparaîtra sur les factures de ce magasin.';
$GLOBALS['TL_LANG']['tl_iso_config']['firstname'][0] = 'Prénom';
$GLOBALS['TL_LANG']['tl_iso_config']['firstname'][1] = 'Saisir le prénom.';
$GLOBALS['TL_LANG']['tl_iso_config']['lastname'][0] = 'Nom';
$GLOBALS['TL_LANG']['tl_iso_config']['lastname'][1] = 'Saisir le nom.';
$GLOBALS['TL_LANG']['tl_iso_config']['company'][0] = 'Société';
$GLOBALS['TL_LANG']['tl_iso_config']['company'][1] = 'Saisir la société.';
$GLOBALS['TL_LANG']['tl_iso_config']['street_1'][0] = 'Rue';
$GLOBALS['TL_LANG']['tl_iso_config']['street_1'][1] = 'Saisir le nom et le numéro de la rue.';
$GLOBALS['TL_LANG']['tl_iso_config']['street_2'][0] = 'Rue 2';
$GLOBALS['TL_LANG']['tl_iso_config']['street_2'][1] = 'Saisir un complément d\'adresse s\'il y a lieu.';
$GLOBALS['TL_LANG']['tl_iso_config']['street_3'][0] = 'Rue 3';
$GLOBALS['TL_LANG']['tl_iso_config']['street_3'][1] = 'Saisir un complément d\'adresse s\'il y a lieu.';
$GLOBALS['TL_LANG']['tl_iso_config']['postal'][0] = 'Code postal';
$GLOBALS['TL_LANG']['tl_iso_config']['postal'][1] = 'Saisir le code postal.';
$GLOBALS['TL_LANG']['tl_iso_config']['city'][0] = 'Ville';
$GLOBALS['TL_LANG']['tl_iso_config']['city'][1] = 'Saisir le nom de la ville.';
$GLOBALS['TL_LANG']['tl_iso_config']['subdivision'][0] = 'Département / État';
$GLOBALS['TL_LANG']['tl_iso_config']['subdivision'][1] = 'Saisir le nom du département ou de l\'état.';
$GLOBALS['TL_LANG']['tl_iso_config']['country'][0] = 'Pays';
$GLOBALS['TL_LANG']['tl_iso_config']['country'][1] = 'Sélectionner le pays.';
$GLOBALS['TL_LANG']['tl_iso_config']['phone'][0] = 'Téléphone';
$GLOBALS['TL_LANG']['tl_iso_config']['phone'][1] = 'Saisir un numéro de téléphone.';
$GLOBALS['TL_LANG']['tl_iso_config']['email'][0] = 'Adresse e-mail de livraison';
$GLOBALS['TL_LANG']['tl_iso_config']['email'][1] = 'Saisir une adresse e-mail valide.';
$GLOBALS['TL_LANG']['tl_iso_config']['shipping_countries'][0] = 'Pays de livraison';
$GLOBALS['TL_LANG']['tl_iso_config']['shipping_countries'][1] = 'Sélectionner les pays autorisés pour l\'adresse de livraison de la commande.';
$GLOBALS['TL_LANG']['tl_iso_config']['shipping_fields'][0] = 'Champs d\'adresse de livraison';
$GLOBALS['TL_LANG']['tl_iso_config']['shipping_fields'][1] = 'Sélectionner les champs qui seront affichés lors de la création d\'une nouvelle adresse d\'expédition.';
$GLOBALS['TL_LANG']['tl_iso_config']['shipping_country'] = array('Shipping default country', 'Select a default country for new shipping addresses.');
$GLOBALS['TL_LANG']['tl_iso_config']['billing_countries'][0] = 'Pays de facturation';
$GLOBALS['TL_LANG']['tl_iso_config']['billing_countries'][1] = 'Sélectionner les pays autorisés pour l\'adresse de facturation de la commande.';
$GLOBALS['TL_LANG']['tl_iso_config']['billing_fields'][0] = 'Champs d\'adresse de facturation';
$GLOBALS['TL_LANG']['tl_iso_config']['billing_fields'][1] = 'Sélectionner les champs qui seront affichés lors de la création d\'une nouvelle adresse de facturation.';
$GLOBALS['TL_LANG']['tl_iso_config']['billing_country'] = array('Billing default country', 'Select a default country for new billing addresses.');
$GLOBALS['TL_LANG']['tl_iso_config']['orderPrefix'][0] = 'Préfixe du numéro de commande';
$GLOBALS['TL_LANG']['tl_iso_config']['orderPrefix'][1] = 'Ajouter un préfixe (ex. : année de l\'exercice fiscal) pour le numéro auto-incrémenté de la commande.';
$GLOBALS['TL_LANG']['tl_iso_config']['orderDigits'] = array('Order number length', 'Select the minimum length of the order number (not including order prefix).');
$GLOBALS['TL_LANG']['tl_iso_config']['templateGroup'][0] = 'Répertoire des modèles';
$GLOBALS['TL_LANG']['tl_iso_config']['templateGroup'][1] = 'Sélectionner un répertoire de modèles qui sera utilisé avant la recherche dans les autres répertoires de modèles.';
$GLOBALS['TL_LANG']['tl_iso_config']['limitMemberCountries'][0] = 'Limiter les pays des membres';
$GLOBALS['TL_LANG']['tl_iso_config']['limitMemberCountries'][1] = 'Limiter les pays des membres (inscription, données personnelles) à la liste combinée des pays de facturation et d\'expédition.';
$GLOBALS['TL_LANG']['tl_iso_config']['enableGoogleAnalytics'][0] = 'Activer le suivi de commerce électronique de Google Analytics';
$GLOBALS['TL_LANG']['tl_iso_config']['enableGoogleAnalytics'][1] = 'Ajouter le suivi Google Analytics e-commerce. Notez qu\'il faut également activer le suivi du commerce électronique dans votre compte Google Analytics.';
$GLOBALS['TL_LANG']['tl_iso_config']['gallery'][0] = 'Galerie d\'images du produit';
$GLOBALS['TL_LANG']['tl_iso_config']['gallery'][1] = 'Des galeries d\'images différentes peuvent être utilisées pour présenter des fichiers multimédias dans un style personnalisé.';
$GLOBALS['TL_LANG']['tl_iso_config']['imageSizes'][0] = 'Tailles des images';
$GLOBALS['TL_LANG']['tl_iso_config']['imageSizes'][1] = 'Créer des tailles d\'image personnalisées à utiliser dans les modèles. Les tailles par défaut sont "gallery", "thumbnail", "medium" et "large".';
$GLOBALS['TL_LANG']['tl_iso_config']['imageSizes'][2] = 'vignette';
$GLOBALS['TL_LANG']['tl_iso_config']['imageSizes'][3] = 'moyenne et grande';


/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_iso_config']['left'] = 'A la gauche du prix';
$GLOBALS['TL_LANG']['tl_iso_config']['right'] = 'A la droite du prix';
$GLOBALS['TL_LANG']['tl_iso_config']['div'] = 'Diviser';
$GLOBALS['TL_LANG']['tl_iso_config']['mul'] = 'Multiplier';

$GLOBALS['TL_LANG']['tl_iso_config']['tl'] = 'En haut à gauche';
$GLOBALS['TL_LANG']['tl_iso_config']['tc'] = 'En haut';
$GLOBALS['TL_LANG']['tl_iso_config']['tr'] = 'En haut à droite';
$GLOBALS['TL_LANG']['tl_iso_config']['bl'] = 'En bas à gauche';
$GLOBALS['TL_LANG']['tl_iso_config']['bc'] = 'En bas';
$GLOBALS['TL_LANG']['tl_iso_config']['br'] = 'En bas à droite';
$GLOBALS['TL_LANG']['tl_iso_config']['cc'] = 'Centrer';

$GLOBALS['TL_LANG']['tl_iso_config']['iwName'][0] = 'Nom';
$GLOBALS['TL_LANG']['tl_iso_config']['iwWidth'][0] = 'Largeur';
$GLOBALS['TL_LANG']['tl_iso_config']['iwHeight'][0] = 'Hauteur';
$GLOBALS['TL_LANG']['tl_iso_config']['iwMode'][0] = 'Mode';
$GLOBALS['TL_LANG']['tl_iso_config']['iwWatermark'][0] = 'Image en filigrane';
$GLOBALS['TL_LANG']['tl_iso_config']['iwPosition'][0] = 'Position';

$GLOBALS['TL_LANG']['tl_iso_config']['fwEnabled'] = 'Activer le champ';
$GLOBALS['TL_LANG']['tl_iso_config']['fwLabel'] = 'Libellé personnalisé';
$GLOBALS['TL_LANG']['tl_iso_config']['fwMandatory'] = 'Obligatoire';

$GLOBALS['TL_LANG']['tl_iso_config']['ecb.int']  = 'Banque centrale européenne';
$GLOBALS['TL_LANG']['tl_iso_config']['admin.ch'] = 'Département fédéral des finances';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_config']['new'][0] = 'Nouvelle configuration';
$GLOBALS['TL_LANG']['tl_iso_config']['new'][1] = 'Créer une nouvelle configuration de boutique.';
$GLOBALS['TL_LANG']['tl_iso_config']['edit'][0] = 'Éditer la configuration';
$GLOBALS['TL_LANG']['tl_iso_config']['edit'][1] = 'Éditer la configuration de boutique ID %s.';
$GLOBALS['TL_LANG']['tl_iso_config']['copy'][0] = 'Dupliquer la configuration';
$GLOBALS['TL_LANG']['tl_iso_config']['copy'][1] = 'Dupliquer la configuration de boutique ID %s.';
$GLOBALS['TL_LANG']['tl_iso_config']['delete'][0] = 'Supprimer la configuration';
$GLOBALS['TL_LANG']['tl_iso_config']['delete'][1] = 'Supprimer la configuration de boutique ID %s.';
$GLOBALS['TL_LANG']['tl_iso_config']['show'][0] = 'Afficher les détails de la configuration';
$GLOBALS['TL_LANG']['tl_iso_config']['show'][1] = 'Afficher les détails de la configuration de boutique ID %s.';


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_config']['name_legend']      = 'Nom';
$GLOBALS['TL_LANG']['tl_iso_config']['address_legend']   = 'Configuration d\'adresse';
$GLOBALS['TL_LANG']['tl_iso_config']['config_legend']    = 'Configuration';
$GLOBALS['TL_LANG']['tl_iso_config']['price_legend']     = 'Prix';
$GLOBALS['TL_LANG']['tl_iso_config']['currency_legend']  = 'Formatage de la monnaie';
$GLOBALS['TL_LANG']['tl_iso_config']['converter_legend'] = 'Conversion de la monnaie';
$GLOBALS['TL_LANG']['tl_iso_config']['order_legend']     = 'Orders';
$GLOBALS['TL_LANG']['tl_iso_config']['images_legend']    = 'Images';

