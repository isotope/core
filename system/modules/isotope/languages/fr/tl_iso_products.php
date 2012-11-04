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
$GLOBALS['TL_LANG']['tl_iso_products']['id'][0]                 = 'ID du produit';
$GLOBALS['TL_LANG']['tl_iso_products']['pages'][0]              = 'Catégories';
$GLOBALS['TL_LANG']['tl_iso_products']['pages'][1]              = 'Sélectionner une catégorie (les catégories sont basées sur les pages afin de profiter des fonctionnalités de Contao telles que la création automatique de la navigation, la protection, les modèles, et l\'intégration complète avec les éléments de contenu).';
$GLOBALS['TL_LANG']['tl_iso_products']['type'][0]               = 'Type de produit';
$GLOBALS['TL_LANG']['tl_iso_products']['type'][1]               = 'Les types de produit sont définis dans le gestionnaire de types de produits';
$GLOBALS['TL_LANG']['tl_iso_products']['alias']                 = array('Alias', 'Vous pouvez entrer un alias unique pour ce produit. Si vous ne remplissez pas le champ, il sera automatiquement généré à partir du nom.');
$GLOBALS['TL_LANG']['tl_iso_products']['name']                  = array('Nom', 'Veuillez, s\'il vous plaît, entrer le nom de ce produit.');
$GLOBALS['TL_LANG']['tl_iso_products']['sku']                   = array('SKU (Unité de gestion des stocks)', 'Veuillez, s\'il vous plaît, entrer une unité de gestion des stocks unique pour ce produit.');
$GLOBALS['TL_LANG']['tl_iso_products']['shipping_weight'][0]    = 'Poids';
$GLOBALS['TL_LANG']['tl_iso_products']['shipping_weight'][1]    = 'Saisir le poids de ce produit. Il peut être utilisé pour calculer le coût d\'expédition.';
$GLOBALS['TL_LANG']['tl_iso_products']['teaser']                = array('Résumé', 'Veuillez, s\'il vous plaît, entrer un résumé.');
$GLOBALS['TL_LANG']['tl_iso_products']['description']           = array('Description', 'Veuillez, s\'il vous plaît, entrer la description du produit.');
$GLOBALS['TL_LANG']['tl_iso_products']['description_meta']      = array('Méta description', 'Meta description will be placed in the header on product detail page, for search engine optimization.');
$GLOBALS['TL_LANG']['tl_iso_products']['keywords_meta']         = array('Méta mots-clés', 'Meta keywords will be placed in the header on product detail page, for search engine optimization.');
$GLOBALS['TL_LANG']['tl_iso_products']['price']                 = array('Prix', 'Veuillez, s\'il vous plaît, entrer un prix pour ce produit.');
$GLOBALS['TL_LANG']['tl_iso_products']['shipping_exempt'][0]    = 'Aucun frais de livraison';
$GLOBALS['TL_LANG']['tl_iso_products']['shipping_exempt'][1]    = 'Vérifier si le produit n\'est pas un produit livré (comme les produits téléchargeables).';
$GLOBALS['TL_LANG']['tl_iso_products']['tax_class']             = array('Tax Class', 'Select your appropriate tax class.');
$GLOBALS['TL_LANG']['tl_iso_products']['baseprice']             = array('Prix de base', 'Veuillez, s\'il vous plaît, entrer votre prix de base.');
$GLOBALS['TL_LANG']['tl_iso_products']['images']                = array('Images', 'Importer des images pour ce produit. Veuillez, s\'il vous plaît, sauvegarder le produit après avoir sélectionné un fichier.');
$GLOBALS['TL_LANG']['tl_iso_products']['protected']             = array('Protéger le produit', 'Restreindre l\'accès du produit à certains groupes de membres.');
$GLOBALS['TL_LANG']['tl_iso_products']['groups']                = array('Groupes de membres autorisés', 'Ces groupes pourront accéder au produit.');
$GLOBALS['TL_LANG']['tl_iso_products']['guests']                = array('Visible par les invités seulement', 'Masquer le produit s\'il y a un utilisateur authentifié.');
$GLOBALS['TL_LANG']['tl_iso_products']['cssID']                 = array('ID / classe(s) CSS', 'Ici, vous pouvez ajouter un ID et une ou plusieurs classes.');
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
$GLOBALS['TL_LANG']['tl_iso_products']['mmTranslate']         = 'Traduire';
$GLOBALS['TL_LANG']['tl_iso_products']['mmTranslateNone']     = array('Non', 'Ne pas traduire cette image.');
$GLOBALS['TL_LANG']['tl_iso_products']['mmTranslateText']     = array('Texte', 'Traduire le texte alternatif et la description pour cette image.');
$GLOBALS['TL_LANG']['tl_iso_products']['mmTranslateAll']      = array('Exclure', 'Ne pas inclure cette image dans la version traduite.');
$GLOBALS['TL_LANG']['tl_iso_products']['existing_option_set'] = 'Sélectionner une option de produits existants';
$GLOBALS['TL_LANG']['tl_iso_products']['new_option_set']      = 'Créer une nouvelle option de produits';
$GLOBALS['TL_LANG']['tl_iso_products']['variantValuesLabel']  = 'Variante';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_products']['new_product'][0]    = 'Nouveau produit';
$GLOBALS['TL_LANG']['tl_iso_products']['new_product'][1]    = 'Créer un nouveau produit';
$GLOBALS['TL_LANG']['tl_iso_products']['new_variant'][0]    = 'Ajouter une variante';
$GLOBALS['TL_LANG']['tl_iso_products']['new_variant'][1]    = 'Ajouter une variante d\'un produit.';
$GLOBALS['TL_LANG']['tl_iso_products']['edit'][0]           = 'Éditer le produit';
$GLOBALS['TL_LANG']['tl_iso_products']['edit'][1]           = 'Éditer le produit ID %s';
$GLOBALS['TL_LANG']['tl_iso_products']['copy'][0]           = 'Dupliquer le produit';
$GLOBALS['TL_LANG']['tl_iso_products']['copy'][1]           = 'Dupliquer le produit ID %s';
$GLOBALS['TL_LANG']['tl_iso_products']['cut'][0]            = 'Déplacer le produit';
$GLOBALS['TL_LANG']['tl_iso_products']['cut'][1]            = 'Déplacer le produit ID %s';
$GLOBALS['TL_LANG']['tl_iso_products']['delete'][0]         = 'Supprimer le produit';
$GLOBALS['TL_LANG']['tl_iso_products']['delete'][1]         = 'Supprimer le produit ID %s';
$GLOBALS['TL_LANG']['tl_iso_products']['toggle']            = array('Publier/dé-publier un produit', 'Publier/dé-publier le produit ID %s');
$GLOBALS['TL_LANG']['tl_iso_products']['show'][0]           = 'Détails du produit';
$GLOBALS['TL_LANG']['tl_iso_products']['show'][1]           = 'Afficher les détails du produit ID %s';
$GLOBALS['TL_LANG']['tl_iso_products']['generate'][0]       = 'Générer des variantes';
$GLOBALS['TL_LANG']['tl_iso_products']['generate'][1]       = 'Générer des variantes pour le produit ID %s';
$GLOBALS['TL_LANG']['tl_iso_products']['related'][0]        = 'Produits similaires';
$GLOBALS['TL_LANG']['tl_iso_products']['related'][1]        = 'Gérer les produits similaires pour le produit ID %s';
$GLOBALS['TL_LANG']['tl_iso_products']['filter']            = array('Filtres avancés', 'Appliquer les filtres avancés');
$GLOBALS['TL_LANG']['tl_iso_products']['filter_remove']     = array('Retirer les filtres', 'Retirer les filtres actifs');
$GLOBALS['TL_LANG']['tl_iso_products']['filter_noimages']   = array('Produits sans image', 'Afficher les produits sans image assignée');
$GLOBALS['TL_LANG']['tl_iso_products']['filter_nocategory'] = array('produits non assignés', 'Voir les produits qui ne sont pas affectés à une catégorie');
$GLOBALS['TL_LANG']['tl_iso_products']['filter_new_today']  = array('Ajouté aujourd\'hui', 'Voir les produits ajoutés aujourd\'hui');
$GLOBALS['TL_LANG']['tl_iso_products']['filter_new_week']   = array('Ajouté cette semaine', 'Voir les produits ajoutés durant les 7 derniers jours');
$GLOBALS['TL_LANG']['tl_iso_products']['filter_new_month']  = array('Ajouté ce mois', 'Voir les produits ajoutés durant les 30 derniers jours');
$GLOBALS['TL_LANG']['tl_iso_products']['tools'][0]          = 'Outils';
$GLOBALS['TL_LANG']['tl_iso_products']['tools'][1]          = 'Plus d\'options pour la gestion des produits';
$GLOBALS['TL_LANG']['tl_iso_products']['toggleGroups']      = array('Masquer/Déployer tous les groupes', 'Masquer/Déployer tous les groupes');
$GLOBALS['TL_LANG']['tl_iso_products']['toggleVariants']    = array('Masquer/Déployer toutes les variantes', 'Masquer/Déployer toutes les variantes');
$GLOBALS['TL_LANG']['tl_iso_products']['import'][0]         = 'Importer des éléments';
$GLOBALS['TL_LANG']['tl_iso_products']['import'][1]         = 'Importer des images et autres médias à partir d\'un dossier.';
$GLOBALS['TL_LANG']['tl_iso_products']['groups']            = array('Groupes de produits', 'Gérer les groupes de produits');
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
$GLOBALS['TL_LANG']['tl_iso_products']['expert_legend']    = 'Paramètres avancés';
$GLOBALS['TL_LANG']['tl_iso_products']['publish_legend']   = 'Publication';
$GLOBALS['TL_LANG']['tl_iso_products']['variant_legend']   = 'Configuration des variantes du produit';


/**
 * Table format
 */
$GLOBALS['TL_LANG']['tl_iso_products']['price_tiers']['min']        = 'Quantité';
$GLOBALS['TL_LANG']['tl_iso_products']['price_tiers']['min_format'] = 'de %s pièces.';
$GLOBALS['TL_LANG']['tl_iso_products']['price_tiers']['price']      = 'Prix';