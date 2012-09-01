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
$GLOBALS['TL_LANG']['tl_iso_products']['id'][0]                 = 'ID du produit';
$GLOBALS['TL_LANG']['tl_iso_products']['pages'][0]              = 'Catégories';
$GLOBALS['TL_LANG']['tl_iso_products']['pages'][1]              = 'Sélectionner une catégorie (les catégories sont basées sur les pages afin de profiter des fonctionnalités de Contao telles que la création automatique de la navigation, la protection, les modèles, et l\'intégration complète avec les éléments de contenu).';
$GLOBALS['TL_LANG']['tl_iso_products']['type'][0]               = 'Type de produit';
$GLOBALS['TL_LANG']['tl_iso_products']['type'][1]               = 'Les types de produit sont définis dans le gestionnaire de types de produits';
$GLOBALS['TL_LANG']['tl_iso_products']['alias'][0]              = 'Alias';
$GLOBALS['TL_LANG']['tl_iso_products']['name'][0]               = 'Nom';
$GLOBALS['TL_LANG']['tl_iso_products']['sku'][0]                = 'Référence (SKU)';
$GLOBALS['TL_LANG']['tl_iso_products']['shipping_weight'][0]    = 'Poids';
$GLOBALS['TL_LANG']['tl_iso_products']['shipping_weight'][1]    = 'Saisir le poids de ce produit. Il peut être utilisé pour calculer le coût d\'expédition.';
$GLOBALS['TL_LANG']['tl_iso_products']['teaser'][0]             = 'Résumé';
$GLOBALS['TL_LANG']['tl_iso_products']['description'][0]        = 'Description';
$GLOBALS['TL_LANG']['tl_iso_products']['description_meta'][0]   = 'Méta description';
$GLOBALS['TL_LANG']['tl_iso_products']['keywords_meta'][0]      = 'Méta mots-clés';
$GLOBALS['TL_LANG']['tl_iso_products']['price'][0]              = 'Prix';
$GLOBALS['TL_LANG']['tl_iso_products']['shipping_exempt'][0]    = 'Aucun frais de livraison';
$GLOBALS['TL_LANG']['tl_iso_products']['shipping_exempt'][1]    = 'Vérifier si le produit n\'est pas un produit livré (comme les produits téléchargeables).';
$GLOBALS['TL_LANG']['tl_iso_products']['tax_class'][0]          = 'Taxe';
$GLOBALS['TL_LANG']['tl_iso_products']['baseprice']             = array('Base price', 'Please enter your base price information.');
$GLOBALS['TL_LANG']['tl_iso_products']['images'][0]             = 'Images';
$GLOBALS['TL_LANG']['tl_iso_products']['protected']             = array('Protect product', 'Restrict product access to certain member groups.');
$GLOBALS['TL_LANG']['tl_iso_products']['groups']                = array('Allowed member groups', 'These groups will be able to access the product.');
$GLOBALS['TL_LANG']['tl_iso_products']['guests']                = array('Show to guests only', 'Hide the product if there is an authenticated user.');
$GLOBALS['TL_LANG']['tl_iso_products']['cssID']                 = array('CSS ID/class', 'Here you can set an ID and one or more classes.');
$GLOBALS['TL_LANG']['tl_iso_products']['published'][0]          = 'Publier le produit';
$GLOBALS['TL_LANG']['tl_iso_products']['published'][1]          = 'Rendre le produit visible aux visiteurs de votre site.';
$GLOBALS['TL_LANG']['tl_iso_products']['start'][0]              = 'Afficher à partir du';
$GLOBALS['TL_LANG']['tl_iso_products']['start'][1]              = 'Ne pas afficher le produit sur le site avant ce jour.';
$GLOBALS['TL_LANG']['tl_iso_products']['stop'][0]               = 'Afficher jusqu\'au';
$GLOBALS['TL_LANG']['tl_iso_products']['stop'][1]               = 'Ne plus afficher le produit sur le site après ce jour.';
$GLOBALS['TL_LANG']['tl_iso_products']['variant_attributes'][0] = 'Configuration des variantes';
$GLOBALS['TL_LANG']['tl_iso_products']['variant_attributes'][1] = 'Choisir la combinaison de valeurs pour cette variante.';
$GLOBALS['TL_LANG']['tl_iso_products']['inherit'][0]            = 'Attributs hérités';
$GLOBALS['TL_LANG']['tl_iso_products']['inherit'][1]            = 'Vérifiez les champs que vous souhaitez reproduire du produit de base.';


