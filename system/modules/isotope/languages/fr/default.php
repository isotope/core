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
 * Errors
 */
$GLOBALS['TL_LANG']['ERR']['systemColumn']                = 'Le nom `%s` est réservé pour l\'administration du système. Merci de choisir un autre nom.';
$GLOBALS['TL_LANG']['ERR']['missingButtonTemplate']       = 'Vous devez choisir un document de mise en page pour le bouton "%s".';
$GLOBALS['TL_LANG']['ERR']['order_conditions']            = 'Vous devez accepter les termes et conditions avant de continuer.';
$GLOBALS['TL_LANG']['ERR']['noStoreConfigurationSet']     = 'Il n\'existe pas de configuration pour cette boutique';
$GLOBALS['TL_LANG']['ERR']['noDefaultStoreConfiguration'] = 'Créez d\'abord une configuration de boutique.';
$GLOBALS['TL_LANG']['ERR']['productNameMissing']          = 'Nom de produit inconnu';
$GLOBALS['TL_LANG']['ERR']['noSubProducts']               = 'Nom de sous-produit inconnu';
$GLOBALS['TL_LANG']['ERR']['emptyOrderHistory']           = 'Vous n\'avez rien commandé jusqu\'ici.';
$GLOBALS['TL_LANG']['ERR']['orderNotFound']               = 'La commande désirée n\'a pas pu être trouvée.';
$GLOBALS['TL_LANG']['ERR']['missingCurrencyFormat']       = 'Monnaie inconnue';
$GLOBALS['TL_LANG']['ERR']['searchNotEnabled']            = 'La fonction de recherche n\'est pas installée !';
$GLOBALS['TL_LANG']['ERR']['isoLoginRequired']            = 'Vous devez être enregistré pour commander.';
$GLOBALS['TL_LANG']['ERR']['mandatoryOption']             = 'Choisissez une option';
$GLOBALS['TL_LANG']['ERR']['noAddressData']               = 'Votre adresse est nécessaire pour calculer les taxes !';
$GLOBALS['TL_LANG']['ERR']['variantDuplicate']            = 'Il existe déjà une variante avec ces attributs. Merci de choisir une autre combinaison.';
$GLOBALS['TL_LANG']['ERR']['breadcrumbEmpty']             = 'La catégorie filtrée est vide, tous les produits sont maintenant affichés.';
$GLOBALS['TL_LANG']['ERR']['discount']                    = 'Saisir des nombres entiers ou décimaux avec + ou - et éventuellement avec un pourcentage.';
$GLOBALS['TL_LANG']['ERR']['surcharge']                   = 'Saisir des nombres entiers ou décimaux éventuellement avec un pourcentage.';
$GLOBALS['TL_LANG']['ERR']['orderFailed']                 = 'Paiement échoué. Essayez encore une fois ou choisissez un autre mode de paiement';
$GLOBALS['TL_LANG']['ERR']['specifyBillingAddress']       = 'Il n\'y a pas d\'adresse de facturation. Veuillez mentionner une adresse de facturation.';
$GLOBALS['TL_LANG']['ERR']['cc_num']                      = 'Veuillez mentionner le numéro valide de la carte de crédit';
$GLOBALS['TL_LANG']['ERR']['cc_type']                     = 'Veuillez sélectionner une carte de crédit.';
$GLOBALS['TL_LANG']['ERR']['cc_exp']                      = 'Veuillez mentionner la date d\'expiration de la carte de crédit en format mm/aa';
$GLOBALS['TL_LANG']['ERR']['cc_ccv']                      = 'Veuillez mentionner le numéro de vérification de la carte de crédit (3 ou 4 chiffres disponibles au recto ou au verso de la carte).';
$GLOBALS['TL_LANG']['ERR']['cc_match']                    = 'Votre numéro de carte de crédit ne correspond pas au type de carte de crédit sélectionné.';
$GLOBALS['TL_LANG']['ERR']['addressDoesNotExist']         = 'Cette adresse n\'existe pas dans votre carnet d\'adresses.';
$GLOBALS['TL_LANG']['ERR']['noAddressBookEntries']        = 'Il n\'y a pas d\'entrées dans votre carnet d\'adresses.';
$GLOBALS['TL_LANG']['ERR']['cartMinSubtotal']             = 'Le montant minimum de commande est %s. Veuillez ajouter d\'autres produits avant de commander.';

/**
 * Miscellaneous
 */
