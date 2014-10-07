<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 * 
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 * 
 * Translations are managed using Transifex. To create a new translation
 * or to help to maintain an existing one, please register at transifex.com.
 * 
 * @link http://help.transifex.com/intro/translating.html
 * @link https://www.transifex.com/projects/i/isotope/language/fr/
 * 
 * @license http://www.gnu.org/licenses/lgpl-3.0.html
 */

$GLOBALS['TL_LANG']['tl_iso_payment']['name'][0] = 'Nom du mode de paiement';
$GLOBALS['TL_LANG']['tl_iso_payment']['name'][1] = 'Saisir un nom pour ce mode de paiement. Il ne sera utilisé que dans le back office.';
$GLOBALS['TL_LANG']['tl_iso_payment']['label'][0] = 'Libellé du mode de paiement';
$GLOBALS['TL_LANG']['tl_iso_payment']['label'][1] = 'Le libellé sera montré aux clients à la caisse.';
$GLOBALS['TL_LANG']['tl_iso_payment']['type'][0] = 'Type de passerelle de paiement';
$GLOBALS['TL_LANG']['tl_iso_payment']['type'][1] = 'Sélectionner une passerelle de paiement particulière (par exemple Authorize.net)';
$GLOBALS['TL_LANG']['tl_iso_payment']['note'][0] = 'Note relative au paiement';
$GLOBALS['TL_LANG']['tl_iso_payment']['note'][1] = 'Cette note peut être envoyée dans les mails de confirmation (##payment_note##).';
$GLOBALS['TL_LANG']['tl_iso_payment']['new_order_status'][0] = 'État pour les nouvelles commandes';
$GLOBALS['TL_LANG']['tl_iso_payment']['new_order_status'][1] = 'Choisir un état correspondant aux nouvelles commandes.';
$GLOBALS['TL_LANG']['tl_iso_payment']['minimum_total'][0] = 'Total minimum';
$GLOBALS['TL_LANG']['tl_iso_payment']['minimum_total'][1] = 'Saisir un nombre supérieur à zéro pour exclure ce mode de paiement pour les commandes de prix inférieur.';
$GLOBALS['TL_LANG']['tl_iso_payment']['maximum_total'][0] = 'Total maximum';
$GLOBALS['TL_LANG']['tl_iso_payment']['maximum_total'][1] = 'Saisir un nombre supérieur à zéro pour exclure ce mode de paiement pour les commandes de prix supérieur.';
$GLOBALS['TL_LANG']['tl_iso_payment']['countries'][0] = 'Pays autorisés';
$GLOBALS['TL_LANG']['tl_iso_payment']['countries'][1] = 'Sélectionner les pays pour lesquels ce mode de paiement est accepté (adresse de facturation du client).';
$GLOBALS['TL_LANG']['tl_iso_payment']['shipping_modules'][0] = 'Modes de livraison';
$GLOBALS['TL_LANG']['tl_iso_payment']['shipping_modules'][1] = 'Vous pouvez limiter ce mode de paiement à certains modes de livraison (par exemple, en espèces uniquement lors du ramassage).';
$GLOBALS['TL_LANG']['tl_iso_payment']['product_types'][0] = 'Types de produits';
$GLOBALS['TL_LANG']['tl_iso_payment']['product_types'][1] = 'Limiter ce mode de paiement pour certains types de produits. Si le panier contient un type de produit que vous n\'avez pas sélectionné, le module de paiement n\'est pas disponible.';
$GLOBALS['TL_LANG']['tl_iso_payment']['config_ids'][0] = 'Configurations de boutique';
$GLOBALS['TL_LANG']['tl_iso_payment']['price'][0] = 'Prix';
$GLOBALS['TL_LANG']['tl_iso_payment']['price'][1] = 'Entrez un prix ou une valeur en pourcentage (ex: "10" ou "10%").';
$GLOBALS['TL_LANG']['tl_iso_payment']['tax_class'][0] = 'Taxe';
$GLOBALS['TL_LANG']['tl_iso_payment']['tax_class'][1] = 'Sélectionner une taxe pour le prix.';
$GLOBALS['TL_LANG']['tl_iso_payment']['trans_type'][0] = 'Type de transaction';
$GLOBALS['TL_LANG']['tl_iso_payment']['trans_type'][1] = 'Sélectionner un encaissement immédiat ou autoriser (et maintenir) en différé (par exemple lors de l\'expédition).';
$GLOBALS['TL_LANG']['tl_iso_payment']['paypal_account'][0] = 'Compte PayPal';
$GLOBALS['TL_LANG']['tl_iso_payment']['paypal_account'][1] = 'Entrez votre nom d\'utilisateur PayPal (adresse e-mail).';
$GLOBALS['TL_LANG']['tl_iso_payment']['payflowpro_user'][0] = 'Nom d\'utilisateur Paypal Payflow Pro';
$GLOBALS['TL_LANG']['tl_iso_payment']['payflowpro_vendor'][0] = 'Fournisseur Paypal Payflow Pro';
$GLOBALS['TL_LANG']['tl_iso_payment']['payflowpro_vendor'][1] = 'Une chaîne alphanumérique de 10 caractères.';
$GLOBALS['TL_LANG']['tl_iso_payment']['payflowpro_partner'][0] = 'Partenaire Paypal Payflow Pro';
$GLOBALS['TL_LANG']['tl_iso_payment']['payflowpro_partner'][1] = 'Sensible à la casse ! Les identifiants habituels des partenaires sont soit "PayPal" ou "PayPalUK".';
$GLOBALS['TL_LANG']['tl_iso_payment']['payflowpro_password'][0] = 'Mot de passe de l\'API Paypal Payflow Pro';
$GLOBALS['TL_LANG']['tl_iso_payment']['payflowpro_password'][1] = 'Une chaîne alphanumérique de 11 caractères.';
$GLOBALS['TL_LANG']['tl_iso_payment']['payflowpro_transType'][0] = 'Type de transaction Paypal Payflow Pro';
$GLOBALS['TL_LANG']['tl_iso_payment']['payflowpro_transType'][1] = 'Sélectionner un type de transaction.';
$GLOBALS['TL_LANG']['tl_iso_payment']['psp_pspid'][0] = 'PSPID';
$GLOBALS['TL_LANG']['tl_iso_payment']['psp_pspid'][1] = 'Le PSPID est votre unique identifiant pour la méthode de paiement.';
$GLOBALS['TL_LANG']['tl_iso_payment']['psp_http_method'][0] = 'Mode HTTP';
$GLOBALS['TL_LANG']['tl_iso_payment']['psp_http_method'][1] = 'Type de transfert de donnée HTTP depuis et vers les serveurs.';
$GLOBALS['TL_LANG']['tl_iso_payment']['psp_hash_method'][0] = 'Méthode de hachage';
$GLOBALS['TL_LANG']['tl_iso_payment']['psp_hash_method'][1] = 'Algorithme de hachage pour le transfert de donnée depuis et vers les serveurs.';
$GLOBALS['TL_LANG']['tl_iso_payment']['psp_hash_method']['sha1'] = 'SHA-1';
$GLOBALS['TL_LANG']['tl_iso_payment']['psp_hash_method']['sha256'] = 'SHA-256';
$GLOBALS['TL_LANG']['tl_iso_payment']['psp_hash_method']['sha512'] = 'SHA-512';
$GLOBALS['TL_LANG']['tl_iso_payment']['psp_hash_in'][0] = 'Signature SHA-IN';
$GLOBALS['TL_LANG']['tl_iso_payment']['psp_hash_in'][1] = 'Ceci sera utilisé pour valider la communication de serveur à serveur.';
$GLOBALS['TL_LANG']['tl_iso_payment']['psp_hash_out'][0] = 'Signature SHA-OUT';
$GLOBALS['TL_LANG']['tl_iso_payment']['psp_hash_out'][1] = 'Ceci sera utilisé pour valider la communication de serveur à serveur.';
$GLOBALS['TL_LANG']['tl_iso_payment']['psp_dynamic_template'][0] = 'URL dynamique';
$GLOBALS['TL_LANG']['tl_iso_payment']['psp_dynamic_template'][1] = 'Entrez ici une URL <strong>absolue</strong> valide à une modèle dynamique.';
$GLOBALS['TL_LANG']['tl_iso_payment']['requireCCV'][0] = 'Code de vérification de la carte (cryptogramme)';
$GLOBALS['TL_LANG']['tl_iso_payment']['requireCCV'][1] = 'Choisir cette option pour augmenter la sécurité des transactions en exigeant que le numéro de vérification de carte soit saisi.';
$GLOBALS['TL_LANG']['tl_iso_payment']['allowed_cc_types'][0] = 'Cartes de crédit acceptés';
$GLOBALS['TL_LANG']['tl_iso_payment']['allowed_cc_types'][1] = 'Sélectionner quelles cartes de crédits sont acceptées par le module.';
$GLOBALS['TL_LANG']['tl_iso_payment']['datatrans_id'][0] = 'ID du commerçant';
$GLOBALS['TL_LANG']['tl_iso_payment']['datatrans_id'][1] = 'Saisir votre identifiant de commerçant (merchant ID).';
$GLOBALS['TL_LANG']['tl_iso_payment']['datatrans_sign'][0] = 'Clé HMAC';
$GLOBALS['TL_LANG']['tl_iso_payment']['datatrans_sign'][1] = 'Saisir votre clé HMAC à partir de l\'interface d\'administration de Datatrans.';
$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_paymentmethod'][0] = 'Mode de paiement';
$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_paymentmethod'][1] = 'Sélectionner un mode de paiement.';
$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_paymentmethod']['creditcard'] = 'Carte de crédit';
$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_paymentmethod']['maestro'] = 'Carte de débit';
$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_paymentmethod']['directdebit'] = 'Débit direct';
$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_sslmerchant'][0] = 'ID du vendeur';
$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_sslmerchant'][1] = 'Saisir l\'identifiant du vendeur (seller ID).';
$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_sslpassword'][0] = 'Mot de passe';
$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_sslpassword'][1] = 'Saisir votre mot de passe SSL.';
$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_merchantref'][0] = 'Référence';
$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_merchantref'][1] = 'Une référence qui sera montrée sur la page détaillée du vendeur au lieu de l\'ID du panier';
$GLOBALS['TL_LANG']['tl_iso_payment']['sofortueberweisung_user_id'][0] = 'ID du client';
$GLOBALS['TL_LANG']['tl_iso_payment']['sofortueberweisung_user_id'][1] = 'Votre ID client pour sofortüberweisung.de';
$GLOBALS['TL_LANG']['tl_iso_payment']['sofortueberweisung_project_id'][0] = 'ID du projet';
$GLOBALS['TL_LANG']['tl_iso_payment']['sofortueberweisung_project_id'][1] = 'Votre ID projet pour sofortüberweisung.de';
$GLOBALS['TL_LANG']['tl_iso_payment']['sofortueberweisung_project_password'][0] = 'Mot de passe du projet';
$GLOBALS['TL_LANG']['tl_iso_payment']['sofortueberweisung_project_password'][1] = 'Votre mot de passe projet pour sofortüberweisung.de';
$GLOBALS['TL_LANG']['tl_iso_payment']['saferpay_accountid'][0] = 'ID acompte Saferpay';
$GLOBALS['TL_LANG']['tl_iso_payment']['saferpay_accountid'][1] = 'Veuillez saisir votre identifiant unique Saferpay.';
$GLOBALS['TL_LANG']['tl_iso_payment']['saferpay_description'][0] = 'Description récapitulatif';
$GLOBALS['TL_LANG']['tl_iso_payment']['saferpay_description'][1] = 'Le client verra cette description sur la page récapitulative de Saferpay.';
$GLOBALS['TL_LANG']['tl_iso_payment']['saferpay_vtconfig'][0] = 'Configuration page de paiement (VTCONFIG)';
$GLOBALS['TL_LANG']['tl_iso_payment']['worldpay_description'][0] = 'Description';
$GLOBALS['TL_LANG']['tl_iso_payment']['groups'][0] = 'Groupes de membres';
$GLOBALS['TL_LANG']['tl_iso_payment']['groups'][1] = 'Restreindre ce mode de paiement pour certains groupes de membres.';
$GLOBALS['TL_LANG']['tl_iso_payment']['protected'][0] = 'Protéger le module';
$GLOBALS['TL_LANG']['tl_iso_payment']['protected'][1] = 'Ne montrer le mode de paiement qu\'à certains groupes de membres';
$GLOBALS['TL_LANG']['tl_iso_payment']['guests'][0] = 'Visible par les invités seulement';
$GLOBALS['TL_LANG']['tl_iso_payment']['guests'][1] = 'Cacher le mode de paiement si un membre est connecté';
$GLOBALS['TL_LANG']['tl_iso_payment']['debug'][0] = 'Mode debug';
$GLOBALS['TL_LANG']['tl_iso_payment']['debug'][1] = 'Pour les tests sans paiement réel.';
$GLOBALS['TL_LANG']['tl_iso_payment']['enabled'][0] = 'Activé';
$GLOBALS['TL_LANG']['tl_iso_payment']['enabled'][1] = 'Cochez cette case si le module de paiement doit être activé dans la boutique.';
$GLOBALS['TL_LANG']['tl_iso_payment']['new'][0] = 'Nouveau mode de paiement';
$GLOBALS['TL_LANG']['tl_iso_payment']['new'][1] = 'Créer un nouveau mode de paiement';
$GLOBALS['TL_LANG']['tl_iso_payment']['edit'][0] = 'Éditer un mode de paiement';
$GLOBALS['TL_LANG']['tl_iso_payment']['edit'][1] = 'Éditer le mode de paiement ID %s';
$GLOBALS['TL_LANG']['tl_iso_payment']['copy'][0] = 'Dupliquer un mode de paiement';
$GLOBALS['TL_LANG']['tl_iso_payment']['copy'][1] = 'Dupliquer le mode de paiement ID %s';
$GLOBALS['TL_LANG']['tl_iso_payment']['delete'][0] = 'Supprimer un mode de paiement';
$GLOBALS['TL_LANG']['tl_iso_payment']['delete'][1] = 'Supprimer le mode de paiement ID %s';
$GLOBALS['TL_LANG']['tl_iso_payment']['toggle'][0] = 'Activer/désactiver un mode de paiement.';
$GLOBALS['TL_LANG']['tl_iso_payment']['toggle'][1] = 'Activer/désactiver le mode de paiement ID %s';
$GLOBALS['TL_LANG']['tl_iso_payment']['show'][0] = 'Détails du mode de paiement';
$GLOBALS['TL_LANG']['tl_iso_payment']['show'][1] = 'Afficher les détails du mode de paiement ID %s';
$GLOBALS['TL_LANG']['tl_iso_payment']['no_shipping'] = 'Commandes sans livraison';
$GLOBALS['TL_LANG']['tl_iso_payment']['payone']['elv'] = 'Rétraction du débit';
$GLOBALS['TL_LANG']['tl_iso_payment']['payone']['cc'] = 'Carte de crédit';
$GLOBALS['TL_LANG']['tl_iso_payment']['payone']['dc'] = 'Carte de débit';
$GLOBALS['TL_LANG']['tl_iso_payment']['payone']['vor'] = 'Prépaiement';
$GLOBALS['TL_LANG']['tl_iso_payment']['payone']['rec'] = 'Facture';
$GLOBALS['TL_LANG']['tl_iso_payment']['payone']['wlt'] = 'e-Portefeuille';
$GLOBALS['TL_LANG']['tl_iso_payment']['type_legend'] = 'Nom &amp; Type';
$GLOBALS['TL_LANG']['tl_iso_payment']['note_legend'] = 'Notes complémentaires';
$GLOBALS['TL_LANG']['tl_iso_payment']['config_legend'] = 'Configuration générale';
$GLOBALS['TL_LANG']['tl_iso_payment']['gateway_legend'] = 'Configuration de la passerelle de paiement';
$GLOBALS['TL_LANG']['tl_iso_payment']['price_legend'] = 'Prix';
$GLOBALS['TL_LANG']['tl_iso_payment']['template_legend'] = 'Modèle';
$GLOBALS['TL_LANG']['tl_iso_payment']['expert_legend'] = 'Paramètres avancés';
$GLOBALS['TL_LANG']['tl_iso_payment']['enabled_legend'] = 'Approbation';