/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_iso_products']['source'][0]           = 'Répertoire source';
$GLOBALS['TL_LANG']['tl_iso_products']['source'][1]           = 'Choisir le dossier où les produits actifs se trouvent';
$GLOBALS['TL_LANG']['tl_iso_products']['internal'][0]         = 'Fichier interne';
$GLOBALS['TL_LANG']['tl_iso_products']['internal'][1]         = 'Sélectionner un fichier multimédia existant sur le serveur web (flash ou fichier mp3).';
$GLOBALS['TL_LANG']['tl_iso_products']['external'][0]         = 'Fichier externe';
$GLOBALS['TL_LANG']['tl_iso_products']['external'][1]         = 'Spécifier une vidéo depuis une source externe (comme Youtube).';
$GLOBALS['TL_LANG']['tl_iso_products']['opAttribute']         = 'Attributs du produit';
$GLOBALS['TL_LANG']['tl_iso_products']['opValueSets']         = 'Valeurs d\'option';
$GLOBALS['TL_LANG']['tl_iso_products']['opValue']             = 'Valeur';
$GLOBALS['TL_LANG']['tl_iso_products']['opLabel']             = 'Libellé';
$GLOBALS['TL_LANG']['tl_iso_products']['opPrice']             = 'Prix (surtaxe)';
$GLOBALS['TL_LANG']['tl_iso_products']['opDisable']           = 'Désactiver';
$GLOBALS['TL_LANG']['tl_iso_products']['opInherit']           = 'Libellé hérité';
$GLOBALS['TL_LANG']['tl_iso_products']['mmSrc']               = 'Aperçu';
$GLOBALS['TL_LANG']['tl_iso_products']['mmAlt']               = 'Texte alternatif';
$GLOBALS['TL_LANG']['tl_iso_products']['mmLink']              = 'Cible du lien';
$GLOBALS['TL_LANG']['tl_iso_products']['mmDesc']              = 'Description';
$GLOBALS['TL_LANG']['tl_iso_products']['mmTranslate']         = 'Translate';
$GLOBALS['TL_LANG']['tl_iso_products']['mmTranslateNone']     = array('None', 'Do not translate this image.');
$GLOBALS['TL_LANG']['tl_iso_products']['mmTranslateText']     = array('Text', 'Translate alt text and description for this image.');
$GLOBALS['TL_LANG']['tl_iso_products']['mmTranslateAll']      = array('All', 'Do not include this image in translated version.');
$GLOBALS['TL_LANG']['tl_iso_products']['existing_option_set'] = 'Sélectionner une option de produits existants';
$GLOBALS['TL_LANG']['tl_iso_products']['new_option_set']      = 'Créer une nouvelle option de produits';
$GLOBALS['TL_LANG']['tl_iso_products']['variantValuesLabel']  = 'Variante';

