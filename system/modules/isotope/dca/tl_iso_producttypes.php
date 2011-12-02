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
 * @copyright  Isotope eCommerce Workgroup 2009-2011
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Table tl_iso_producttypes
 */
$GLOBALS['TL_DCA']['tl_iso_producttypes'] = array
(
	// Config
	'config' => array
	(
		'dataContainer'				=> 'Table',
		'enableVersioning'			=> true,
		'closed'					=> true,
		'onload_callback' => array
		(
			array('tl_iso_producttypes', 'checkPermission'),
			array('IsotopeBackend', 'initializeSetupModule'),
		)
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'					=> 1,
			'fields'				=> array('name'),
			'flag'					=> 1,
			'panelLayout'			=> 'filter;search,limit',
		),
		'label' => array
		(
			'fields'				=> array('name', 'fallback'),
			'format'				=> '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
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
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['new'],
				'href'					=> 'act=create',
				'class'					=> 'header_new',
				'attributes'			=> 'onclick="Backend.getScrollOffset();"',
			),
			'all' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'				=> 'act=select',
				'class'				=> 'header_edit_all',
				'attributes'		=> 'onclick="Backend.getScrollOffset();"'
			),
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['edit'],
				'href'				=> 'act=edit',
				'icon'				=> 'edit.gif',
			),
			'copy' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['copy'],
				'href'				=> 'act=copy',
				'icon'				=> 'copy.gif',
			),
			'delete' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['delete'],
				'href'				=> 'act=delete',
				'icon'				=> 'delete.gif',
				'attributes'		=> 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
			),
			'show' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['show'],
				'href'				=> 'act=show',
				'icon'				=> 'show.gif'
			)
		)
	),

	// Palettes
	'palettes' => array
	(
		'__selector__'				=> array('class', 'variants'),
		'default'					=> '{name_legend},name,class,fallback,prices;{description_legend:hide},description;{template_legend},list_template,reader_template;{attributes_legend},attributes,variants;{download_legend:hide},downloads',
		'regular'					=> '{name_legend},name,class,fallback,prices;{description_legend:hide},description;{template_legend},list_template,reader_template;{attributes_legend},attributes,variants;{download_legend:hide},downloads',
	),

	// Subpalettes
	'subpalettes' => array
	(
		'variants'					=> 'variant_attributes,force_variant_options',
	),

	// Fields
	'fields' => array
	(
		'name' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['name'],
			'exclude'				=> true,
			'inputType'				=> 'text',
			'eval'					=> array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50')
		),
		'class' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['class'],
			'exclude'				=> true,
			'inputType'				=> 'select',
			'default'				=> 'regular',
			'options'				=> array_keys($GLOBALS['ISO_PRODUCT']),
			'reference'				=> &$GLOBALS['ISO_LANG']['PRODUCT'],
			'eval'					=> array('mandatory'=>true, 'submitOnChange'=>true, 'helpwizard'=>true, 'tl_class'=>'w50'),
		),
		'fallback' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['fallback'],
			'exclude'				=> true,
			'inputType'				=> 'checkbox',
			'eval'					=> array('fallback'=>true, 'tl_class'=>'w50'),
		),
		'prices' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['prices'],
			'exclude'				=> true,
			'inputType'				=> 'checkbox',
			'eval'					=> array('tl_class'=>'w50'),
		),
		'list_template' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['list_template'],
			'inputType'				=> 'select',
			'default'				=> 'iso_list_default',
			'options_callback'		=> array('tl_iso_producttypes', 'getListTemplates'),
			'eval'					=> array('mandatory'=>true, 'tl_class'=>'w50'),
		),
		'reader_template' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['reader_template'],
			'inputType'				=> 'select',
			'default'				=> 'iso_reader_default',
			'options_callback'		=> array('tl_iso_producttypes', 'getReaderTemplates'),
			'eval'					=> array('mandatory'=>true, 'tl_class'=>'w50'),
		),
		'description' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['description'],
			'exclude'				=> true,
			'inputType'				=> 'textarea',
			'eval'					=> array('style'=>'height:80px', 'tl_class'=>'clr'),
		),
        'attributes' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['attributes'],
			'exclude'				=> true,
			'inputType'				=> 'attributeWizard',
			'default'				=> array('type', 'pages', 'alias', 'sku', 'name', 'teaser', 'description', 'price', 'tax_class', 'images', 'published'),
			'eval'					=> array('mandatory'=>true),
		),
		'variants' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['variants'],
			'exclude'				=> true,
			'inputType'				=> 'checkbox',
			'eval'					=> array('tl_class'=>'clr', 'submitOnChange'=>true),
		),
        'variant_attributes' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['variant_attributes'],
			'exclude'				=> true,
			'inputType'				=> 'attributeWizard',
			'eval'					=> array('variants'=>true, 'tl_class'=>'clr'),
		),
        'force_variant_options' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['force_variant_options'],
			'exclude'				=> true,
			'inputType'				=> 'checkbox',
			'eval'					=> array('tl_class'=>'clr'),
		),
		'downloads' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['downloads'],
			'exclude'				=> true,
			'inputType'				=> 'checkbox',
			'eval'					=> array(),
		),
	)
);


