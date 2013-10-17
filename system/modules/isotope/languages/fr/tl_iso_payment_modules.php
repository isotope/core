<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 * 
 * Copyright (C) 2009-2013 Isotope eCommerce Workgroup
 * 
 * Core translations are managed using Transifex. To create a new translation
 * or to help to maintain an existing one, please register at transifex.com.
 * 
 * @link http://help.transifex.com/intro/translating.html
 * @link https://www.transifex.com/projects/i/isotope/language/fr/
 * 
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

$GLOBALS['TL_LANG']['tl_iso_payment_modules']['name'][0] = 'Nom du mode de paiement';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['name'][1] = 'Saisir un nom pour ce mode de paiement. Il ne sera utilisé que dans le back office.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['label'][0] = 'Libellé du mode de paiement';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['label'][1] = 'Le libellé sera montré aux clients à la caisse.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['type'][0] = 'Type de passerelle de paiement';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['type'][1] = 'Sélectionner une passerelle de paiement particulière (par exemple Authorize.net)';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['note'][0] = 'Note relative au paiement';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['note'][1] = 'Cette note peut être envoyée dans les mails de confirmation (##payment_note##).';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['new_order_status'][0] = 'État pour les nouvelles commandes';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['new_order_status'][1] = 'Choisir un état correspondant aux nouvelles commandes.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['minimum_total'][0] = 'Total minimum';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['minimum_total'][1] = 'Saisir un nombre supérieur à zéro pour exclure ce mode de paiement pour les commandes de prix inférieur.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['maximum_total'][0] = 'Total maximum';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['maximum_total'][1] = 'Saisir un nombre supérieur à zéro pour exclure ce mode de paiement pour les commandes de prix supérieur.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['countries'][0] = 'Pays autorisés';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['countries'][1] = 'Sélectionner les pays pour lesquels ce mode de paiement est accepté (adresse de facturation du client).';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['shipping_modules'][0] = 'Modes de livraison';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['shipping_modules'][1] = 'Vous pouvez limiter ce mode de paiement à certains modes de livraison (par exemple, en espèces uniquement lors du ramassage).';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['product_types'][0] = 'Types de produit';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['product_types'][1] = 'Limiter ce mode de paiement pour certains types de produits. Si le panier contient un type de produit que vous n\'avez pas sélectionné, le module de paiement n\'est pas disponible.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['price'][0] = 'Prix';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['tax_class'][0] = 'Taxe';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['trans_type'][0] = 'Type de transaction';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['trans_type'][1] = 'Sélectionner un encaissement immédiat ou autoriser (et maintenir) en différé (par exemple lors de l\'expédition).';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['paypal_account'][0] = 'Compte PayPal';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['paypal_account'][1] = 'Entrez votre nom d\'utilisateur PayPal (adresse e-mail).';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['postfinance_pspid'][0] = 'Postfinance PSPID';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['postfinance_pspid'][1] = 'The PSPID is your unique identification for the Postfinance system.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['postfinance_secret'][0] = 'Postfinance SHA-1-IN signature';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['postfinance_secret'][1] = 'This will be used to validate the server communication.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['postfinance_method'][0] = 'Postfinance method';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['postfinance_method'][1] = 'Type of data transfer from postfinance.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['requireCCV'][0] = 'Code de vérification de la carte (cryptogramme)';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['requireCCV'][1] = 'Choisir cette option pour augmenter la sécurité des transactions en exigeant que le numéro de vérification de carte soit saisi.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['allowed_cc_types'][0] = 'Cartes de crédit acceptés';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['allowed_cc_types'][1] = 'Sélectionner quelles cartes de crédits sont acceptées par le module.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['groups'][0] = 'Groupes de membres';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['groups'][1] = 'Restreindre ce mode de paiement pour certains groupes de membres.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['protected'][0] = 'Protéger le module';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['protected'][1] = 'Ne montrer le mode de paiement qu\'à certains groupes de membres';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['guests'][0] = 'Visible par les invités seulement';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['guests'][1] = 'Cacher le mode de paiement si un membre est connecté';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['debug'][0] = 'Mode debug';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['debug'][1] = 'Pour les tests sans paiement réel.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['enabled'][0] = 'Activé';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['enabled'][1] = 'Cochez cette case si le module de paiement doit être activé dans la boutique.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['new'][0] = 'Nouveau mode de paiement';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['new'][1] = 'Créer un nouveau mode de paiement';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['edit'][0] = 'Éditer un mode de paiement';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['edit'][1] = 'Éditer le mode de paiement ID %s';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['copy'][0] = 'Dupliquer un mode de paiement';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['copy'][1] = 'Dupliquer le mode de paiement ID %s';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['delete'][0] = 'Supprimer un mode de paiement';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['delete'][1] = 'Supprimer le mode de paiement ID %s';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['show'][0] = 'Détails du mode de paiement';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['show'][1] = 'Afficher les détails du mode de paiement ID %s';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['no_shipping'] = 'Commandes sans livraison';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['type_legend'] = 'Nom &amp; Type';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['note_legend'] = 'Notes complémentaires';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['config_legend'] = 'Configuration générale';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['gateway_legend'] = 'Configuration de la passerelle de paiement';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['price_legend'] = 'Prix';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['template_legend'] = 'Modèle';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expert_legend'] = 'Paramètres avancés';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['enabled_legend'] = 'Paramètres activés';