$GLOBALS['TL_LANG']['MSC']['editLanguage']                      = 'Éditer';
$GLOBALS['TL_LANG']['MSC']['deleteLanguage']                    = 'Supprimer';
$GLOBALS['TL_LANG']['MSC']['defaultLanguage']                   = 'De retour';
$GLOBALS['TL_LANG']['MSC']['editingLanguage']                   = 'ATTENTION : vous éditez des données spécifiques à la langue !';
$GLOBALS['TL_LANG']['MSC']['deleteLanguageConfirm']             = 'Êtes-vous sûr de vouloir supprimer cette langue ? Il n\'y aura pas d\'annulation possible !';
$GLOBALS['TL_LANG']['MSC']['undefinedLanguage']                 = 'Indéfini';
$GLOBALS['TL_LANG']['MSC']['copyFallback']			= 'Duplicate Fallback';
$GLOBALS['TL_LANG']['MSC']['noSurcharges']                      = 'Pas de surtaxes trouvées.';
$GLOBALS['TL_LANG']['MSC']['ajaxLoadingMessage']                = 'Chargement...';
$GLOBALS['TL_LANG']['MSC']['orderDetailsHeadline']              = 'Numéro de commande de %s / %s';
$GLOBALS['TL_LANG']['MSC']['payment_processing']                = 'Votre paiement est en cours de traitement. Veuillez patienter...';
$GLOBALS['TL_LANG']['MSC']['authorizedotnet_process_failed']    = 'Votre paiement n\'a pas pu être traité. <br /><br />Cause: %s';
$GLOBALS['TL_LANG']['MSC']['mmNoUploads']                       = 'Aucun fichier envoyé.';
$GLOBALS['TL_LANG']['MSC']['mmUpload']                          = 'Envoyer un nouveau fichier';
$GLOBALS['TL_LANG']['MSC']['quantity']                          = 'Quantité';
$GLOBALS['TL_LANG']['MSC']['order_conditions']                  = 'J\'accepte les termes et conditions';
$GLOBALS['TL_LANG']['MSC']['defaultSearchText']                 = 'Produits à rechercher';
$GLOBALS['TL_LANG']['MSC']['blankSelectOptionLabel']            = '-';
$GLOBALS['TL_LANG']['MSC']['emptySelectOptionLabel']            = 'Veuillez sélectionner...';
$GLOBALS['TL_LANG']['MSC']['downloadsLabel']                    = 'Vos produits téléchargeables';
$GLOBALS['TL_LANG']['MSC']['priceRangeLabel']                   = '<span class="from">À partir de</span> %s';
$GLOBALS['TL_LANG']['MSC']['detailLabel']                       = 'Afficher les détails';
$GLOBALS['TL_LANG']['MSC']['searchTextBoxLabel']                = 'Chercher le terme :';
$GLOBALS['TL_LANG']['MSC']['searchFieldsLabel']                 = 'Champs de recherche :';
$GLOBALS['TL_LANG']['MSC']['perPageLabel']                      = 'Produits par page';
$GLOBALS['TL_LANG']['MSC']['searchTermsLabel']                  = 'Mots-clés';
$GLOBALS['TL_LANG']['MSC']['submitLabel']                       = 'Valider';
$GLOBALS['TL_LANG']['MSC']['clearFiltersLabel']                 = 'Effacer les filtres';
$GLOBALS['TL_LANG']['MSC']['buttonLabel']['update']             = 'Mettre à jour';
$GLOBALS['TL_LANG']['MSC']['buttonLabel']['add_to_cart']        = 'Ajouter au panier';
$GLOBALS['TL_LANG']['MSC']['pagerSectionTitleLabel']            = 'Page :';
$GLOBALS['TL_LANG']['MSC']['orderByLabel']                      = 'Trier par :';
//$GLOBALS['TL_LANG']['MSC']['buttonActionString']['add_to_cart'] = 'Mettre le produit %s dans le panier';
$GLOBALS['TL_LANG']['MSC']['noProducts']                        = 'Aucun produit trouvé.';
$GLOBALS['TL_LANG']['MSC']['invalidProductInformation']         = 'Désolé, cette information de produit ne se trouve pas dans notre boutique. Pour obtenir de l\'aide, veuillez nous contacter.';
$GLOBALS['TL_LANG']['MSC']['productOptionsLabel']               = 'Options';
$GLOBALS['TL_LANG']['MSC']['previousStep']                      = 'Retour';
$GLOBALS['TL_LANG']['MSC']['nextStep']                          = 'Continuer';
$GLOBALS['TL_LANG']['MSC']['confirmOrder']                      = 'Commander';
$GLOBALS['TL_LANG']['MSC']['noCategoriesAssociated']            = 'Pas de catégories associées avec ce produit';
$GLOBALS['TL_LANG']['MSC']['labelPerPage']                      = 'Par page';
$GLOBALS['TL_LANG']['MSC']['labelSortBy']                       = 'Trier par';
$GLOBALS['TL_LANG']['MSC']['labelSubmit']                       = 'Soumettre';
$GLOBALS['TL_LANG']['MSC']['labelProductVariants']              = 'Veuillez sélectionner';
$GLOBALS['TL_LANG']['MSC']['removeProductLinkText']             = 'Supprimer';
$GLOBALS['TL_LANG']['MSC']['noItemsInCart']                     = 'Il n\'y a pas d\'article dans votre panier.';
$GLOBALS['TL_LANG']['MSC']['addedToCart']                       = 'Le produit sélectionné a bien été ajouté à votre panier.';
$GLOBALS['TL_LANG']['MSC']['cartMerged']                        = 'The products from your last visit have been readded. Please review your shopping cart items.';
$GLOBALS['TL_LANG']['MSC']['removeProductLinkTitle']            = 'Supprime %s de votre panier';
$GLOBALS['TL_LANG']['MSC']['subTotalLabel']                     = 'Sous-total de la commande :';
$GLOBALS['TL_LANG']['MSC']['shippingLabel']                     = 'Livraison';
$GLOBALS['TL_LANG']['MSC']['paymentLabel']                      = 'Paiement';
$GLOBALS['TL_LANG']['MSC']['taxLabel']                          = '%s Taxe :';
$GLOBALS['TL_LANG']['MSC']['grandTotalLabel']                   = 'Total de la commande :';
$GLOBALS['TL_LANG']['MSC']['shippingOptionsLabel']              = 'Options de livraison choisis :';
$GLOBALS['TL_LANG']['MSC']['noVariants']                        = 'Aucune variante de produit trouvée.';
$GLOBALS['TL_LANG']['MSC']['generateSubproducts']               = 'Créer des sous-produits';
$GLOBALS['TL_LANG']['MSC']['selectItemPrompt']                  = '(sélectionner)';
$GLOBALS['TL_LANG']['MSC']['actualPrice']                       = 'Prix actuel';
$GLOBALS['TL_LANG']['MSC']['noPaymentModules']                  = 'Actuellement, il n\'y a pas d\'options de paiement.';
$GLOBALS['TL_LANG']['MSC']['noShippingModules']                 = 'Actuellement, il n\'y a pas d\'options de livraison.';
$GLOBALS['TL_LANG']['MSC']['noOrderEmails']                     = 'Aucun e-mail de commande trouvé.';
$GLOBALS['TL_LANG']['MSC']['noOrders']                          = 'Aucune commande trouvée.';
$GLOBALS['TL_LANG']['MSC']['downloadsRemaining']                = '<br />%s téléchargements restants';
$GLOBALS['TL_LANG']['ISO']['couponsInputLabel']                 = 'Code promotionel';
$GLOBALS['TL_LANG']['ISO']['couponsHeadline']                   = 'Saisir un code promotionel';
$GLOBALS['TL_LANG']['ISO']['couponsSubmitLabel']                = 'Appliquer';
$GLOBALS['TL_LANG']['MSC']['cartBT']                            = 'Panier d\'achat';
$GLOBALS['TL_LANG']['MSC']['checkoutBT']                        = 'Commander';
$GLOBALS['TL_LANG']['MSC']['continueShoppingBT']                = 'Continuez vos achats';
$GLOBALS['TL_LANG']['MSC']['updateCartBT']                      = 'Mettre le panier à jour';
$GLOBALS['TL_LANG']['MSC']['orderStatusHeadline']               = 'Statut de commande: %s';
$GLOBALS['TL_LANG']['MSC']['checkboutStepBack']                 = 'Retourner à l\'étape "%s"';