//$GLOBALS['TL_LANG']['tl_iso_products']['batch_size'][0]      = 'Taille du traitement par lots';
//$GLOBALS['TL_LANG']['tl_iso_products']['batch_size'][1]      = 'Sélectionner le nombre d\'enregistrements à traiter en une fois.';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_products']['new_product'][0]    = 'Nouveau produit';
$GLOBALS['TL_LANG']['tl_iso_products']['new_product'][1]    = 'Créer un nouveau produit';
$GLOBALS['TL_LANG']['tl_iso_products']['new_variant'][0]    = 'Ajouter une variante';
$GLOBALS['TL_LANG']['tl_iso_products']['new_variant'][1]    = 'Ajouter une variante d\'un produit.';
$GLOBALS['TL_LANG']['tl_iso_products']['edit'][0]           = 'Éditer le produit';
$GLOBALS['TL_LANG']['tl_iso_products']['edit'][1]           = 'Éditer le produit ID %s';
$GLOBALS['TL_LANG']['tl_iso_products']['copy'][0]           = 'Copier le produit';
$GLOBALS['TL_LANG']['tl_iso_products']['copy'][1]           = 'Copier le produit ID %s';
$GLOBALS['TL_LANG']['tl_iso_products']['cut'][0]            = 'Déplacer le produit';
$GLOBALS['TL_LANG']['tl_iso_products']['cut'][1]            = 'Déplacer le produit ID %s';
$GLOBALS['TL_LANG']['tl_iso_products']['delete'][0]         = 'Supprimer le produit';
$GLOBALS['TL_LANG']['tl_iso_products']['delete'][1]         = 'Supprimer le produit ID %s';
$GLOBALS['TL_LANG']['tl_iso_products']['toggle']            = array('Publish/unpublish product', 'Publish/unpublish product ID %s');
$GLOBALS['TL_LANG']['tl_iso_products']['show'][0]           = 'Afficher les détails du produit';
$GLOBALS['TL_LANG']['tl_iso_products']['show'][1]           = 'Afficher les détails du produit ID %s';
$GLOBALS['TL_LANG']['tl_iso_products']['generate'][0]       = 'Générer des variantes';
$GLOBALS['TL_LANG']['tl_iso_products']['generate'][1]       = 'Générer des variantes pour le produit ID %s';
$GLOBALS['TL_LANG']['tl_iso_products']['related'][0]        = 'Produits relatifs';
$GLOBALS['TL_LANG']['tl_iso_products']['related'][1]        = 'Gérer les produits relatifs pour le produit ID %s';
$GLOBALS['TL_LANG']['tl_iso_products']['filter']            = array('Filtres avancés', 'Appliquer les filtres avancés');
$GLOBALS['TL_LANG']['tl_iso_products']['filter_remove']     = array('Retirer les filtres', 'Retirer les filtres actifs');
$GLOBALS['TL_LANG']['tl_iso_products']['filter_noimages']   = array('Produits sans image', 'Afficher les produits sans image assignée');
$GLOBALS['TL_LANG']['tl_iso_products']['filter_nocategory'] = array('produits non assignés', 'Voir les produits qui ne sont pas affectés à une catégorie');
$GLOBALS['TL_LANG']['tl_iso_products']['filter_new_today']  = array('Ajouté aujourd\'hui', 'Voir les produits ajoutés aujourd\'hui');
$GLOBALS['TL_LANG']['tl_iso_products']['filter_new_week']   = array('Ajouté cette semaine', 'Voir les produits ajoutés durant les 7 derniers jours');
$GLOBALS['TL_LANG']['tl_iso_products']['filter_new_month']  = array('Ajouté ce mois', 'Voir les produits ajoutés durant les 30 derniers jours');
$GLOBALS['TL_LANG']['tl_iso_products']['tools'][0]          = 'Outils';
$GLOBALS['TL_LANG']['tl_iso_products']['tools'][1]          = 'Plus d\'options pour la gestion des produits';
$GLOBALS['TL_LANG']['tl_iso_products']['toggleGroups']      = array('Toggle all groups', 'Toggle all groups');
$GLOBALS['TL_LANG']['tl_iso_products']['toggleVariants']    = array('Toggle all variants', 'Toggle all variants');
$GLOBALS['TL_LANG']['tl_iso_products']['import'][0]         = 'Importer des éléments';
$GLOBALS['TL_LANG']['tl_iso_products']['import'][1]         = 'Importer des images et autres médias à partir d\'un dossier.';
$GLOBALS['TL_LANG']['tl_iso_products']['groups']            = array('Product Groups', 'Manage product groups');
$GLOBALS['TL_LANG']['tl_iso_products']['quick_edit'][0]     = 'Édition rapide des variantes';
$GLOBALS['TL_LANG']['tl_iso_products']['quick_edit'][1]     = 'Édition rapide des variantes pour le produit ID %s';
$GLOBALS['TL_LANG']['tl_iso_products']['downloads'][0]      = 'Téléchargements';
$GLOBALS['TL_LANG']['tl_iso_products']['downloads'][1]      = 'Éditer les téléchargements du produit ID %s';
$GLOBALS['TL_LANG']['tl_iso_products']['prices'][0]         = 'Gestion des prix';
$GLOBALS['TL_LANG']['tl_iso_products']['prices'][1]         = 'Gérer les prix du produit ID %s';


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_products']['general_legend']   = 'Paramètres généraux';
$GLOBALS['TL_LANG']['tl_iso_products']['meta_legend']      = 'Méta informations';
$GLOBALS['TL_LANG']['tl_iso_products']['pricing_legend']   = 'Paramètres du prix';
$GLOBALS['TL_LANG']['tl_iso_products']['inventory_legend'] = 'Paramètres de l\'inventaire';
$GLOBALS['TL_LANG']['tl_iso_products']['shipping_legend']  = 'Paramètres de livraison';
$GLOBALS['TL_LANG']['tl_iso_products']['options_legend']   = 'Paramètres des options du produit';
$GLOBALS['TL_LANG']['tl_iso_products']['media_legend']     = 'Gestionnaire de média';
$GLOBALS['TL_LANG']['tl_iso_products']['expert_legend']    = 'Expert settings';
$GLOBALS['TL_LANG']['tl_iso_products']['publish_legend']   = 'Publication';
$GLOBALS['TL_LANG']['tl_iso_products']['variant_legend']   = 'Configuration des variantes du produit';

//$GLOBALS['TL_LANG']['tl_iso_products']['filter_cache'][0] = 'Reconstruire le cache du filtre';
//$GLOBALS['TL_LANG']['tl_iso_products']['tax_legend']      = 'Paramètres de taxe';


/**
 * Table format
 */
$GLOBALS['TL_LANG']['tl_iso_products']['price_tiers']['min']        = 'Quantity';
$GLOBALS['TL_LANG']['tl_iso_products']['price_tiers']['min_format'] = 'from %s pcs.';
$GLOBALS['TL_LANG']['tl_iso_products']['price_tiers']['price']      = 'Price';