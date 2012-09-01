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

$GLOBALS['TL_LANG']['tl_iso_payment_modules']['type'][0] = 'Type de passerelle de paiement';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['type'][1] = 'Sélectionner une passerelle de paiement particulière (par exemple Authorize.net)';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['name'][0] = 'Nom du mode de paiement';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['name'][1] = 'Saisir un nom pour ce mode de paiement. Il ne sera utilisé que dans le back office.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['label'][0] = 'Libellé du mode de paiement';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['label'][1] = 'Le libellé sera montré aux clients à la caisse.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['note'][0] = 'Note relative au paiement';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['note'][1] = 'Cette note peut être envoyée dans les mails de confirmation (##payment_note##).';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['price'][0] = 'Prix';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['tax_class'][0] = 'Taxe';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['new_order_status'][0] = 'Statut pour les nouvelles commandes';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['new_order_status'][1] = 'Choisir un statut correspondant aux nouvelles commandes.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['postsale_mail'][0] = 'Modèle de courriel pour les changements de statut';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['postsale_mail'][1] = 'Sélectionnez un modèle de courriel pour avertir l\'administrateur de la boutique sur les changements d\'état de paiement.';
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
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['trans_type'][0] = 'Type de transaction';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['trans_type'][1] = 'Sélectionner un encaissement immédiat ou autoriser (et maintenir) en différé (par exemple lors de l\'expédition).';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['paypal_account'][0] = 'Compte PayPal';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['paypal_account'][1] = 'Entrez votre nom d\'utilisateur paypal (adresse e-mail).';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_user'][0] = 'Paypal Payflow Pro username Paypal Payflow Pro';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_vendor'][0] = 'Vendeur Paypal Payflow Pro';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_vendor'][1] = 'Une chaîne alphanumérique d\'environ 10 caractères.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_partner'][0] = 'Partenaire Paypal Payflow Pro';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_password'][0] = 'Mot de passe api Paypal Payflow Pro';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_password'][1] = 'Une chaîne alphanumérique d\'environ 11 caractères.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_transType'][0] = 'Type de transaction Paypal Payflow Pro';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_transType'][1] = 'Sélectionner un type de transaction';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['epay_merchantnumber'][0] = 'Numéro de marchand';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['epay_merchantnumber'][1] = 'Le numéro de commerçant unique créé dans ePay. Ce numéro de commerçant se trouve dans votre contrat avec PBS.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['epay_secretkey'][0] = 'Clé secrète';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['epay_secretkey'][1] = 'La clé secrète mise en place dans votre configuration ePay.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['button'][0] = 'Bouton de commande';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['button'][1] = 'Afficher un bouton personnalisé au lieu de celui par défaut.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['requireCCV'][0] = 'Code de vérification de la carte (cryptogramme)';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['requireCCV'][1] = 'Choisir cette option pour augmenter la sécurité des transactions en exigeant que le numéro de vérification de carte soit saisi.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['cybersource_merchant_id'][0] = 'Identifiant commerçant Cybersource';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['cybersource_merchant_id'][1] = 'Saisir votre identifiant commerçant Cybersource ici';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['cybersource_trans_key'][0] = 'Clé de transaction Cybersource';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['cybersource_trans_key'][1] = 'Fournie lorsque vous avez terminé l\'inscription pour votre passerelle';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['cybersource_trans_type'][0] = 'Type de transaction Cybersource';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['allowed_cc_types'][0] = 'Cartes de crédit acceptés';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['allowed_cc_types'][1] = 'Sélectionner quelles cartes de crédits sont acceptées par le module.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['authorize_login'][0] = 'Identifiant Authorize.net';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['authorize_login'][1] = 'Fourni quand vous avez complété votre inscription pour votre passerelle.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['authorize_trans_key'][0] = 'Clé Authorize.net';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['authorize_trans_key'][1] = 'Fournie quand vous avez complété votre inscription pour votre passerelle.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['authorize_trans_type'][0] = 'Type de transaction Authorize.net';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['groups'][0] = 'Groupes de membres';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['groups'][1] = 'Restreindre ce mode de paiement pour certains groupes de membres.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['protected'][0] = 'Protéger le module';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['protected'][1] = 'Ne montrer le mode de paiement qu\'à certains groupes de membres';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['guests'][0] = 'Montrer aux invités seulement';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['guests'][1] = 'Cacher le mode de paiement si un membre est connecté';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['debug'][0] = 'Mode debug';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['debug'][1] = 'Pour les tests sans paiement réel.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['enabled'][0] = 'Activé';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['enabled'][1] = 'Cochez cette case si le module de paiement doit être activé dans le magasin.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['capture'] = 'Autorise et Capture';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['auth'] = 'Autorise seulement';
$GLOBALS['TL_LANG']['ISO_PAY']['authorizedotnet']['modes']['AUTH_CAPTURE'] = 'Autorise et Capture';
$GLOBALS['TL_LANG']['ISO_PAY']['authorizedotnet']['modes']['AUTH_ONLY'] = 'Autorise seulement';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['no_shipping'] = 'Commandes sans livraison';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['AUTH_CAPTURE'][0] = 'Autorise et Capture';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['AUTH_CAPTURE'][1] = 'Les transactions de ce type seront envoyées pour autorisation. La transaction sera automatiquement prise pour le réglement si elle est approuvée. C\'est le type de transaction par défaut de la passerelle. Si aucun type n\'est indiqué lors de la présentation des transactions à la passerelle, la passerelle présume que la transaction est de ce type';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['AUTH_ONLY'][0] = 'Autorise seulement';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['AUTH_ONLY'][1] = 'Les transactions de ce type sont soumises si le commerçant souhaite valider la carte de crédit pour le montant de la marchandise vendue. Si le commerçant n\'a pas les marchandises en stock ou souhaite examiner les commandes avant expédition des marchandises, ce type de transaction doit être soumise. La passerelle va envoyer la transaction à l\'institution financière pour approbation. Toutefois, cette transaction ne sera pas envoyé pour le règlement. Si le commerçant ne statue pas sur la transaction dans les 30 jours, la transaction ne sera plus disponible pour la capture.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['CAPTURE_ONLY'][0] = 'Capture seulement';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['CAPTURE_ONLY'][1] = 'Il s\'agit d\'une demande de régler une transaction qui n\'a pas été soumise à l\'autorisation par la passerelle de paiement. La passerelle va accepter cette transaction si un code d\'autorisation est soumis. x_auth_code est un champ obligatoire pour les transactions de type CAPTURE_ONLY.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['CREDIT'][0] = 'Crédit';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['CREDIT'][1] = 'Cette opération est également appelée «remboursement» et indique à la passerelle que l\'argent doit circuler à partir du commerçant vers la clientèle. La passerelle acceptera un crédit ou une demande de remboursement si la transaction soumise remplit les conditions suivantes: <ul><li>La transaction est soumise avec l\'ID de la transaction initiale contre laquelle le crédit a été émis.</li><li>La passerelle a un dossier de la transaction originale.</li><li>L\'opération initiale a été réglée.</li><li>La somme du montant soumis dans la transaction de crédit et tous les crédits présentées contre l\'opération initiale est inférieure au montant de la transaction originale.</li><li>La totalité ou les quatre derniers chiffres du numéro de carte de crédit soumis à la transaction de crédit correspondent à la totalité ou aux quatres derniers chiffres du numéro de carte de crédit utilisée lors de la transaction originale.</li><li>La transaction est soumise à moins de 120 jours à compter de la date de règlement et l\'heure de la transaction originale.</li></ul> Une clé de transaction est tenue de présenter un crédit au système.';
$GLOBALS['TL_LANG']['tl_payment_module']['payflowpro_transTypes']['Sale'] = 'Autorisation et Capture';
$GLOBALS['TL_LANG']['tl_payment_module']['payflowpro_transTypes']['Authorization'] = 'Autorise seulement';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['type_legend'] = 'Nom & Type';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['note_legend'] = 'Notes complémentaires';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['config_legend'] = 'Configuration générale';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['gateway_legend'] = 'Configuration de la passerelle de paiement';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['price_legend'] = 'Prix';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['template_legend'] = 'Modèle';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expert_legend'] = 'Réglages expert';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['enabled_legend'] = 'Paramètres activés';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['new'][0] = 'Nouveau mode de paiement';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['new'][1] = 'Créer un nouveau mode de paiement';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['edit'][0] = 'Modifier le mode de paiement';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['edit'][1] = 'Modifier le mode de paiement ID %s';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['copy'][0] = 'Copier le mode de paiement';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['copy'][1] = 'Copier le mode de paiement ID %s';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['delete'][0] = 'Supprimer le mode de paiement';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['delete'][1] = 'Supprimer le mode de paiement ID %s';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['show'][0] = 'Détails de mode de paiement';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['show'][1] = 'Montrer les détails du mode de paiement ID %s';