//Addresses
$GLOBALS['TL_LANG']['MSC']['createNewAddressLabel']             = 'Créer une nouvelle adresse';
$GLOBALS['TL_LANG']['MSC']['useBillingAddress']                 = 'Utiliser l\'adresse de paiement';
$GLOBALS['TL_LANG']['MSC']['useCustomerAddress']                = 'Utiliser l\'adresse du client';
$GLOBALS['TL_LANG']['MSC']['differentShippingAddress']          = 'Adresse de livraison différente';

$GLOBALS['TL_LANG']['MSC']['addressBookLabel']                  = 'Adresses';
$GLOBALS['TL_LANG']['MSC']['editAddressLabel']                  = 'Éditer';
$GLOBALS['TL_LANG']['MSC']['deleteAddressLabel']                = 'Supprimer';
$GLOBALS['TL_LANG']['MSC']['deleteAddressConfirm']              = 'Voulez-vous vraiment supprimer cette adresse ? Cette action ne peut pas être annulée.';

//Invoice language Entries
$GLOBALS['TL_LANG']['MSC']['iso_invoice_title']                 = 'Facture';
$GLOBALS['TL_LANG']['MSC']['iso_order_status']                  = 'Statut';
$GLOBALS['TL_LANG']['MSC']['iso_order_date']                    = 'Date de la commande';
$GLOBALS['TL_LANG']['MSC']['iso_billing_address_header']        = 'Adresse de facturation';
$GLOBALS['TL_LANG']['MSC']['iso_shipping_address_header']       = 'Adresse de livraison';
$GLOBALS['TL_LANG']['MSC']['iso_tax_header']                    = 'Taxe';
$GLOBALS['TL_LANG']['MSC']['iso_subtotal_header']               = 'Sous-total';
$GLOBALS['TL_LANG']['MSC']['iso_order_shipping_header']         = 'Livraison et traitement';
$GLOBALS['TL_LANG']['MSC']['iso_order_grand_total_header']      = 'Grand total';
$GLOBALS['TL_LANG']['MSC']['iso_order_items']                   = 'Articles';
$GLOBALS['TL_LANG']['MSC']['iso_order_sku']                     = 'Référence (SKU)';
$GLOBALS['TL_LANG']['MSC']['iso_quantity_header']               = 'Quantité';
$GLOBALS['TL_LANG']['MSC']['iso_price_header']                  = 'Prix';
$GLOBALS['TL_LANG']['MSC']['iso_sku_header']                    = 'Réf.';
$GLOBALS['TL_LANG']['MSC']['iso_product_name_header']           = 'Nom de produit';
$GLOBALS['TL_LANG']['MSC']['iso_card_name_title']               = 'Nom sur la carte de crédit';

$GLOBALS['TL_LANG']['MSC']['low_to_high']                       = 'De bas en haut';
$GLOBALS['TL_LANG']['MSC']['high_to_low']                       = 'De haut en bas';
$GLOBALS['TL_LANG']['MSC']['a_to_z']                            = 'De A à Z';
$GLOBALS['TL_LANG']['MSC']['z_to_a']                            = 'De Z à A';
$GLOBALS['TL_LANG']['MSC']['old_to_new']                        = 'Du plus ancien au plus récent';
$GLOBALS['TL_LANG']['MSC']['new_to_old']                        = 'Du plus récent au plus ancien';


/**
 * Content elements
 */
$GLOBALS['TL_LANG']['CTE']['isotope']                           = 'Isotope eCommerce';


/**
 * Isotope module labels
 */
$GLOBALS['TL_LANG']['ISO']['productSingle']                     = '1 produit';
$GLOBALS['TL_LANG']['ISO']['productMultiple']                   = '%s produits';

$GLOBALS['TL_LANG']['ISO']['shipping_address_message']          = 'Saisissez vos informations de livraison ou choisissez une adresse existante.';
$GLOBALS['TL_LANG']['ISO']['billing_address_message']           = 'Saisissez vos informations de facturation ou choisissez une adresse existante.';
$GLOBALS['TL_LANG']['ISO']['billing_address_guest_message']     = 'Saisissez vos informations de facturation';
$GLOBALS['TL_LANG']['ISO']['customer_address_message']          = 'Saisissez vos informations client ou sélectionnez une adresse existante.';
$GLOBALS['TL_LANG']['ISO']['customer_address_guest_message']    = 'Saisissez vos informations client.';
$GLOBALS['TL_LANG']['ISO']['shipping_method_message']           = 'Sélectionnez un mode de livraison.';
$GLOBALS['TL_LANG']['ISO']['shipping_method_missing']           = 'Choisissez un mode de livraison s.v.p.';
$GLOBALS['TL_LANG']['ISO']['payment_method_message']            = 'Saisissez vos informations de paiement';
$GLOBALS['TL_LANG']['ISO']['payment_method_missing']            = 'Sélectionnez une méthode de paiement';
$GLOBALS['TL_LANG']['ISO']['order_review_message']              = 'Contrôlez et confirmez les détails de votre commande';