/**
 * tl_iso_producttypes class.
 *
 * @extends Backend
 */
class tl_iso_producttypes extends Backend
{

	/**
	 * Check permissions to edit table tl_iso_producttypes.
	 *
	 * @access public
	 * @return void
	 */
	public function checkPermission()
	{
		if ($this->Input->get('mod') != 'producttypes')
			return;

		$this->import('BackendUser', 'User');

		// Hide archived (sold and deleted) product types
		if ($this->User->isAdmin)
		{
			$arrProductTypes = $this->Database->execute("SELECT id FROM tl_iso_producttypes WHERE archive<2")->fetchEach('id');
		}
		else
		{
			$arrTypes = is_array($this->User->iso_product_types) ? $this->User->iso_product_types : array(0);
			$arrProductTypes = $this->Database->execute("SELECT id FROM tl_iso_producttypes WHERE id IN ('','" . implode("','", $arrTypes) . "') AND archive<2")->fetchEach('id');
		}

		// Set root IDs
		if (!count($arrProductTypes))
		{
			$arrProductTypes = array(0);
		}

		$GLOBALS['TL_DCA']['tl_iso_producttypes']['list']['sorting']['root'] = $arrProductTypes;

		// Check current action
		switch ($this->Input->get('act'))
		{
			case 'edit':
			case 'copy':
			case 'delete':
			case 'show':
				if (!in_array($this->Input->get('id'), $arrProductTypes))
				{
					$this->log('Not enough permissions to '.$this->Input->get('act').' product type ID "'.$this->Input->get('id').'"', 'tl_iso_producttypes checkPermission()', TL_ACCESS);
					$this->redirect($this->Environment->script.'?act=error');
				}
				break;

			case 'editAll':
			case 'copyAll':
			case 'deleteAll':
				$session = $this->Session->getData();
				$session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $arrProductTypes);
				$this->Session->setData($session);
				break;
		}
	}


	/**
	 * Return list templates as array
	 * @param object
	 * @return array
	 */
	public function getListTemplates(DataContainer $dc)
	{
		return $this->getTemplateGroup('iso_list_');
	}


	/**
	 * Return reader templates as array
	 * @param object
	 * @return array
	 */
	public function getReaderTemplates(DataContainer $dc)
	{
		return $this->getTemplateGroup('iso_reader_');
	}


	/**
	 * List template from all themes, show theme name
	 * Based on Controller::getTemplateGroup from Contao 2.9.3
	 */
	protected function getTemplateGroup($strPrefix, $intTheme=0)
	{
		$arrThemes = array();
		$arrTemplates = array();
		$arrFolders = array();

		// Add the templates root directory
		$arrFolders[] = TL_ROOT . '/templates';

		// Add theme templates folder
		$objTheme = $this->Database->execute("SELECT name, templates FROM tl_theme" . ($intPid>0 ? " WHERE id=$intPid" : ''));
		while( $objTheme->next() )
		{
			if ($objTheme->templates != '' && is_dir(TL_ROOT .'/'. $objTheme->templates))
			{
				$arrFolders[] = TL_ROOT .'/'. $objTheme->templates;
				$arrThemes[TL_ROOT .'/'. $objTheme->templates] = $objTheme->name;
			}
		}

		// Add the module templates folders if they exist
		foreach ($this->Config->getActiveModules() as $strModule)
		{
			$strFolder = TL_ROOT . '/system/modules/' . $strModule . '/templates';

			if (is_dir($strFolder))
			{
				$arrFolders[] = $strFolder;
			}
		}

		// Find all matching templates
		foreach ($arrFolders as $strFolder)
		{
			$arrFiles = preg_grep('/^' . preg_quote($strPrefix, '/') . '/i',  scan($strFolder));

			foreach ($arrFiles as $strTemplate)
			{
				$strName = basename($strTemplate);
				$strName = substr($strName, 0, strrpos($strName, '.'));
				$arrTemplates[$strName] = $strName . (isset($arrThemes[$strFolder]) ? ' ['.$arrThemes[$strFolder].']' : '');
			}
		}

		natcasesort($arrTemplates);
		return $arrTemplates;
	}
}

