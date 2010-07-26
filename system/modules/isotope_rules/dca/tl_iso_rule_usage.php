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
 * @copyright  Leo Feyer 2005-2010
 * @author     Leo Feyer <http://www.typolight.org>
 * @package    Faq
 * @license    LGPL
 * @filesource
 */


/**
 * Table tl_iso_rule_usage
 */
$GLOBALS['TL_DCA']['tl_iso_rule_usage'] = array
(
/*
	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'ctable'                      => array('tl_faq'),
		'switchToEdit'                => true,
		'enableVersioning'            => true,
		'onload_callback' => array
		(
			array('tl_faq_category', 'checkPermission')
		)
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 1,
			'fields'                  => array('title'),
			'flag'                    => 1,
			'panelLayout'             => 'search,limit'
		),
		'label' => array
		(
			'fields'                  => array('title'),
			'format'                  => '%s'
		),
		'global_operations' => array
		(
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_faq_category']['edit'],
				'href'                => 'table=tl_faq',
				'icon'                => 'edit.gif',
				'attributes'          => 'class="contextmenu"'
			),
			'editheader' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_faq_category']['editheader'],
				'href'                => 'act=edit',
				'icon'                => 'header.gif',
				'button_callback'     => array('tl_faq_category', 'editHeader'),
				'attributes'          => 'class="edit-header"'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_faq_category']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_faq_category']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['tl_faq_category']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_faq_category']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			)
		)
	),

	// Palettes
	'palettes' => array
	(
		'__selector__'                => array('allowComments'),
		'default'                     => '{title_legend},title,headline,jumpTo;{comments_legend:hide},allowComments'
	),

	// Subpalettes
	'subpalettes' => array
	(
		'allowComments'               => 'notify,template,sortOrder,perPage,moderate,bbcode,requireLogin,disableCaptcha'
	),

	// Fields
	'fields' => array
	(
		'title' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_faq_category']['title'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50')
		),
		'headline' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_faq_category']['headline'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50')
		),
		'jumpTo' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_faq_category']['jumpTo'],
			'exclude'                 => true,
			'inputType'               => 'pageTree',
			'eval'                    => array('fieldType'=>'radio', 'tl_class'=>'clr')
		),
		'allowComments' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_faq_category']['allowComments'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange'=>true)
		),
		'notify' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_faq_category']['notify'],
			'default'                 => 'notify_admin',
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => array('notify_admin', 'notify_author', 'notify_both'),
			'reference'               => &$GLOBALS['TL_LANG']['tl_faq_category'],
			'eval'                    => array('tl_class'=>'w50')
		),
		'template' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_faq_category']['template'],
			'default'                 => 'com_default',
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => $this->getTemplateGroup('com_'),
			'eval'                    => array('tl_class'=>'w50')
		),
		'sortOrder' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_faq_category']['sortOrder'],
			'default'                 => 'ascending',
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => array('ascending', 'descending'),
			'reference'               => &$GLOBALS['TL_LANG']['MSC'],
			'eval'                    => array('tl_class'=>'w50')
		),
		'perPage' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_faq_category']['perPage'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'digit', 'tl_class'=>'w50')
		),
		'moderate' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_faq_category']['moderate'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('tl_class'=>'w50')
		),
		'bbcode' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_faq_category']['bbcode'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('tl_class'=>'w50')
		),
		'requireLogin' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_faq_category']['requireLogin'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('tl_class'=>'w50')
		),
		'disableCaptcha' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_faq_category']['disableCaptcha'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('tl_class'=>'w50')
		)
	)
	*/
);




?>