$GLOBALS['TL_LANG']['ISO']['checkout_address']                  = 'Adresse';
$GLOBALS['TL_LANG']['ISO']['checkout_shipping']                 = 'Livraison';
$GLOBALS['TL_LANG']['ISO']['checkout_payment']                  = 'Paiement';
$GLOBALS['TL_LANG']['ISO']['checkout_review']                   = 'Résumé';
$GLOBALS['TL_LANG']['ISO']['billing_address']                   = 'Adresse de facturation';
$GLOBALS['TL_LANG']['ISO']['shipping_address']                  = 'Adresse de livraison';
$GLOBALS['TL_LANG']['ISO']['billing_shipping_address']          = 'Adresse de facturation & de livraison';
$GLOBALS['TL_LANG']['ISO']['customer_address']                  = 'Adresse du client';
$GLOBALS['TL_LANG']['ISO']['shipping_method']                   = 'Mode de livraison';
$GLOBALS['TL_LANG']['ISO']['payment_method']                    = 'Mode de facturation';
$GLOBALS['TL_LANG']['ISO']['order_conditions']                  = 'Conditions de commande';
$GLOBALS['TL_LANG']['ISO']['order_review']                      = 'Revue de la commande';
$GLOBALS['TL_LANG']['ISO']['changeCheckoutInfo']                = 'Modifier';
$GLOBALS['TL_LANG']['ISO']['cc_num']                            = 'Numéro de carte de crédit';
$GLOBALS['TL_LANG']['ISO']['cc_type']                           = 'Type de carte de crédit';
$GLOBALS['TL_LANG']['ISO']['cc_ccv']                            = 'Numéro CCV (code de 3 ou 4 chiffres)';
$GLOBALS['TL_LANG']['ISO']['cc_exp_paypal']                     = 'Date d\'expiration de la carte de crédit';
$GLOBALS['TL_LANG']['ISO']['cc_exp_date']                       = 'Mois /année de l\'expiration';
$GLOBALS['TL_LANG']['ISO']['cc_exp_month']                      = 'Mois de l\'expiration';
$GLOBALS['TL_LANG']['ISO']['cc_exp_year']                       = 'Année de l\'expiration';
$GLOBALS['TL_LANG']['ISO']['cc_issue_number']                   = 'Numéro de parution de la carte de crédit, 2 chiffres (pour les cartes Maestro et Solo)';
$GLOBALS['TL_LANG']['ISO']['cc_start_date']                     = 'Date du début de la carte de crédit (pour les cartes Maestro et Solo)';
$GLOBALS['TL_LANG']['MSC']['pay_with_cc'][0]                    = 'Procéder au paiement';
$GLOBALS['TL_LANG']['MSC']['pay_with_cc'][1]                    = 'Merci d\'entrer les informations requises pour traiter votre paiement.';
$GLOBALS['TL_LANG']['MSC']['pay_with_cc'][2]                    = 'Payer maintenant';
$GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0]              = 'Procéder au paiement';
$GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1]              = 'Vous allez être redirigés sur le site du fournisseur de services de paiement. Si vous n\'êtes pas automatiquement redirigé sur le site, appuyez sur le bouton "Payer maintenant".';
$GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][2]              = 'Payer maintenant';
$GLOBALS['TL_LANG']['MSC']['backendPaymentEPay']                = 'Merci d\'utiliser l\'adresse suivante pour accéder au panneau d\'administration ePay';
$GLOBALS['TL_LANG']['ISO']['backendPaymentNotFound']            = 'Module de paiement inconnu!';
$GLOBALS['TL_LANG']['ISO']['backendShippingNotFound']           = 'Module de livraison inconnu!';
$GLOBALS['TL_LANG']['ISO']['backendPaymentNoInfo']              = 'Ce module de paiement ne fournit pas d\'informations supplémentaires';
$GLOBALS['TL_LANG']['ISO']['backendShippingNoInfo']             = 'Ce module de livraison ne fournit pas d\'informations supplémentaires';


/**
 * Miscellaneous
 */
$GLOBALS['ISO_LANG']['MSC']['useDefault']                       = 'Utiliser la valeur par défaut';
$GLOBALS['ISO_LANG']['MSC']['activeStep']			= 'active step: ';
$GLOBALS['ISO_LANG']['MSC']['productcacheLoading']	        = 'Loading products...';
$GLOBALS['ISO_LANG']['MSC']['productcacheNoscript']	        = 'Votre navigateur ne supporte pas JavaScript. Veuillez, s\'il vous plaît, <a href="%s">cliquer ici</a> pour afficher la liste des produits.';
$GLOBALS['ISO_LANG']['MSC']['noFilesInFolder']		        = 'Aucun fichier dans ce dossier';
$GLOBALS['ISO_LANG']['MSC']['loadingProductData']	        = 'Loading product data…';
$GLOBALS['ISO_LANG']['MSC']['templateConfig']		        = '%s (Store Config: %s)';
$GLOBALS['ISO_LANG']['MSC']['templateTheme']		        = '%s (Theme: %s)';
$GLOBALS['ISO_LANG']['MSC']['splittedTaxRate']		        = 'Splitted';
$GLOBALS['ISO_LANG']['MSC']['newOrders']			= 'You have %s order(s) with status "%s"';
$GLOBALS['ISO_LANG']['MSC']['checkoutStep']			= 'Step %s of %s (%s) - ';


/**
 * Shipping modules
 */
$GLOBALS['ISO_LANG']['SHIP']['flat'][0]                         = 'Livraison à bas prix';
$GLOBALS['ISO_LANG']['SHIP']['weight_total'][0]                 = 'Poids total de la livraison';
$GLOBALS['ISO_LANG']['SHIP']['order_total'][0]                  = 'Commande totale de la livraison';
$GLOBALS['ISO_LANG']['SHIP']['collection'][0]                   = 'Collection';
$GLOBALS['ISO_LANG']['SHIP']['ups'][0]                          = 'Tarifs UPS et service de livraison';
$GLOBALS['ISO_LANG']['SHIP']['usps'][0]                         = 'Tarifs USPS et service de livraison';


/**
 * Payment modules
 */
$GLOBALS['ISO_LANG']['PAY']['cash'][0]                          = 'Paiement en espèces';
$GLOBALS['ISO_LANG']['PAY']['cash'][1]                          = 'Choisissez cette option pour tous le paiements en mode hors connexion';
$GLOBALS['ISO_LANG']['PAY']['paypal'][0]                        = 'Passage à la caisse standard PayPal';
$GLOBALS['ISO_LANG']['PAY']['paypal'][1]                        = 'Ce module Paypal supporte IPN (notification instantanée de paiement)';
$GLOBALS['ISO_LANG']['PAY']['paypalpayflowpro'][0]              = 'PayPal Payflow Pro';
$GLOBALS['ISO_LANG']['PAY']['paypalpayflowpro'][1]              = 'Le module PayPal Payflow offre un portail de service complet pour cartes de crédit, une solution robuste pour presque tous les sites de e-commerce';
$GLOBALS['ISO_LANG']['PAY']['postfinance'][0]                   = 'Postfinance';
$GLOBALS['ISO_LANG']['PAY']['postfinance'][1]                   = 'Portail du service de paiement du système de la poste Suisse pour différents types de cartes. Le Webshop est instantanément informé de toute transaction réussie';
$GLOBALS['ISO_LANG']['PAY']['authorizedotnet'][0]               = 'Authorize.net';
$GLOBALS['ISO_LANG']['PAY']['authorizedotnet'][1]               = 'Portail de paiement Authorize.net';
$GLOBALS['ISO_LANG']['PAY']['cybersource'][0]                   = 'Cybersource';
$GLOBALS['ISO_LANG']['PAY']['cybersource'][1]                   = 'Pour les utilisateurs de Cybersource. Utilise Simple Order API.';


/**
 * Galleries
 */
