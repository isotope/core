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
 * Table tl_iso_mail_content
 */
$GLOBALS['TL_DCA']['tl_iso_mail_content'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'ptable'                      => 'tl_iso_mail',
		'enableVersioning'            => true,
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 4,
			'fields'                  => array('language'),
			'flag'                    => 1,
			'panelLayout'             => 'filter;search,limit',
			'headerFields'            => array('name', 'senderName', 'sender'),
			'child_record_callback'   => array('tl_iso_mail_content', 'listRows'),
		),
		'label' => array
		(
			'fields'                  => array('subject', 'language'),
			'format'                  => '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
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
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_mail_content']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_mail_content']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_mail_content']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_mail_content']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),
		)
	),

	// Palettes
	'palettes' => array
	(
		'default'                     => '{settings_legend},language,fallback;{content_legend},subject,html,text,textOnly',
	),

	// Fields
	'fields' => array
	(
		'language' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_mail_content']['language'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'default'                 => $GLOBALS['TL_LANGUAGE'],
			'options'                 => $this->getLanguages(),
			'eval'                    => array('mandatory'=>true, 'tl_class'=>'w50'),
		),
		'fallback' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_mail_content']['fallback'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('tl_class'=>'w50 m12'),
		),
		'subject' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_mail_content']['subject'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'long'),
		),
		'text' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_mail_content']['text'],
			'exclude'                 => true,
			'inputType'               => 'textarea',
			'eval'                    => array('mandatory'=>true, 'decodeEntities'=>true, 'helpwizard'=>true),
			'explanation'             => 'isoMailTokens',
		),
		'textOnly' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_mail_content']['textOnly'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange'=>true),
		),
		'html' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_mail_content']['html'],
			'exclude'                 => true,
			'inputType'               => 'textarea',
			'eval'                    => array('mandatory'=>true, 'rte'=>'tinyMCE', 'decodeEntities'=>true, 'helpwizard'=>true),
			'explanation'             => 'isoMailTokens',
		),
	)
);


class tl_iso_mail_content extends Backend
{
	protected $arrLanguages;
	
	public function listRows($arrRow)
	{
		if (!is_array($this->arrLanguages))
		{
			$arrLanguages = $this->getLanguages();
		}
		
		return '
<div class="cte_type published"><strong>' . $arrRow['subject'] . '</strong> - ' . $arrLanguages[$arrRow['language']] . ($arrRow['fallback'] ? (' (' . $GLOBALS['TL_LANG']['tl_iso_mail_content']['fallback'][0] . ')') : '') . '</div>
<div class="limit_height' . (!$GLOBALS['TL_CONFIG']['doNotCollapse'] ? ' h64' : '') . ' block">
' . $arrRow['html'] . '
<hr />
' . nl2br($arrRow['text']) . '
</div>' . "\n";
	}
}

