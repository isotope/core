<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 * Copyright (C) 2005 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at http://www.gnu.org/licenses/.
 *
 * PHP version 5
 * @copyright  Winans Creative 2009
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Table tl_iso_mail
 */
$GLOBALS['TL_DCA']['tl_iso_mail'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'ctables'                     => array('tl_iso_mail_content'),
		'enableVersioning'            => true
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 1,
			'fields'                  => array('name'),
			'flag'                    => 1,
			'panelLayout'             => 'filter;search,limit'
		),
		'label' => array
		(
			'fields'                  => array('name', 'sender'),
			'format'                  => '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
//			'label_callback'          => array('', 'addIcon')
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
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_mail']['edit'],
				'href'                => 'table=tl_iso_mail_content',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_mail']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_mail']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_mail']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),
		)
	),

	// Palettes
	'palettes' => array
	(
		'default'                     => '{name_legend},name;{address_legend},senderName,sender,cc,bcc;{expert_legend:hide},template',
	),

	// Fields
	'fields' => array
	(
		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_mail']['name'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'long'),
		),
		'senderName' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_mail']['senderName'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
		),
		'sender' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_mail']['sender'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'rgxp'=>'email', 'tl_class'=>'w50'),
		),
		'cc' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_mail']['cc'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'rgxp'=>'extnd', 'tl_class'=>'w50'),
		),
		'bcc' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_mail']['bcc'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'rgxp'=>'extnd', 'tl_class'=>'w50'),
		),
		'template' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_mail']['template'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'default'                 => 'mail_default',
			'options'                 => $this->getTemplateGroup('mail_'),
			'eval'                    => array('mandatory'=>true, 'tl_class'=>'w50'),
		),
	)
);