$GLOBALS['ISO_LANG']['GAL']['default'][0]                       = 'Galerie standard (Lightbox/Mediabox)';
$GLOBALS['ISO_LANG']['GAL']['default'][1]                       = '<p>Se sert de Lightbox/Mediabox pour des images de grande taille. Assurez-vous d\'avoir bien sélectionné le template "moo_" approprié dans votre configuration de mise en page.</p><p>Vous pouvez fournir un attribut "rel" à la cible du lien comme par exemple "tl_files/video.mov|lightbox[400 300]". Si aucun attribut "rel" n\'est fourni, le lien sera ouvert dans une nouvelle fenêtre.</p>';
$GLOBALS['ISO_LANG']['GAL']['inline'][0]                        = 'Galerie en ligne';
$GLOBALS['ISO_LANG']['GAL']['inline'][1]                        = 'En cliquant sur la galerie, elle se chargera à la place de l\'image principale.';
$GLOBALS['ISO_LANG']['GAL']['zoom']                             = array('Galerie en ligne avec un effet de zoom', 'En plus du comportement de la galerie en ligne, elle ajoute également une fonction de zoom à l\'image principale.');


/**
 * Product types
 */
$GLOBALS['ISO_LANG']['PRODUCT']['regular'][0]                   = 'Produit standard';
$GLOBALS['ISO_LANG']['PRODUCT']['regular'][1]                   = 'Produit standard. Sélectionnez-le s\'il n\'y a pas d\'autres possibilités.';


/**
 * Credit card types
 */
$GLOBALS['ISO_LANG']['CCT']['mc']                               = 'MasterCard';
$GLOBALS['ISO_LANG']['CCT']['visa']                             = 'Visa';
$GLOBALS['ISO_LANG']['CCT']['amex']                             = 'American Express';
$GLOBALS['ISO_LANG']['CCT']['discover']                         = 'Découvrir';
$GLOBALS['ISO_LANG']['CCT']['jcb']                              = 'JBC';
$GLOBALS['ISO_LANG']['CCT']['diners']                           = 'Diner\'s Club';
$GLOBALS['ISO_LANG']['CCT']['enroute']                          = 'EnRoute';
$GLOBALS['ISO_LANG']['CCT']['carte_blanche']                    = 'Carte Blanche';
$GLOBALS['ISO_LANG']['CCT']['jal']                              = 'JAL';
$GLOBALS['ISO_LANG']['CCT']['maestro']                          = 'Maestro UK';
$GLOBALS['ISO_LANG']['CCT']['delta']                            = 'Delta';
$GLOBALS['ISO_LANG']['CCT']['solo']                             = 'Solo';
$GLOBALS['ISO_LANG']['CCT']['visa_electron']                    = 'Visa Electron';
$GLOBALS['ISO_LANG']['CCT']['dankort']                          = 'Dankort';
$GLOBALS['ISO_LANG']['CCT']['laser']                            = 'Laser';
$GLOBALS['ISO_LANG']['CCT']['carte_bleue']                      = 'Carte Bleue';
$GLOBALS['ISO_LANG']['CCT']['carta_si']                         = 'Carta Si';
$GLOBALS['ISO_LANG']['CCT']['enc_acct_num']                     = 'Numéro de compte crypté';
$GLOBALS['ISO_LANG']['CCT']['uatp']                             = 'Universal Air Travel Program';
$GLOBALS['ISO_LANG']['CCT']['maestro_intl']                     = 'Maestro International';
$GLOBALS['ISO_LANG']['CCT']['ge_money_uk']                      = 'GE Money UK';


/**
 * Weight Units
 * http://www.metric-conversions.org/weight/weight-conversions.htm
 */
$GLOBALS['ISO_LANG']['WGT']['mg'][0]                            = 'Milligramme (mg)';
$GLOBALS['ISO_LANG']['WGT']['mg'][1]                            = 'Unité de masse correspondant à un millième de gramme';
$GLOBALS['ISO_LANG']['WGT']['g'][0]                             = 'Gramme (g)';
$GLOBALS['ISO_LANG']['WGT']['g'][1]                             = 'Unité métrique de poids correspondant à un millième de kilogramme';
$GLOBALS['ISO_LANG']['WGT']['kg'][0]                            = 'Kilogramme (kg)';
$GLOBALS['ISO_LANG']['WGT']['kg'][1]                            = 'Un kilogramme est l\'équivalent de 1\'000 grammes ou de 2,2  livres; la masse d\'un litre d\'eau';
$GLOBALS['ISO_LANG']['WGT']['t'][0]                             = 'Tonne métrique (t)';
$GLOBALS['ISO_LANG']['WGT']['t'][1]                             = 'Unité de poids kilogramme correspondant à 1\'000 kilogrammes ou 2,204.6 livres';
$GLOBALS['ISO_LANG']['WGT']['ct'][0]                            = 'Carats (ct)';
$GLOBALS['ISO_LANG']['WGT']['ct'][1]                            = 'Unité de poids utilisée pour pierres précieuses. Un carat correspondant à 1/5 d\'un gramme (200 milligrammes). Prenez note que carat avec un "K" est une mesure pour la pureté d\'un alliage d\'or';
$GLOBALS['ISO_LANG']['WGT']['oz'][0]                            = 'Once (oz)';
$GLOBALS['ISO_LANG']['WGT']['oz'][1]                            = 'Unité de poids correspondant à 1/16 d\'un livre ou 28.35 grammes.';
$GLOBALS['ISO_LANG']['WGT']['lb'][0]                            = 'Livre (lb)';
$GLOBALS['ISO_LANG']['WGT']['lb'][1]                            = 'Unité de masse correspondant à 16 onces';
$GLOBALS['ISO_LANG']['WGT']['st'][0]                            = 'Stone (st)';
$GLOBALS['ISO_LANG']['WGT']['st'][1]                            = 'Une mesure britannique correspondant à 14 livres';
$GLOBALS['ISO_LANG']['WGT']['grain'][0]                         = 'Grain';
$GLOBALS['ISO_LANG']['WGT']['grain'][1]                         = '1/7000 d\'un livre, correspondant à 64.799 milligrammes';


/**
 * Attributes
 */
