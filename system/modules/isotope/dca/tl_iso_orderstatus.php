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
 */


/**
 * Table tl_iso_orderstatus
 */
$GLOBALS['TL_DCA']['tl_iso_orderstatus'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'					=> 'Table',
		'label'							=> &$GLOBALS['TL_LANG']['IMD']['orderstatus'][0],
		'enableVersioning'				=> true,
		'closed'						=> true,
		'onload_callback'				=> array
		(
			array('IsotopeBackend', 'initializeSetupModule'),
		)
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'						=> 5,
			'fields'					=> array('name'),
			'panelLayout'				=> 'filter;search,limit',
			'paste_button_callback'		=> array('tl_iso_orderstatus', 'pasteButton'),
			'icon'						=> 'system/modules/isotope/html/traffic-light.png',
		),
		'label' => array
		(
			'fields'					=> array('name'),
			'format'					=> '%s',
//			'format'					=> '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
//			'maxCharacters'				=> 100,
//			'label_callback'			=> array('tl_iso_orderstatus', 'addIcon'),
		),
		'global_operations' => array
		(
			'back' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['MSC']['backBT'],
				'href'					=> 'mod=&table=',
				'class'					=> 'header_back',
				'attributes'			=> 'onclick="Backend.getScrollOffset();"',
			),
			'new' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_orderstatus']['new'],
				'href'					=> 'act=paste&amp;mode=create',
				'class'					=> 'header_new',
				'attributes'			=> 'onclick="Backend.getScrollOffset();"',
			),
			'all' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'					=> 'act=select',
				'class'					=> 'header_edit_all',
				'attributes'			=> 'onclick="Backend.getScrollOffset();" accesskey="e"'
			),
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_orderstatus']['edit'],
				'href'					=> 'act=edit',
				'icon'					=> 'edit.gif'
			),
			'copy' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_orderstatus']['copy'],
				'href'					=> 'act=paste&amp;mode=copy',
				'icon'					=> 'copy.gif'
			),
			'cut' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_orderstatus']['cut'],
				'href'					=> 'act=paste&amp;mode=cut',
				'icon'					=> 'cut.gif',
				'attributes'			=> 'onclick="Backend.getScrollOffset();"'
			),
			'delete' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_orderstatus']['delete'],
				'href'					=> 'act=delete',
				'icon'					=> 'delete.gif',
				'attributes'			=> 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_orderstatus']['show'],
				'href'					=> 'act=show',
				'icon'					=> 'show.gif'
			),
		)
	),

	// Palettes
	'palettes' => array
	(
		'default'						=> '{name_legend},name,paid,welcomescreen;{email_legend},mail_customer,mail_admin,sales_email',
	),

	// Fields
	'fields' => array
	(
		'name' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_orderstatus']['name'],
			'exclude'					=> true,
			'inputType'					=> 'text',
			'eval'						=> array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'clr'),
		),
		'paid' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_orderstatus']['paid'],
			'exclude'					=> true,
			'inputType'					=> 'checkbox',
			'eval'						=> array('tl_class'=>'w50'),
		),
		'welcomescreen' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_orderstatus']['welcomescreen'],
			'exclude'					=> true,
			'inputType'					=> 'checkbox',
			'eval'						=> array('tl_class'=>'w50'),
		),
		'mail_customer' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_orderstatus']['mail_customer'],
			'exclude'					=> true,
			'inputType'					=> 'select',
			'foreignKey'				=> 'tl_iso_mail.name',
			'eval'						=> array('includeBlankOption'=>true)
		),
		'mail_admin' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_orderstatus']['mail_admin'],
			'exclude'					=> true,
			'inputType'					=> 'select',
			'foreignKey'				=> 'tl_iso_mail.name',
			'eval'						=> array('includeBlankOption'=>true, 'tl_class'=>'w50')
		),
		'sales_email' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_orderstatus']['sales_email'],
			'exclude'					=> true,
			'inputType'					=> 'text',
			'eval'						=> array('maxlength'=>255, 'rgxp'=>'email', 'tl_class'=>'w50')
		),
	)
);


class tl_iso_orderstatus extends \Backend
{

	/**
	 * Add an image to each record
	 * @param array
	 * @param string
	 * @return string
	 */
	public function addIcon($row, $label)
	{
		$image = 'published';

		if (!$row['published'] || (strlen($row['start']) && $row['start'] > time()) || (strlen($row['stop']) && $row['stop'] < time()))
		{
			$image = 'un'.$image;
		}

		return sprintf('<div class="list_icon" style="background-image:url(\'system/themes/%s/images/%s.gif\');">%s</div>', $this->getTheme(), $image, $label);
	}


	/**
	 * Return the paste button
	 * @param object
	 * @param array
	 * @param string
	 * @param boolean
	 * @param array
	 * @return string
	 */
	public function pasteButton(DataContainer $dc, $row, $table, $cr, $arrClipboard=false)
	{
		if ($row['id'] == 0)
		{
			$imagePasteInto = $this->generateImage('pasteinto.gif', sprintf($GLOBALS['TL_LANG'][$dc->table]['pasteinto'][1], $row['id']));

			return $cr ? $this->generateImage('pasteinto_.gif').' ' : '<a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&mode=2&pid='.$row['id'].'&id='.$arrClipboard['id']).'" title="'.specialchars(sprintf($GLOBALS['TL_LANG'][$dc->table]['pasteinto'][1], $row['id'])).'" onclick="Backend.getScrollOffset();">'.$imagePasteInto.'</a> ';
		}

		$imagePasteAfter = $this->generateImage('pasteafter.gif', sprintf($GLOBALS['TL_LANG'][$dc->table]['pasteafter'][1], $row['id']));

		return (($arrClipboard['mode'] == 'cut' && $arrClipboard['id'] == $row['id']) || $cr) ? $this->generateImage('pasteafter_.gif').' ' : '<a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&mode=1&pid='.$row['id'].'&id='.$arrClipboard['id']).'" title="'.specialchars(sprintf($GLOBALS['TL_LANG'][$dc->table]['pasteafter'][1], $row['id'])).'" onclick="Backend.getScrollOffset();">'.$imagePasteAfter.'</a> ';
	}
}

