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
 * @author     Rebecca Jutzi <rebecca.jutzi@bluewin.ch>
 * @author     Simon Moos <cc@simonmoos.com>
 * @author     Cyril Ponce <cyril@contao.fr>
 * @author     Stéphane Cagni <stephane@cagni.fr>
 * @author     Katelle Ave <contact@graphikat.net>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][0][0] = '##order_id##';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][0][1] = 'Numéro unique pour cette commande';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][1][0] = '##items##';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][1][1] = 'Nombre d\'éléments dans le panier';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][2][0] = '##products##';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][2][1] = 'Produits dans le panier';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][3][0] = '##subTotal##';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][3][1] = 'Sous-total de la commande';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][4][0] = '##taxTotal##';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][4][1] = 'Total des taxes (hors frais de port)';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][5][0] = '##shippingPrice##';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][5][1] = 'Total des frais de port';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][6][0] = '##grandTotal##';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][6][1] = 'Total général';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][7][0] = '##cart_text##';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][7][1] = 'Liste des produits au format texte';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][8][0] = '##cart_html##';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][8][1] = 'Liste des produits au format HTML';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][9][0] = '##billing_address##<br />##billing_address_text##';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][9][1] = 'Adresse de facturation au format HTML ou texte brut.';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][10][0] = '##shipping_address##<br />##shipping_address_text##';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][10][1] = 'Adresse de livraison au format HTML ou texte brut.';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][11][0] = '##shipping_method##';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][11][1] = 'Nom de la méthode de livraison (inscrite dans le back office)';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][12][0] = '##shipping_note##<br />##shipping_note_text##';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][12][1] = 'Notez le message pour le mode d\'expédition choisi (également disponible sous forme de texte ).';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][13][0] = '##payment_method##';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][13][1] = 'Nom du mode de paiement (comme inscrit dans le back office)';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][14][0] = '##payment_note##<br />##payment_note_text##';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][14][1] = 'Notez le message pour le mode de paiement choisi (également disponible sous forme de texte ).';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][15][0] = '##billing_firstname##<br />##billing_lastname##<br />...';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][15][1] = 'Champs individuels de l\'adresse de facturation.';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][16][0] = '##shipping_firstname##<br />##shipping_lastname##<br />...';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][16][1] = 'Champs individuels de l\'adresse de livraison.';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][17][0] = '##form_...##';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][17][1] = 'Récupérer des données de formulaire. Utiliser le préfixe "form_" et le nom du champ.';
$GLOBALS['TL_LANG']['XPL']['mediaManager'] = '<p class="tl_help_table">Pour télécharger une nouvelle image, sélectionner le fichier et sauvegarder le produit. Après le téléchargement, un aperçu de l\'image, un texte alternatif et une description sont affichés. Pour plusieurs photos,cliquer sur les flèches à droite pour changer leur ordre, l\'image du haut est utilisée comme image principale de chaque produit.</p>';