$GLOBALS['ISO_LANG']['ATTR']['text'][0]                         = 'Texte (jusqu\'à 255 caractères)';
$GLOBALS['ISO_LANG']['ATTR']['text'][1]                         = 'Un champ de saisie en ligne pour un texte court ou moyen.';
$GLOBALS['ISO_LANG']['ATTR']['textarea'][0]                     = 'Texte long (plus de 255 caractères)';
$GLOBALS['ISO_LANG']['ATTR']['textarea'][1]                     = 'Un champ de saisie multiligne pour un texte moyen ou long.';
$GLOBALS['ISO_LANG']['ATTR']['select'][0]                       = 'Liste déroulante';
$GLOBALS['ISO_LANG']['ATTR']['select'][1]                       = 'Un menu déroulant simple ou multiligne. Ce type de champ est adapté pour les variantes de produits.';
$GLOBALS['ISO_LANG']['ATTR']['radio'][0]                        = 'Bouton radio';
$GLOBALS['ISO_LANG']['ATTR']['radio'][1]                        = 'Une liste des multiples options d\'où chacune peut être sélectionnée. Ce type de champ est adapté pour les variantes de produits.';
$GLOBALS['ISO_LANG']['ATTR']['checkbox'][0]                     = 'Case à cocher';
$GLOBALS['ISO_LANG']['ATTR']['checkbox'][1]                     = 'Une liste des multiples options à partir de laquelle chacune peut être sélectionnée.';
$GLOBALS['ISO_LANG']['ATTR']['mediaManager'][0]                 = 'Gestionnaire de médias';
$GLOBALS['ISO_LANG']['ATTR']['mediaManager'][1]                 = 'Une liste des multiples options d\'où chacune peut être sélectionnée. Ce type de champ est adapté pour les variantes de produits.';
$GLOBALS['ISO_LANG']['ATTR']['conditionalselect'][0]            = 'Liste déroulante conditionnelle';
$GLOBALS['ISO_LANG']['ATTR']['conditionalselect'][1]            = 'Montrer les options de sélection basées sur un autre menu de sélection.';
$GLOBALS['ISO_LANG']['ATTR']['fileTree']			= array('Arborescence de fichiers', 'Une arborescence de fichiers pour un ou plusieurs fichiers et dossiers.');
$GLOBALS['ISO_LANG']['ATTR']['downloads']			= array('Téléchargements', 'Télécharger les fichiers du produit, par exemple manuels, fiches techniques, etc.');
$GLOBALS['ISO_LANG']['ATTR']['upload']				= array('Envoi de fichier', 'Champ d\'une ligne pour l\'envoi d\'un fichier local vers le serveur.');


/**
 * Currencies
 */
