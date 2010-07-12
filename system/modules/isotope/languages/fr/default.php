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
 * @copyright  Winans Creative 2009, Intelligent Spark 2010, iserv.ch GmbH 2010
 * @author     Rebecca Jutzi <rebecca.jutzi@bluewin.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

$GLOBALS['TL_LANG']['CTE']['attributeLinkRepeater'][0] = 'liste d\'attributs à filtrer';
$GLOBALS['TL_LANG']['CTE']['attributeLinkRepeater'][1] = 'Cet élément crée une collection de liens d\'après un filtre choisi d\'attributs de produits';
$GLOBALS['TL_LANG']['ERR']['systemColumn'] = 'Le nom `%s` que vous avez choisi, est reservé pour l\'administration du système. Choisisez un autre nom s.v.p.';
$GLOBALS['TL_LANG']['ERR']['missingButtonTemplate'] = 'Vous devez choisir un document de mise en page pour le bouton "%s"';
$GLOBALS['TL_LANG']['ERR']['order_conditions'] = 'Vous devez accepter les termes et conditions avant de continuer';
$GLOBALS['TL_LANG']['ERR']['noStoreConfigurationSet'] = 'Il n\'existe pas de configuration pour cette boutique';
$GLOBALS['TL_LANG']['ERR']['noDefaultStoreConfiguration'] = 'Créez d\'abord une configuration de boutique';
$GLOBALS['TL_LANG']['ERR']['productNameMissing'] = 'Nom de produit inconnu';
$GLOBALS['TL_LANG']['ERR']['noSubProducts'] = 'Nom de sous-produit inconnu';
$GLOBALS['TL_LANG']['ERR']['emptyOrderHistory'] = 'Vous n\'avez rien commandé jusqu\'ici';
$GLOBALS['TL_LANG']['ERR']['orderNotFound'] = 'La commande désirée n\'a pas pu être trouvée';
$GLOBALS['TL_LANG']['ERR']['missingCurrencyFormat'] = 'Monnaie inconnue';
$GLOBALS['TL_LANG']['ERR']['searchNotEnabled'] = 'La fonction de recherche n\'est pas installée';
$GLOBALS['TL_LANG']['ERR']['isoLoginRequired'] = 'Vous devez être enregistré pour continuer';
$GLOBALS['TL_LANG']['ERR']['mandatoryOption'] = 'Choisisez un option';
$GLOBALS['TL_LANG']['ERR']['noAddressData'] = 'Votre adresse est nécessaire pour calculer les taxes';
$GLOBALS['TL_LANG']['ERR']['variantDuplicate'] = 'Il existe déjà une variante avec ces attributs.';
$GLOBALS['TL_LANG']['ERR']['breadcrumbEmpty'] = 'La catégorie filtrée est vide, tous les produits sont maintenant indiquées.';
$GLOBALS['TL_LANG']['ERR']['invalidCoupon'] = 'Coupon invalide';
$GLOBALS['TL_LANG']['ERR']['discountFactors'] = 'Prière de remplir des nombres entiers ou décimaux, au choix soit un pourcentage.';
$GLOBALS['TL_LANG']['ERR']['generalFactors'] = 'Prière de remplir des nombres entiers ou décimaux, au choix soit désigner avec + ou -, et au choix soit un pourcentage.';
$GLOBALS['TL_LANG']['ERR']['orderFailed'] = 'Fermez la session a échouer';
$GLOBALS['TL_LANG']['ERR']['specifyBillingAddress'] = 'Il n\' y pas d\'adresse de facturation. Prière de mentionner une adresse de facturation.';
$GLOBALS['TL_LANG']['ERR']['cc_num'] = 'Prière de mentionner le numéro d\'une carte de crédit valide';
$GLOBALS['TL_LANG']['ERR']['cc_type'] = 'Prière de sélectionner une carte de crédit.';
$GLOBALS['TL_LANG']['ERR']['cc_exp'] = 'Prière de mentionner une date d\'expiration de carte de crédit en format mm/aa.';
$GLOBALS['TL_LANG']['ERR']['cc_ccv'] = 'Prière de mentionner le numéro de vérification de la carte de crédit (3 ou 4 chiffres disponibles au recto ou au verso de la carte.';
$GLOBALS['TL_LANG']['ERR']['cc_match'] = 'Votre numéro de carte de crédit ne correspond pas au type de carte de crédit sélectionnée.';
$GLOBALS['TL_LANG']['ERR']['cc_exp_paypal'] = 'Prière de mentionner une date d\'expiration de carte de crédit en format mm/aa.';
$GLOBALS['TL_LANG']['ERR']['addressDoesNotExist'] = 'Cette adresse n\'existe pas dans votre carnet d\'adresses.';
$GLOBALS['TL_LANG']['MSC']['labelLanguage'] = 'Langue';
$GLOBALS['TL_LANG']['MSC']['editLanguage'] = 'Editer';
$GLOBALS['TL_LANG']['MSC']['deleteLanguage'] = 'Effacer';
$GLOBALS['TL_LANG']['MSC']['defaultLanguage'] = 'De retour';
$GLOBALS['TL_LANG']['MSC']['undefinedLanguage'] = 'Indéfini';
$GLOBALS['TL_LANG']['MSC']['ajaxLoadingMessage'] = 'Chargement';
$GLOBALS['TL_LANG']['MSC']['quantity'] = 'Quantité';
$GLOBALS['TL_LANG']['MSC']['downloadCount'] = '%s téléchargements';
$GLOBALS['TL_LANG']['MSC']['defaultSearchText'] = 'Produits rechercher';
$GLOBALS['TL_LANG']['MSC']['emptySelectOptionLabel'] = 'Prière de sélectionner';
$GLOBALS['TL_LANG']['MSC']['perPageLabel'] = 'Des produits par page';
$GLOBALS['TL_LANG']['MSC']['searchTermsLabel'] = 'Des mots-clés';
$GLOBALS['TL_LANG']['MSC']['searchLabel'] = 'Recherche';
$GLOBALS['TL_LANG']['MSC']['buttonLabel']['update'] = 'Mise à jour';
$GLOBALS['TL_LANG']['MSC']['pagerSectionTitleLabel'] = 'Page:';
$GLOBALS['TL_LANG']['MSC']['previousStep'] = 'Retour';
$GLOBALS['TL_LANG']['MSC']['nextStep'] = 'Continuer';
$GLOBALS['TL_LANG']['MSC']['confirmOrder'] = 'Commande';
$GLOBALS['TL_LANG']['MSC']['labelPerPage'] = 'Par page';
$GLOBALS['TL_LANG']['MSC']['labelProductVariants'] = 'Prière de sélectionner';
$GLOBALS['TL_LANG']['MSC']['removeProductLinkText'] = 'Supprimer';
$GLOBALS['TL_LANG']['MSC']['removeProductLinkTitle'] = 'Supprime %s de votre carte';
$GLOBALS['TL_LANG']['MSC']['shippingLabel'] = 'Livraison';
$GLOBALS['TL_LANG']['MSC']['paymentLabel'] = 'Paiement';
$GLOBALS['TL_LANG']['MSC']['grandTotalLabel'] = 'Commande totale:';
$GLOBALS['TL_LANG']['MSC']['selectItemPrompt'] = '(sélectionner)';
$GLOBALS['TL_LANG']['MSC']['actualPrice'] = 'Prix actuel';
$GLOBALS['TL_LANG']['ISO']['couponsSubmitLabel'] = 'Appliquer';
$GLOBALS['TL_LANG']['ISO']['checkout_address'] = 'Adresse';
$GLOBALS['TL_LANG']['ISO']['checkout_shipping'] = 'Livraison';
$GLOBALS['TL_LANG']['ISO']['checkout_payment'] = 'Paiement';
$GLOBALS['TL_LANG']['ISO']['billing_address'] = 'Adresse de facturation';
$GLOBALS['TL_LANG']['ISO']['shipping_address'] = 'Adresse de livraison';
$GLOBALS['TL_LANG']['ISO']['billing_shipping_address'] = 'Adresse de facturation & livraison';
$GLOBALS['TL_LANG']['ISO']['shipping_method'] = 'Mode de livraison';
$GLOBALS['TL_LANG']['ISO']['payment_method'] = 'Mode de facturation';
$GLOBALS['TL_LANG']['ISO']['order_conditions'] = 'Conditions de commande';
$GLOBALS['TL_LANG']['ISO']['changeCheckoutInfo'] = 'Changer';
$GLOBALS['TL_LANG']['ISO']['cc_num'] = 'Numéro de carte de crédit';
$GLOBALS['TL_LANG']['ISO']['cc_type'] = 'Type de carte de crédit';
$GLOBALS['TL_LANG']['ISO']['pay_with_paypal'][2] = 'Payer maintenant';
$GLOBALS['TL_LANG']['ISO']['pay_with_epay'][0] = 'Payer par ePaiement';
$GLOBALS['TL_LANG']['ISO']['pay_with_epay'][2] = 'Payer maintenant';
$GLOBALS['TL_LANG']['SHIP']['collection'][0] = 'Collection';
$GLOBALS['TL_LANG']['PAY']['cash'][0] = 'Cash';
$GLOBALS['TL_LANG']['PAY']['postfinance'][0] = 'Postfinance';
$GLOBALS['TL_LANG']['CCT']['mc'] = 'MasterCard';
$GLOBALS['TL_LANG']['CCT']['visa'] = 'Visa';
$GLOBALS['TL_LANG']['CCT']['amex'] = 'American Express';
$GLOBALS['TL_LANG']['CCT']['discover'] = 'Découvrir';
$GLOBALS['TL_LANG']['CCT']['diners'] = 'Diner\'s Club';
$GLOBALS['TL_LANG']['WGT']['mg'][0] = 'Milligramme (mg)';
$GLOBALS['TL_LANG']['WGT']['g'][0] = 'Gramme';

