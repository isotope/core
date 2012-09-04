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
$GLOBALS['TL_LANG']['tl_module']['iso_list_layout'][0]            = 'Modèle de liste de produits';
$GLOBALS['TL_LANG']['tl_module']['iso_list_layout'][1]            = 'Choisir une disposition la liste. Vous pouvez ajouter dispositions de liste personnalisées à <em>dossier templates</em>. La liste des fichiers modèle commence par <em>iso_list_</em> et nécessite l\'extension de fichier <em>.tpl</em>.';
$GLOBALS['TL_LANG']['tl_module']['iso_reader_layout'][0]          = 'Modèle de fiche produit';
$GLOBALS['TL_LANG']['tl_module']['iso_reader_layout'][1]          = 'Choisir un modèle de fiche. Vous pouvez ajouter des modèles personnalisés dans <em>dossier templates</em>. Les modèles commencent par <em>iso_reader_</em> et nécessitent l\'extension de fichier <em>.tpl</em>.';
$GLOBALS['TL_LANG']['tl_module']['iso_reader_jumpTo'][0]          = 'Aller à la page de la fiche produit';
$GLOBALS['TL_LANG']['tl_module']['iso_reader_jumpTo'][1]          = 'Ce paramètre définit la page à laquelle l\'utilisateur sera redirigé en cliquant un produit pour plus d\'infos.';
$GLOBALS['TL_LANG']['tl_module']['iso_cart_layout'][0]            = 'Modèle du panier';
$GLOBALS['TL_LANG']['tl_module']['iso_filterTpl']                 = array('Filter template', 'Please choose a filter template. You can add custom filter templates to folder <em>templates</em>. Filter template files start with <em>iso_filter_</em>.');
$GLOBALS['TL_LANG']['tl_module']['iso_cols'][0]                   = 'Colonnes';
$GLOBALS['TL_LANG']['tl_module']['iso_cols'][1]                   = 'Entrez un nombre de colonnes à afficher en largeur dans la liste des modèles';
$GLOBALS['TL_LANG']['tl_module']['iso_config_id'][0]              = 'Configuration de boutique';
$GLOBALS['TL_LANG']['tl_module']['iso_config_id'][1]              = 'Sélectionner la configuration de boutique pour laquelle ce module sera utilisé.';
$GLOBALS['TL_LANG']['tl_module']['iso_config_ids'][0]             = 'Configurations de boutique';
$GLOBALS['TL_LANG']['tl_module']['iso_config_ids'][1]             = 'Sélectionner les configurations de boutique pour laquelle ce module sera utilisé.';
$GLOBALS['TL_LANG']['tl_module']['iso_login_jumpTo'][0]           = 'Page de connexion';
$GLOBALS['TL_LANG']['tl_module']['iso_login_jumpTo'][1]           = 'Sélectionner la page où l\'utilisateur devra se connecter pour commander.';
$GLOBALS['TL_LANG']['tl_module']['iso_payment_modules'][0]        = 'Modes de paiement';
$GLOBALS['TL_LANG']['tl_module']['iso_payment_modules'][1]        = 'Sélectionnez un ou plusieurs modes de paiement pour ce module de commande.';
$GLOBALS['TL_LANG']['tl_module']['iso_shipping_modules'][0]       = 'Modes de livraison';
$GLOBALS['TL_LANG']['tl_module']['iso_shipping_modules'][1]       = 'Sélectionnez un ou plusieurs modes de livraison pour ce module de commande.';
$GLOBALS['TL_LANG']['tl_module']['iso_checkout_method'][0]        = 'Mode de commande';
$GLOBALS['TL_LANG']['tl_module']['iso_checkout_method'][1]        = 'Choisissez votre mode de commande';
$GLOBALS['TL_LANG']['tl_module']['iso_order_conditions'][0]       = 'Conditions de commande';
$GLOBALS['TL_LANG']['tl_module']['iso_order_conditions'][1]       = 'Choisissez un formulaire personnalisé utilisé pour afficher vos termes et conditions de vente (en option).';
$GLOBALS['TL_LANG']['tl_module']['iso_order_conditions_position'] = array('Position of order conditions form', 'Define if the order condition form should be shown before or after the products list.');
$GLOBALS['TL_LANG']['tl_module']['iso_addToAddressbook'][0]       = 'Ajouter au carnet d\'adresses';
$GLOBALS['TL_LANG']['tl_module']['iso_addToAddressbook'][1]       = 'Ajouter de nouvelles adresses au carnet d\'adresses des membres (si connecté).';
$GLOBALS['TL_LANG']['tl_module']['iso_emptyMessage']              = array('Définir un message vide', 'Définir un message personnalisé quand il n\'y a rien à afficher (liste de produit vide, panier vide, etc.)');
$GLOBALS['TL_LANG']['tl_module']['iso_noProducts'][0]             = 'Message pour la liste vide';
$GLOBALS['TL_LANG']['tl_module']['iso_noProducts'][1]             = 'Laisser vide pour le message par défaut.';
$GLOBALS['TL_LANG']['tl_module']['iso_emptyFilter']               = array('Define a message if no filter is set', 'Set a custom message when there is no filter set.');
$GLOBALS['TL_LANG']['tl_module']['iso_noFilter']                  = array('Message when no filter is set', 'Enter a custom message if there is no filter set.');
$GLOBALS['TL_LANG']['tl_module']['orderCompleteJumpTo'][0]        = 'Aller à la page de commande terminée';
$GLOBALS['TL_LANG']['tl_module']['orderCompleteJumpTo'][1]        = 'Sélectionner la page vers laquelle le client sera redirigé après sa commande validée.';
$GLOBALS['TL_LANG']['tl_module']['iso_jump_first'][0]             = 'Redirection vers le premier produit';
$GLOBALS['TL_LANG']['tl_module']['iso_jump_first'][1]             = 'Cocher cette case si les utilisateurs sont redirigés vers le premier produit de la liste.';
$GLOBALS['TL_LANG']['tl_module']['iso_hide_list']                 = array('Hide in reader mode', 'Hide product list when a product alias is found in the URL.');
$GLOBALS['TL_LANG']['tl_module']['iso_mail_customer'][0]          = 'E-mail de notification client';
$GLOBALS['TL_LANG']['tl_module']['iso_mail_customer'][1]          = 'Sélectionner l\'e-mail Isotope à envoyer aux clients quand ils ont passé une commande.';
$GLOBALS['TL_LANG']['tl_module']['iso_mail_admin'][0]             = 'E-mail de notification administrateur des ventes';
$GLOBALS['TL_LANG']['tl_module']['iso_mail_admin'][1]             = 'Sélectionner l\'e-mail Isotope à envoyer aux administrateurs des ventes quand un client passe une commande.';
$GLOBALS['TL_LANG']['tl_module']['iso_sales_email'][0]            = 'Adresse e-mail de l\'administrateur des ventes';
$GLOBALS['TL_LANG']['tl_module']['iso_sales_email'][1]            = 'Saisir une adresse e-mail autre que celle par défaut de l\'administrateur système pour stocker les notifications qui doivent être envoyés.';
$GLOBALS['TL_LANG']['tl_module']['iso_category_scope'][0]         = 'Portée de la catégorie';
$GLOBALS['TL_LANG']['tl_module']['iso_list_where']                = array('Condition', 'Ici, vous pouvez entrer une condition SQL pour filtrer les produits. Vous devez préfixer les champs avec "p1." (par ex. : <em>p1.featured=1</em> ou <em>p1.color!=\'red\'</em>)!');
$GLOBALS['TL_LANG']['tl_module']['iso_use_quantity'][0]           = 'Activer la quantité';
$GLOBALS['TL_LANG']['tl_module']['iso_use_quantity'][1]           = 'Permet aux utilisateurs de spécifier un ou plusieurs produits à acheter.';
$GLOBALS['TL_LANG']['tl_module']['iso_filterModules']             = array('Filter modules', 'Select the filter modules you want to consider for this product list.');
$GLOBALS['TL_LANG']['tl_module']['iso_filterFields'][0]           = 'Activer les filtres';
$GLOBALS['TL_LANG']['tl_module']['iso_filterFields'][1]           = 'Sélectionner les filtres à activer.';
$GLOBALS['TL_LANG']['tl_module']['iso_sortingFields'][0]          = 'Activer les champs de tri';
$GLOBALS['TL_LANG']['tl_module']['iso_sortingFields'][1]          = 'Sélectionner les champs de tri à activer.';
$GLOBALS['TL_LANG']['tl_module']['iso_searchFields'][0]           = 'Activer les champs de recherche';
$GLOBALS['TL_LANG']['tl_module']['iso_searchFields'][1]           = 'Sélectionner les champs de recherche à activer.';
$GLOBALS['TL_LANG']['tl_module']['iso_enableLimit'][0]            = 'Activer la limitation par page';
$GLOBALS['TL_LANG']['tl_module']['iso_enableLimit'][1]            = 'Permettre à l\'utilisateur de sélectionner le nombre d\'enregistrements à afficher par page.';
$GLOBALS['TL_LANG']['tl_module']['iso_perPage']                   = array('Per page options', 'Enter a comma separated list for the limit dropdown. The first option will be used as the default value. Values will automatically sort by number.');
$GLOBALS['TL_LANG']['tl_module']['iso_cart_jumpTo'][0]            = 'Aller à la page du panier';
$GLOBALS['TL_LANG']['tl_module']['iso_cart_jumpTo'][1]            = 'Ce paramètre définit la page vers laquelle l\'utilisateur sera redirigé lors d\'une vue complète du panier.';
$GLOBALS['TL_LANG']['tl_module']['iso_checkout_jumpTo'][0]        = 'Aller à la page de commande';
$GLOBALS['TL_LANG']['tl_module']['iso_checkout_jumpTo'][1]        = 'Ce paramètre définit la page vers laquelle l\'utilisateur sera redirigé au moment de compléter la transaction.';
$GLOBALS['TL_LANG']['tl_module']['iso_addProductJumpTo'][0]       = 'Aller à la page produit';
$GLOBALS['TL_LANG']['tl_module']['iso_addProductJumpTo'][1]       = 'Ce paramètre définit la page vers laquelle l\'utilisateur sera redirigé lorsqu\'il ajoute un article au panier, si autre que la page en cours.';
$GLOBALS['TL_LANG']['tl_module']['iso_listingSortField'][0]       = 'Champ de tri initial';
$GLOBALS['TL_LANG']['tl_module']['iso_listingSortDirection'][0]   = 'Sens initial du tri';
$GLOBALS['TL_LANG']['tl_module']['iso_listingSortDirection'][1]   = 'Sélectionnez un sens initial du tri';
$GLOBALS['TL_LANG']['tl_module']['iso_buttons'][0]                = 'Boutons';
$GLOBALS['TL_LANG']['tl_module']['iso_buttons'][1]                = 'Sélectionner les boutons à afficher';
$GLOBALS['TL_LANG']['tl_module']['iso_forward_review'][0]         = 'Avancer à la page résumé';
$GLOBALS['TL_LANG']['tl_module']['iso_forward_review'][1]         = 'Avancer l\'utilisateur à la page résumé si aucune donnée n\'est requise lors des étapes suivantes.';
$GLOBALS['TL_LANG']['tl_module']['iso_related_categories'][0]     = 'Catégories connexes';
$GLOBALS['TL_LANG']['tl_module']['iso_related_categories'][1]     = 'Sélectionner les catégories desquelles montrer les produits.';
$GLOBALS['TL_LANG']['tl_module']['iso_includeMessages']           = array('Include messaging', 'This setting allows the module to include any errors, notifications, or confirmations the visitor should be aware of.');
$GLOBALS['TL_LANG']['tl_module']['iso_continueShopping']          = array('Enable "Continue shopping" button', 'Add a link to the currently added product to the cart.');