$GLOBALS['ISO_LANG']['CUR']['AED'] = 'AED - Dirham des Émirats arabes unis';
$GLOBALS['ISO_LANG']['CUR']['AFN'] = 'AFN - Afghani';
$GLOBALS['ISO_LANG']['CUR']['ALL'] = 'ALL - Lek';
$GLOBALS['ISO_LANG']['CUR']['AMD'] = 'AMD - Dram';
$GLOBALS['ISO_LANG']['CUR']['ANG'] = 'ANG - Florin des Antilles néerlandaises';
$GLOBALS['ISO_LANG']['CUR']['AOA'] = 'AOA - Kwanza';
$GLOBALS['ISO_LANG']['CUR']['ARS'] = 'ARS - Nouveau peso argentin';
$GLOBALS['ISO_LANG']['CUR']['AUD'] = 'AUD - Dollar australien';
$GLOBALS['ISO_LANG']['CUR']['AWG'] = 'AWG - Florin d’Aruba';
$GLOBALS['ISO_LANG']['CUR']['AZN'] = 'AZN - Manat azerbaïdjanais';
$GLOBALS['ISO_LANG']['CUR']['BAM'] = 'BAM - Mark convertible de Bosnie-Herzégovine';
$GLOBALS['ISO_LANG']['CUR']['BBD'] = 'BBD - Dollar de la Barbade';
$GLOBALS['ISO_LANG']['CUR']['BDT'] = 'BDT - Taka';
$GLOBALS['ISO_LANG']['CUR']['BGN'] = 'BGN - Lev bulgare';
$GLOBALS['ISO_LANG']['CUR']['BHD'] = 'BHD - Dinar bahreïni';
$GLOBALS['ISO_LANG']['CUR']['BIF'] = 'BIF - Franc burundais';
$GLOBALS['ISO_LANG']['CUR']['BMD'] = 'BMD - Dollar des Bermudes';
$GLOBALS['ISO_LANG']['CUR']['BND'] = 'BND - Dollar de Brunei';
$GLOBALS['ISO_LANG']['CUR']['BOB'] = 'BOB - Boliviano bolivien';
$GLOBALS['ISO_LANG']['CUR']['BRL'] = 'BRL - Réal brésilien';
$GLOBALS['ISO_LANG']['CUR']['BSD'] = 'BSD - Dollar bahaméen';
$GLOBALS['ISO_LANG']['CUR']['BTN'] = 'BTN - Ngultrum';
$GLOBALS['ISO_LANG']['CUR']['BWP'] = 'BWP - Pula';
$GLOBALS['ISO_LANG']['CUR']['BYR'] = 'BYR - Rouble biélorusse';
$GLOBALS['ISO_LANG']['CUR']['BZD'] = 'BZD - Dollar du Belize';
$GLOBALS['ISO_LANG']['CUR']['CAD'] = 'CAD - Dollar canadien';
$GLOBALS['ISO_LANG']['CUR']['CDF'] = 'CDF - Franc congolais';
$GLOBALS['ISO_LANG']['CUR']['CHF'] = 'CHF - Franc suisse';
$GLOBALS['ISO_LANG']['CUR']['CLF'] = 'CLF - Unidades de Fomento';
$GLOBALS['ISO_LANG']['CUR']['CLP'] = 'CLP - Peso chilien';
$GLOBALS['ISO_LANG']['CUR']['CNY'] = 'CNY - Yuan Renminbi';
$GLOBALS['ISO_LANG']['CUR']['COP'] = 'COP - Peso colombien';
$GLOBALS['ISO_LANG']['CUR']['CRC'] = 'CRC - Colón costaricien';
$GLOBALS['ISO_LANG']['CUR']['CUC'] = 'CUC - Peso convertible cubain';
$GLOBALS['ISO_LANG']['CUR']['CUP'] = 'CUP - Peso cubain';
$GLOBALS['ISO_LANG']['CUR']['CVE'] = 'CVE - Escudo cap-verdien';
$GLOBALS['ISO_LANG']['CUR']['CZK'] = 'CZK - Couronne tchèque';
$GLOBALS['ISO_LANG']['CUR']['DJF'] = 'DJF - Franc de Djibouti';
$GLOBALS['ISO_LANG']['CUR']['DKK'] = 'DKK - Couronne danoise';
$GLOBALS['ISO_LANG']['CUR']['DOP'] = 'DOP - Peso dominicain';
$GLOBALS['ISO_LANG']['CUR']['DZD'] = 'DZD - Dinar algérien';
$GLOBALS['ISO_LANG']['CUR']['EGP'] = 'EGP - Livre égyptienne';
$GLOBALS['ISO_LANG']['CUR']['ERN'] = 'ERN - Nakfa de l\'Érythrée ';
$GLOBALS['ISO_LANG']['CUR']['ETB'] = 'ETB - Birr éthiopien';
$GLOBALS['ISO_LANG']['CUR']['EUR'] = 'EUR - Euro';
$GLOBALS['ISO_LANG']['CUR']['FJD'] = 'FJD - Dollar de Fidji';
$GLOBALS['ISO_LANG']['CUR']['FKP'] = 'FKP - Livre des Îles Malouines';
$GLOBALS['ISO_LANG']['CUR']['GBP'] = 'GBP - Livre sterling';
$GLOBALS['ISO_LANG']['CUR']['GEL'] = 'GEL - Lari';
$GLOBALS['ISO_LANG']['CUR']['GHS'] = 'GHS - Cedi du Ghana';
$GLOBALS['ISO_LANG']['CUR']['GIP'] = 'GIP - Livre de Gibraltar';
$GLOBALS['ISO_LANG']['CUR']['GMD'] = 'GMD - Dalasi';
$GLOBALS['ISO_LANG']['CUR']['GNF'] = 'GNF - Franc Guinéen';
$GLOBALS['ISO_LANG']['CUR']['GTQ'] = 'GTQ - Quetzal guatémaltèque';
$GLOBALS['ISO_LANG']['CUR']['GYD'] = 'GYD - Dollar du Guyana';
$GLOBALS['ISO_LANG']['CUR']['HKD'] = 'HKD - Dollar de Hong Kong';
$GLOBALS['ISO_LANG']['CUR']['HNL'] = 'HNL - Lempira hondurien';
$GLOBALS['ISO_LANG']['CUR']['HRD'] = 'HRD - Dinar croate';
$GLOBALS['ISO_LANG']['CUR']['HRK'] = 'HRK - Kuna croate';
$GLOBALS['ISO_LANG']['CUR']['HTG'] = 'HTG - Gourde haïtienne';
$GLOBALS['ISO_LANG']['CUR']['HUF'] = 'HUF - Forint hongrois';
$GLOBALS['ISO_LANG']['CUR']['IDR'] = 'IDR - Roupie indonésienne';
$GLOBALS['ISO_LANG']['CUR']['ILS'] = 'ILS - Shekel';
$GLOBALS['ISO_LANG']['CUR']['INR'] = 'INR - Roupie indienne';
$GLOBALS['ISO_LANG']['CUR']['IQD'] = 'IQD - Dinar irakien';
$GLOBALS['ISO_LANG']['CUR']['IRR'] = 'IRR - Rial iranien';
$GLOBALS['ISO_LANG']['CUR']['ISK'] = 'ISK - Couronne islandaise';
$GLOBALS['ISO_LANG']['CUR']['JMD'] = 'JMD - Dollar jamaïcain';
$GLOBALS['ISO_LANG']['CUR']['JOD'] = 'JOD - Dinar jordanien';
$GLOBALS['ISO_LANG']['CUR']['JPY'] = 'JPY - Yen';
$GLOBALS['ISO_LANG']['CUR']['KES'] = 'KES - Shilling kényan';
$GLOBALS['ISO_LANG']['CUR']['KGS'] = 'KGS - Som du Kirghizistan';
$GLOBALS['ISO_LANG']['CUR']['KHR'] = 'KHR - Riel du Cambodge';
$GLOBALS['ISO_LANG']['CUR']['KMF'] = 'KMF - Franc comorien';
$GLOBALS['ISO_LANG']['CUR']['KPW'] = 'KPW - Won de Corée du Nord';
$GLOBALS['ISO_LANG']['CUR']['KRW'] = 'KRW - Won de Corée du Sud';
$GLOBALS['ISO_LANG']['CUR']['KWD'] = 'KWD - Dinar koweïtien';
$GLOBALS['ISO_LANG']['CUR']['KYD'] = 'KYD - Dollar des Îles Caïmans';
$GLOBALS['ISO_LANG']['CUR']['KZT'] = 'KZT - Tenge kazakh';
$GLOBALS['ISO_LANG']['CUR']['LAK'] = 'LAK - Kip';
$GLOBALS['ISO_LANG']['CUR']['LBP'] = 'LBP - Livre libanaise';
$GLOBALS['ISO_LANG']['CUR']['LKR'] = 'LKR - Roupie srilankaise';
$GLOBALS['ISO_LANG']['CUR']['LRD'] = 'LRD - Dollar libérien';
$GLOBALS['ISO_LANG']['CUR']['LSL'] = 'LSL - Loti';
$GLOBALS['ISO_LANG']['CUR']['LTL'] = 'LTL - Litas lituanien';
$GLOBALS['ISO_LANG']['CUR']['LVL'] = 'LVL - Lats letton';
$GLOBALS['ISO_LANG']['CUR']['LYD'] = 'LYD - Dinar libyen';
$GLOBALS['ISO_LANG']['CUR']['MAD'] = 'MAD - Dirham marocain';
$GLOBALS['ISO_LANG']['CUR']['MDL'] = 'MDL - Leu moldave';
$GLOBALS['ISO_LANG']['CUR']['MGA'] = 'MGA - Ariary malgache';
$GLOBALS['ISO_LANG']['CUR']['MKD'] = 'MKD - Dinar macédonien';
$GLOBALS['ISO_LANG']['CUR']['MMK'] = 'MMK - Kyat';
$GLOBALS['ISO_LANG']['CUR']['MNT'] = 'MNT - Tugrik';
$GLOBALS['ISO_LANG']['CUR']['MOP'] = 'MOP - Pataca';
$GLOBALS['ISO_LANG']['CUR']['MRO'] = 'MRO - Ouguiya';
$GLOBALS['ISO_LANG']['CUR']['MUR'] = 'MUR - Roupie mauricienne';
$GLOBALS['ISO_LANG']['CUR']['MVR'] = 'MVR - Rufiyaa maldivienne';
$GLOBALS['ISO_LANG']['CUR']['MWK'] = 'MWK - Kwacha malawien';
$GLOBALS['ISO_LANG']['CUR']['MXN'] = 'MXN - Peso mexicain';
$GLOBALS['ISO_LANG']['CUR']['MYR'] = 'MYR - Ringgit (Dollar malaisien)';
$GLOBALS['ISO_LANG']['CUR']['MZN'] = 'MZN - Metical';
$GLOBALS['ISO_LANG']['CUR']['NAD'] = 'NAD - Dollar namibien';
$GLOBALS['ISO_LANG']['CUR']['NGN'] = 'NGN - Naira';
$GLOBALS['ISO_LANG']['CUR']['NIO'] = 'NIO - Córdoba d’or';
$GLOBALS['ISO_LANG']['CUR']['NOK'] = 'NOK - Couronne norvégienne';
$GLOBALS['ISO_LANG']['CUR']['NPR'] = 'NPR - Roupie népalaise';
$GLOBALS['ISO_LANG']['CUR']['NZD'] = 'NZD - Dollar néo-zélandais';
$GLOBALS['ISO_LANG']['CUR']['OMR'] = 'OMR - Rial omanais';
$GLOBALS['ISO_LANG']['CUR']['PAB'] = 'PAB - Balboa panaméen';
$GLOBALS['ISO_LANG']['CUR']['PEN'] = 'PEN - Nouveau sol péruvien';
$GLOBALS['ISO_LANG']['CUR']['PGK'] = 'PGK - Kina';
$GLOBALS['ISO_LANG']['CUR']['PHP'] = 'PHP - Peso philippin';
$GLOBALS['ISO_LANG']['CUR']['PKR'] = 'PKR - Roupie pakistanaise';
$GLOBALS['ISO_LANG']['CUR']['PLN'] = 'PLN - Złoty';
$GLOBALS['ISO_LANG']['CUR']['PYG'] = 'PYG - Guaraní';
$GLOBALS['ISO_LANG']['CUR']['QAR'] = 'QAR - Riyal qatari';
$GLOBALS['ISO_LANG']['CUR']['RON'] = 'RON - Nouveau leu roumain';
$GLOBALS['ISO_LANG']['CUR']['RSD'] = 'RSD - Dinar serbe';
$GLOBALS['ISO_LANG']['CUR']['RUB'] = 'RUB - Rouble de la Fédération de Russie';
$GLOBALS['ISO_LANG']['CUR']['RWF'] = 'RWF - Franc rwandais';
$GLOBALS['ISO_LANG']['CUR']['SAR'] = 'SAR - Rial saoudien';
$GLOBALS['ISO_LANG']['CUR']['SBD'] = 'SBD - Dollar des îles Salomon';
$GLOBALS['ISO_LANG']['CUR']['SCR'] = 'SCR - Roupie seychelloise';
$GLOBALS['ISO_LANG']['CUR']['SDG'] = 'SDG - Livre soudanaise';
$GLOBALS['ISO_LANG']['CUR']['SEK'] = 'SEK - Couronne suédoise';
$GLOBALS['ISO_LANG']['CUR']['SGD'] = 'SGD - Dollar de Singapour';
$GLOBALS['ISO_LANG']['CUR']['SHP'] = 'SHP - Livre de Sainte-Hélène';
$GLOBALS['ISO_LANG']['CUR']['SLL'] = 'SLL - Leone';
$GLOBALS['ISO_LANG']['CUR']['SOS'] = 'SOS - Shilling somalien';
$GLOBALS['ISO_LANG']['CUR']['SRD'] = 'SRD - Dollar de Surinam';
$GLOBALS['ISO_LANG']['CUR']['SSP'] = 'SSP - Livre sud-soudanaise';
$GLOBALS['ISO_LANG']['CUR']['STD'] = 'STD - Dobra';
$GLOBALS['ISO_LANG']['CUR']['SVC'] = 'SVC - Colon salvadorien';
$GLOBALS['ISO_LANG']['CUR']['SYP'] = 'SYP - Livre de Syrie';
$GLOBALS['ISO_LANG']['CUR']['SZL'] = 'SZL - Lilangeni';
$GLOBALS['ISO_LANG']['CUR']['THB'] = 'THB - Baht';
$GLOBALS['ISO_LANG']['CUR']['TJS'] = 'TJS - Somoni du Tadjikistan';
$GLOBALS['ISO_LANG']['CUR']['TMT'] = 'TMT - Manat du Turkménistan';
$GLOBALS['ISO_LANG']['CUR']['TND'] = 'TND - Dinar tunisien';
$GLOBALS['ISO_LANG']['CUR']['TOP'] = 'TOP - Pa\'anga';
$GLOBALS['ISO_LANG']['CUR']['TRY'] = 'TRY - Nouvelle livre turque';
$GLOBALS['ISO_LANG']['CUR']['TTD'] = 'TTD - Dollar de Trinité-et-Tobago';
$GLOBALS['ISO_LANG']['CUR']['TWD'] = 'TWD - Nouveau dollar de Taïwan';
$GLOBALS['ISO_LANG']['CUR']['TZS'] = 'TZS - Shilling tanzanien';
$GLOBALS['ISO_LANG']['CUR']['UAH'] = 'UAH - Hryvna';
$GLOBALS['ISO_LANG']['CUR']['UGX'] = 'UGX - Shilling ougandais';
$GLOBALS['ISO_LANG']['CUR']['USD'] = 'USD - Dollar américain';
$GLOBALS['ISO_LANG']['CUR']['UYU'] = 'UYU - Peso uruguayen';
$GLOBALS['ISO_LANG']['CUR']['UZS'] = 'UZS - Sum';
$GLOBALS['ISO_LANG']['CUR']['VEF'] = 'VEF - Bolivar fuerte';
$GLOBALS['ISO_LANG']['CUR']['VND'] = 'VND - Dong';
$GLOBALS['ISO_LANG']['CUR']['VUV'] = 'VUV - Vatu';
$GLOBALS['ISO_LANG']['CUR']['WST'] = 'WST - Tala';
$GLOBALS['ISO_LANG']['CUR']['XCD'] = 'XCD - Dollar des Caraïbes orientales';
$GLOBALS['ISO_LANG']['CUR']['YER'] = 'YER - Riyal du Yémen';
$GLOBALS['ISO_LANG']['CUR']['ZAR'] = 'ZAR - Rand sud africain';
$GLOBALS['ISO_LANG']['CUR']['ZMK'] = 'ZMK - Kwacha zambien';
$GLOBALS['ISO_LANG']['CUR']['ZWL'] = 'ZWL - Dollar du Zimbabwe';


/**
 * Currency symbols
 */
$GLOBALS['ISO_LANG']['CUR_SYMBOL']['USD'] = '$';
$GLOBALS['ISO_LANG']['CUR_SYMBOL']['EUR'] = '€';
$GLOBALS['ISO_LANG']['CUR_SYMBOL']['GBP'] = '£';
$GLOBALS['ISO_LANG']['CUR_SYMBOL']['JPY'] = '¥';

