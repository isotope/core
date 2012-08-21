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
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Rebecca Jutzi <rebecca.jutzi@bluewin.ch>
 * @author     Simon Moos <cc@simonmoos.com>
 * @author     Cyril Ponce <cyril@contao.fr>
 * @author     Stéphane Cagni <stephane@cagni.fr>
 * @author     Katelle Ave <contact@graphikat.net>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

$GLOBALS['TL_LANG']['tl_iso_attributes']['name'][0] = 'Nom';
$GLOBALS['TL_LANG']['tl_iso_attributes']['name'][1] = 'Merci d\' entrer un nom pour cet attribut.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['field_name'][0] = 'Nom interne';
$GLOBALS['TL_LANG']['tl_iso_attributes']['field_name'][1] = 'Le nom interne est le nom du champ dans la base de données et doit être unique.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['type'][0] = 'Type';
$GLOBALS['TL_LANG']['tl_iso_attributes']['type'][1] = 'Merci de sélectionner un type pour cet attribut.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['legend'][0] = 'Groupe de champs';
$GLOBALS['TL_LANG']['tl_iso_attributes']['variant_option'][0] = 'Ajouter à l\'assistant de variantes du produit';
$GLOBALS['TL_LANG']['tl_iso_attributes']['variant_option'][1] = 'Si sélectionné, cet attribut sera ajouté l\'assistant de variantes de produit pour une utilisation comme une option variante de produit.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['customer_defined'][0] = 'Défini par le client';
$GLOBALS['TL_LANG']['tl_iso_attributes']['customer_defined'][1] = 'Merci de choisir si cette valeur doit être définie par le client (frontend).';
$GLOBALS['TL_LANG']['tl_iso_attributes']['description'][0] = 'Description';
$GLOBALS['TL_LANG']['tl_iso_attributes']['description'][1] = 'La description est indiquée par un indice à l\'utilisateur backend.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['options'][0] = 'Options';
$GLOBALS['TL_LANG']['tl_iso_attributes']['options'][1] = 'Merci d\' entrer une ou plusieurs options. Utilisez les boutons pour ajouter, déplacer ou supprimer une option. Si vous travaillez sans l\'aide de JavaScript, vous devez enregistrer vos modifications avant de modifier l\'ordre!';
$GLOBALS['TL_LANG']['tl_iso_attributes']['mandatory'][0] = 'Champ obligatoire';
$GLOBALS['TL_LANG']['tl_iso_attributes']['mandatory'][1] = 'Le produit ne sera pas ajouté au panier si ce champ est vide.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['multiple'][0] = 'Sélection multiple';
$GLOBALS['TL_LANG']['tl_iso_attributes']['multiple'][1] = 'Autorise les visiteurs à sélectionner plusieurs options.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['size'][0] = 'Taile de la liste';
$GLOBALS['TL_LANG']['tl_iso_attributes']['size'][1] = 'Vous pouvez entrer ici la taille de la boîte de sélection.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['extensions'][0] = 'Types de champs autorisés';
$GLOBALS['TL_LANG']['tl_iso_attributes']['extensions'][1] = 'Une liste des extensions de fichier valide séparée par des virgules.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['rte'][0] = 'Utiliser l\'éditeur HTML';
$GLOBALS['TL_LANG']['tl_iso_attributes']['rte'][1] = 'Sélectionnez un fichier de configuration tinyMCE pour permettre l\'éditeur de texte riche.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['multilingual'][0] = 'Multilingue';
$GLOBALS['TL_LANG']['tl_iso_attributes']['multilingual'][1] = 'Cochez cette case si ce champ doit être traduit.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['rgxp'][0] = 'Validation de saisie';
$GLOBALS['TL_LANG']['tl_iso_attributes']['maxlength'][0] = 'Longueur maximale';
$GLOBALS['TL_LANG']['tl_iso_attributes']['maxlength'][1] = 'Limite la longueur du champ à un certain nombre de caractères (texte) ou octets (téléchargement).';
$GLOBALS['TL_LANG']['tl_iso_attributes']['foreignKey'][0] = 'Table et champ contenant les options';
$GLOBALS['TL_LANG']['tl_iso_attributes']['foreignKey'][1] = 'Au lieu d\'ajouter des options vous pouvez entrer une combinaison table.champ pour les sélectionner depuis la base de données.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['conditionField'][0] = 'Champ parent';
$GLOBALS['TL_LANG']['tl_iso_attributes']['gallery'][0] = 'Galerie d\'images';
$GLOBALS['TL_LANG']['tl_iso_attributes']['gallery'][1] = 'Des galeries d\'images différentes peuvent être développées pour présenter des fichiers multimédias dans un style personnalisé.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['fe_sorting'][0] = 'Ajouter aux option de la liste de tri';
$GLOBALS['TL_LANG']['tl_iso_attributes']['be_filter'][0] = 'Peut-être filtré en back office';
$GLOBALS['TL_LANG']['tl_iso_attributes']['be_search'][0] = 'Peut-être recherché en back office';
$GLOBALS['TL_LANG']['tl_iso_attributes']['be_search'][1] = 'Le moteur de recherche doit-il inclure ce champ?';
$GLOBALS['TL_LANG']['tl_iso_attributes']['fe_filter'][0] = 'Peut-être filtré en front office';
$GLOBALS['TL_LANG']['tl_iso_attributes']['fe_filter'][1] = 'Cet atrribut peut-il être filtré en frontend?';
$GLOBALS['TL_LANG']['tl_iso_attributes']['fe_search'][0] = 'Peut-être recherché en front office';
$GLOBALS['TL_LANG']['tl_iso_attributes']['fe_search'][1] = 'Le moteur de recherche doit-il inclure ce champ?';
$GLOBALS['TL_LANG']['tl_iso_attributes']['opValue'] = 'Valeur';
$GLOBALS['TL_LANG']['tl_iso_attributes']['opLabel'] = 'Libellé';
$GLOBALS['TL_LANG']['tl_iso_attributes']['opDefault'] = 'Par défaut';
$GLOBALS['TL_LANG']['tl_iso_attributes']['opGroup'] = 'Groupe';
$GLOBALS['TL_LANG']['tl_iso_attributes']['digit'][0] = 'Caractères numériques';
$GLOBALS['TL_LANG']['tl_iso_attributes']['digit'][1] = 'Autorise les caractères numériques, moins (-), point (.) et espace ( ).';
$GLOBALS['TL_LANG']['tl_iso_attributes']['alpha'][0] = 'Caractères alphabétiques';
$GLOBALS['TL_LANG']['tl_iso_attributes']['alpha'][1] = 'Autorise les caractères alphabétiques, moins (-), point (.) et espace ( ).';
$GLOBALS['TL_LANG']['tl_iso_attributes']['alnum'][0] = 'Caractères alphanumériques';
$GLOBALS['TL_LANG']['tl_iso_attributes']['alnum'][1] = 'Autorise les caractères alphanumériques, moins (-), point (.), underscore (_) et espace ( ).';
$GLOBALS['TL_LANG']['tl_iso_attributes']['extnd'][0] = 'Caractères alphanumériques étendus';
$GLOBALS['TL_LANG']['tl_iso_attributes']['date'][0] = 'Date';
$GLOBALS['TL_LANG']['tl_iso_attributes']['date'][1] = 'Vérifie si l\'entrée correspond au format de date.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['time'][0] = 'Heure';
$GLOBALS['TL_LANG']['tl_iso_attributes']['time'][1] = 'Vérifie si l\'entrée correspond au format d\'heure.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['datim'][0] = 'Date et heure';
$GLOBALS['TL_LANG']['tl_iso_attributes']['datim'][1] = 'Vérifie si l\'entrée correspond au formats de date et d\'heure.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['phone'][0] = 'Numéro de téléphone';
$GLOBALS['TL_LANG']['tl_iso_attributes']['phone'][1] = 'Autorise les caractères numériques, plus (+), moins (-), slash (/), parenthèses () et espace ( ).';
$GLOBALS['TL_LANG']['tl_iso_attributes']['email'][0] = 'Adresse e-mail';
$GLOBALS['TL_LANG']['tl_iso_attributes']['email'][1] = 'Vérifie si l\'adresse e-mail est valide.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['url'][0] = 'Format d\'URL';
$GLOBALS['TL_LANG']['tl_iso_attributes']['url'][1] = 'Vérifie si l\'URL est valide.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['price'][0] = 'Prix';
$GLOBALS['TL_LANG']['tl_iso_attributes']['price'][1] = 'Vérifie si le prix est valide.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['discount'][0] = 'Remise';
$GLOBALS['TL_LANG']['tl_iso_attributes']['discount'][1] = 'Vérifie si le format de réduction est valide.<br />Exemple: -10%, -10, +10, +10%';
$GLOBALS['TL_LANG']['tl_iso_attributes']['surcharge'][0] = 'Supplément';
$GLOBALS['TL_LANG']['tl_iso_attributes']['surcharge'][1] = 'Vérifie si le format de supplément est valide.<br />Exemple: 10.00, 10%';
$GLOBALS['TL_LANG']['tl_iso_attributes']['new'][0] = 'Nouvel attribut';
$GLOBALS['TL_LANG']['tl_iso_attributes']['new'][1] = 'Créer un nouvel attribut';
$GLOBALS['TL_LANG']['tl_iso_attributes']['edit'][0] = 'Editer l\'attribut';
$GLOBALS['TL_LANG']['tl_iso_attributes']['edit'][1] = 'Editer l\'attribut ID %s.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['copy'][0] = 'Copier l\'attribut';
$GLOBALS['TL_LANG']['tl_iso_attributes']['copy'][1] = 'Copier l\'attribut ID %s.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['delete'][0] = 'Supprimer l\'attribut';
$GLOBALS['TL_LANG']['tl_iso_attributes']['delete'][1] = 'Supprimer l\'attribut ID %s.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['show'][0] = 'Afficher les détails de l\'attribut';
$GLOBALS['TL_LANG']['tl_iso_attributes']['show'][1] = 'Afficher les détails de l\'attribut ID %s.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['attribute_legend'] = 'Nom et type d\'attribut';
$GLOBALS['TL_LANG']['tl_iso_attributes']['description_legend'] = 'Description';
$GLOBALS['TL_LANG']['tl_iso_attributes']['options_legend'] = 'Options';
$GLOBALS['TL_LANG']['tl_iso_attributes']['config_legend'] = 'Configuration d\'attribut';
$GLOBALS['TL_LANG']['tl_iso_attributes']['validation_legend'] = 'Validation de saisie';
$GLOBALS['TL_LANG']['tl_iso_attributes']['search_filters_legend'] = 'Paramètres de recherche et de filtre';