//$GLOBALS['TL_LANG']['tl_module']['iso_forceNoProducts'][0] = 'Forcer le message vide';


/**
 * References
 */
$GLOBALS['TL_LANG']['tl_module']['iso_checkout_method_ref']['member']                  = 'Connexion/inscription requise';
$GLOBALS['TL_LANG']['tl_module']['iso_checkout_method_ref']['guest']                   = 'Commande invité seulement';
$GLOBALS['TL_LANG']['tl_module']['iso_checkout_method_ref']['both']                    = 'Les deux autorisés';
$GLOBALS['TL_LANG']['tl_module']['iso_category_scope_ref']['global']                   = 'Toutes catégories';
$GLOBALS['TL_LANG']['tl_module']['iso_category_scope_ref']['current_and_first_child']  = 'Catégorie actuelle et la première catégorie enfant';
$GLOBALS['TL_LANG']['tl_module']['iso_category_scope_ref']['current_and_all_children'] = 'Catégorie actuelle et toutes les catégories enfant';
$GLOBALS['TL_LANG']['tl_module']['iso_category_scope_ref']['current_category']         = 'Catégorie en cours';
$GLOBALS['TL_LANG']['tl_module']['iso_category_scope_ref']['parent']                   = 'Catégorie parente';
$GLOBALS['TL_LANG']['tl_module']['iso_category_scope_ref']['product']                  = 'Produit actuel des catégories';
$GLOBALS['TL_LANG']['tl_module']['sortingDirection']['ASC']                            = 'ASC';
$GLOBALS['TL_LANG']['tl_module']['sortingDirection']['DESC']                           = 'DESC';
$GLOBALS['TL_LANG']['tl_module']['iso_order_conditions_position']['before']            = 'Avant la liste des produits';
$GLOBALS['TL_LANG']['tl_module']['iso_order_conditions_position']['after']             = 'Après la liste des produits';


