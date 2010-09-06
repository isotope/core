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
 * @copyright  Winans Creative 2009, Intelligent Spark 2010, iserv.ch GmbH 2010
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/MultiTextWizard/html/js/multitext.js';


/**
 * Table tl_iso_products 
 */
$GLOBALS['TL_DCA']['tl_iso_products'] = array
(

	// Config
	'config' => array
	(
		'label'                       => &$GLOBALS['TL_LANG']['MOD']['iso_products'][0],
		'dataContainer'               => 'ProductData',
		'enableVersioning'			  => true,
		'closed'					  => true,
		'ctable'					  => array('tl_iso_downloads', 'tl_iso_product_categories', 'tl_iso_prices'),
		'ltable'					  => 'tl_iso_producttypes.languages',
		'lref'						  => 'type',
		'onload_callback'			  => array
		(
			array('tl_iso_products', 'checkPermission'),
			array('tl_iso_products', 'addBreadcrumb'),
			array('tl_iso_products', 'buildPaletteString'),
			array('tl_iso_products', 'generatePageAssociations'),
		),
	),
	
	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 5,
			'fields'				  => array('name'),
			'flag'					  => 1,
			'panelLayout'			  => 'filter;sort,search,limit',
			'icon'                    => 'system/modules/isotope/html/icon-products.gif',
			'paste_button_callback'   => array('tl_iso_products', 'pasteProduct'),
		),
		'label' => array
		(
			'fields'                  => array('name'),
			'format'                  => '%s',
			'label_callback'		  => array('tl_iso_products', 'getRowLabel'),
		),
		'global_operations' => array
		(
			'new_product' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_products']['new_product'],
				'href'                => 'act=create',
				'class'				  => 'header_new',
				'attributes'          => 'onclick="Backend.getScrollOffset();"',
			),
			'new_variant' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_products']['new_variant'],
				'href'                => 'act=paste&mode=create',
				'class'				  => 'header_new',
				'attributes'          => 'onclick="Backend.getScrollOffset();"',
			),
			'tools' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_products']['tools'],
				'href'                => '',
				'class'               => 'header_isotope_tools',
				'attributes'          => 'onclick="Backend.getScrollOffset();" style="display:none"',
			),
			'toggleNodes' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['toggleNodes'],
				'href'                => 'ptg=all',
				'class'               => 'header_toggle isotope-tools',
				'attributes'          => 'onclick="Backend.getScrollOffset();"',
			),
			'import' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_products']['import'],
				'href'                => 'key=import',
				'class'               => 'header_import_assets isotope-tools',
				'attributes'          => 'onclick="Backend.getScrollOffset();"',
			),
			'link'	=> array
			(
				'label'				  => &$GLOBALS['TL_LANG']['tl_iso_products']['link'],
				'href'				  => 'key=link',
				'class'				  => 'header_product_category_link isotope-tools',
				'attributes'		  => 'onclick="Backend.getScrollOffset();"',
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_products']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif',
			),
			'copy' => array
			(
				'label'				  => &$GLOBALS['TL_LANG']['tl_iso_products']['copy'],
				'href'                => 'act=paste&amp;mode=copy&amp;childs=1',
				'icon'                => 'copy.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();"',
				'button_callback'     => array('tl_iso_products', 'copyProduct')
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_products']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
			),
			'toggle' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_products']['toggle'],
				'icon'                => 'visible.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset(); return AjaxRequest.toggleVisibility(this, %s);"',
				'button_callback'     => array('tl_iso_products', 'toggleIcon')
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_products']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),
			'tools' => array
			(
				'label'				  => &$GLOBALS['TL_LANG']['tl_iso_products']['tools'],
				'icon'				  => 'system/modules/isotope/html/tools.png',
				'attributes'          => 'class="invisible isotope-contextmenu"',
			),
			'quick_edit' => array
			(
				'label'				  => &$GLOBALS['TL_LANG']['tl_iso_products']['quick_edit'],
				'href'				  => 'key=quick_edit',
				'icon'				  => 'system/modules/isotope/html/icon-quick_edit.png',
				'button_callback'	  => array('tl_iso_products', 'quickEditButton'),
				'attributes'          => 'class="isotope-tools"',
			),
			'generate' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_products']['generate'],
				'href'                => 'key=generate',
				'icon'				  => 'system/modules/isotope/html/icon-generate.png',
				'button_callback'	  => array('tl_iso_products', 'generateButton'),
				'attributes'          => 'class="isotope-tools"',
			),
			'related' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_products']['related'],
				'href'                => 'table=tl_iso_related_products',
				'icon'                => 'system/modules/isotope/html/icon-related.png',
				'button_callback'	  => array('tl_iso_products', 'relatedButton'),
				'attributes'          => 'class="isotope-tools"',
			),
			'downloads' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_products']['downloads'],
				'href'                => 'table=tl_iso_downloads',
				'icon'                => 'system/modules/isotope/html/attach.png',
				'button_callback'	  => array('tl_iso_products', 'downloadsButton'),
				'attributes'          => 'class="isotope-tools"',
			),
			'prices' => array
			(
				'label'				  => &$GLOBALS['TL_LANG']['tl_iso_products']['prices'],
				'href'				  => 'table=tl_iso_prices',
				'icon'				  => 'system/modules/isotope/html/prices.png',
				'button_callback'	  => array('tl_iso_products', 'pricesButton'),
				'attributes'          => 'class="isotope-tools"',				
			),
		),
	),
	
	// Palettes
	'palettes' => array
	(
		'__selector__'				=> array('type', 'stock_enabled'),
		'default'					=> '{general_legend},type',
	),
	
	// Subpalettes
	'subpalettes' => array
	(
		'stock_enabled'				=> 'stock_quantity,stock_oversell',
	),
	
	// Fields
	'fields' => array
	(
		'type' => array
		(
			'label'					=>  &$GLOBALS['TL_LANG']['tl_iso_products']['type'],
			'filter'				=> true,
			'inputType'				=> 'select',
			'options_callback'		=> array('tl_iso_products', 'getProductTypes'),
			'foreignKey'			=> (strlen($this->Input->get('table')) ? 'tl_iso_producttypes.name' : ''),
			'eval'					=> array('mandatory'=>true, 'submitOnChange'=>true),
			'attributes'			=> array('legend'=>'general_legend', 'fixed'=>true, 'inherit'=>true),
		),
		'pages' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['pages'],
			'filter'				=> true,
			'inputType'				=> 'pageTree',
			'foreignKey'			=> 'tl_page.title',
			'eval'					=> array('mandatory'=>false, 'multiple'=>true, 'fieldType'=>'checkbox'),
			'attributes'			=> array('legend'=>'general_legend', 'fixed'=>true, 'inherit'=>true),
			'save_callback'			=> array
			(
				array('tl_iso_products','saveProductCategories'),
			),
		),
		'inherit' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['inherit'],
			'inputType'				=> 'inheritCheckbox',
			'eval'					=> array('multiple'=>true),
		),
		'alias' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['alias'],
			'search'				=> true,
			'sorting'				=> true,
			'inputType'				=> 'text',
			'eval'					=> array('rgxp'=>'alnum', 'doNotCopy'=>true, 'spaceToUnderscore'=>true, 'maxlength'=>128, 'tl_class'=>'w50'),
			'attributes'			=> array('legend'=>'general_legend', 'fixed'=>true, 'inherit'=>true),
			'save_callback' => array
			(
				array('tl_iso_products', 'generateAlias'),
			)
		),
		'sku' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['sku'],
			'search'				=> true,
			'sorting'				=> true,
			'inputType'				=> 'text',
			'eval'					=> array('maxlength'=>128, 'tl_class'=>'w50'),
			'attributes'			=> array('mandatory'=>true, 'legend'=>'general_legend'),
		),
		'name' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['name'],
			'search'				=> true,
			'sorting'				=> true,
			'inputType'				=> 'text',
			'eval'					=> array('mandatory'=>true, 'tl_class'=>'clr long'),
			'attributes'			=> array('legend'=>'general_legend', 'multilingual'=>true, 'fixed'=>true, 'is_order_by_enabled'=>true),
		),
		'teaser' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['teaser'],
			'search'				=> true,
			'inputType'				=> 'textarea',
			'eval'					=> array('style'=>'height:80px'),
			'attributes'			=> array('legend'=>'general_legend', 'multilingual'=>true),
		),
		'description' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['description'],
			'search'				=> true,
			'inputType'				=> 'textarea',
			'eval'					=> array('mandatory'=>true, 'rte'=>'tinyMCE'),
			'attributes'			=> array('legend'=>'general_legend', 'multilingual'=>true, 'use_rich_text_editor'=>true),
		),
		'description_meta' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['description_meta'],
			'search'				=> true,
			'inputType'				=> 'textarea',
			'attributes'			=> array('legend'=>'meta_legend', 'multilingual'=>true, 'maxlength'=>200),
		),
		'keywords_meta' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['keywords_meta'],
			'search'				=> true,
			'inputType'				=> 'textarea',
			'attributes'			=> array('legend'=>'meta_legend', 'multilingual'=>true, 'maxlength'=>200),
		),
		'price' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['price'],
			'sorting'				=> true,
			'inputType'				=> 'text',
			'eval'					=> array('mandatory'=>true, 'maxlength'=>255, 'rgxp'=>'price', 'tl_class'=>'w50'),
			'attributes'			=> array('legend'=>'pricing_legend', 'is_order_by_enabled'=>true),
		),
		'prices' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['prices'],
			'inputType'				=> 'dcaWizard',
			'foreignTable'			=> 'tl_iso_prices',
			'tableColumns'			=> array('config_id', 'member_group', 'start', 'stop'),
		),
		'tax_class' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['tax_class'],
			'inputType'				=> 'select',
			'foreignKey'			=> 'tl_iso_tax_class.name',
			'attributes'			=> array('legend'=>'pricing_legend'),
			'eval'					=> array('includeBlankOption'=>true),
		),/*
		'max_order_quantity' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['max_order_quantity'],
			'inputType'				=> 'text',
			'eval'					=> array('rgxp'=>'digit', 'disabled'=>'disabled'),
			'attributes'			=> array('legend'=>'inventory_legend'),
		),
		'stock_enabled' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['stock_enabled'],
			'inputType'				=> 'checkbox',
			'eval'					=> array('submitOnChange'=>true, 'tl_class'=>'clr'),
			'attributes'			=> array('legend'=>'inventory_legend'),
		),
		'stock_quantity' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['stock_quantity'],
			'inputType'				=> 'text',
			'eval'					=> array('mandatory'=>true, 'rgxp'=>'digit', 'disabled'=>'disabled', 'tl_class'=>'w50'),
		),
		'stock_oversell' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['stock_oversell'],
			'inputType'				=> 'checkbox',
			'eval'					=> array('tl_class'=>'w50 m12'),
		),*/
		'shipping_weight' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['shipping_weight'],
			'inputType'				=> 'timePeriod',
			'default'				=> 'kg',
			'options'				=> array('mg', 'g', 'kg', 't', 'ct', 'oz', 'lb', 'st', 'grain'),
			'reference'				=> &$GLOBALS['TL_LANG']['WGT'],
			'eval'					=> array('rgxp'=>'digit', 'tl_class'=>'w50 wizard', 'helpwizard'=>true),
			'attributes'			=> array('legend'=>'shipping_legend'),
		),
		'shipping_exempt' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['shipping_exempt'],
			'inputType'				=> 'checkbox',
			'eval'					=> array('tl_class'=>'w50'),
			'attributes'			=> array('legend'=>'shipping_legend'),
		),
		'images' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['images'],
			'inputType'				=> 'mediaManager',
			'explanation'			=> 'mediaManager',
			'eval'					=> array('extensions'=>'jpeg,jpg,png,gif', 'helpwizard'=>true),
			'attributes'			=> array('legend'=>'media_legend', 'fixed'=>true),
		),
		'published' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['published'],
			'filter'				=> true,
			'inputType'				=> 'checkbox',
			'eval'					=> array('doNotCopy'=>true),
			'attributes'			=> array('legend'=>'publish_legend', 'fixed'=>true, 'variant_fixed'=>true),
		),
		'start' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['start'],
			'inputType'				=> 'text',
			'eval'					=> array('rgxp'=>'date', 'datepicker'=>$this->getDatePickerString(), 'tl_class'=>'w50 wizard'),
			'attributes'			=> array('legend'=>'publish_legend'),
		),
		'stop' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['stop'],
			'inputType'				=> 'text',
			'eval'					=> array('rgxp'=>'date', 'datepicker'=>$this->getDatePickerString(), 'tl_class'=>'w50 wizard'),
			'attributes'			=> array('legend'=>'publish_legend'),
		),
		'source' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['source'],
			'eval'					=> array('mandatory'=>true, 'required'=>true, 'fieldType'=>'radio'),
		),
		'variant_attributes' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['variant_attributes'],
			'inputType'				=> 'variantWizard',
			'options'				=> array(),
			'eval'					=> array('doNotSaveEmpty'=>true),
		),
	),
);


class tl_iso_products extends Backend
{

	public function __construct()
	{
		parent::__construct();

		$this->import('BackendUser', 'User');
		$this->import('Isotope');
	}


	/**
	 * Show/hide the downloads button
	 */
	public function downloadsButton($row, $href, $label, $title, $icon, $attributes)
	{
		$objType = $this->Database->execute("SELECT * FROM tl_iso_producttypes WHERE id={$row['type']}");

		if (!$objType->prices)
			return '';
			
		$objDownloads = $this->Database->prepare("SELECT COUNT(*) AS total FROM tl_iso_downloads WHERE pid=?")->execute($row['id']);
			
		return '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).' '.$objDownloads->total.'</a> ';
	}
	
	
	/**
	 * Show/hide the prices button
	 */
	public function pricesButton($row, $href, $label, $title, $icon, $attributes)
	{
		$objType = $this->Database->execute("SELECT * FROM tl_iso_producttypes WHERE id={$row['type']}");

		if (!$objType->prices)
			return '';
						
		return '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
	}
	
	
	/**
	 * Only list product types a user is allowed to see.
	 */
	public function checkPermission()
	{
		if (strlen($this->Input->get('act')) && $this->Input->get('mode') != 'create')
		{
			$GLOBALS['TL_DCA']['tl_iso_products']['config']['closed'] = false;
		}

		// Hide "add variant" button if no products with variants enabled exist
		if (!$this->Database->execute("SELECT * FROM tl_iso_products LEFT JOIN tl_iso_producttypes ON tl_iso_products.type=tl_iso_producttypes.id WHERE tl_iso_producttypes.variants='1'")->numRows)
		{
			unset($GLOBALS['TL_DCA']['tl_iso_products']['list']['global_operations']['new_variant']);
		}
		
		$this->import('BackendUser', 'User');
		
		// Hide archived (sold and deleted) products
		if ($this->User->isAdmin)
		{
			$arrProducts = $this->Database->execute("SELECT id FROM tl_iso_products WHERE archive<2")->fetchEach('id');
		}
		else
		{
			$arrTypes = is_array($this->User->iso_product_types) ? $this->User->iso_product_types : array(0);
			$arrProducts = $this->Database->execute("SELECT id FROM tl_iso_products WHERE type IN ('','" . implode("','", $arrTypes) . "') AND archive<2")->fetchEach('id');
		}
		
		if (!count($arrProducts))
		{
			$arrProducts = array(0);
		}
		
		$GLOBALS['TL_DCA']['tl_iso_products']['list']['sorting']['root'] = $arrProducts;
		
		if (strlen($this->Input->get('id')) && !in_array($this->Input->get('id'), $arrProducts))
		{
			$this->log('Cannot access product ID '.$this->Input->get('id'), 'tl_iso_products checkPermission()', TL_ACCESS);
			$this->redirect('typolight/main.php?act=error');
		}
	}
	
	
	/**
	 * Add the breadcrumb menu
	 */
	public function addBreadcrumb()
	{
		// Set a new node
		if (isset($_GET['node']))
		{
			$this->Session->set('tl_page_node', $this->Input->get('node'));
			$this->redirect(preg_replace('/&node=[^&]*/', '', $this->Environment->request));
		}

		$intNode = $this->Session->get('tl_page_node');

		if ($intNode < 1)
		{
			return;
		}

		$arrIds = array();
		$arrLinks = array();

		// Generate breadcrumb trail
		if ($intNode)
		{
			$this->loadDataContainer('tl_page');
			$tl_page = new tl_page();
			$intId = $intNode;

			do
			{
				$objPage = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")
								->limit(1)
								->execute($intId);

				if ($objPage->numRows < 1)
				{
					// Currently selected page does not exits
					if ($intId == $intNode)
					{
						$this->Session->set('tl_page_node', 0);
						return;
					}

					break;
				}

				$arrIds[] = $intId;

				// No link for the active page
				if ($objPage->id == $intNode)
				{
					$arrLinks[] = $tl_page->addIcon($objPage->row(), '', null, '', true) . ' ' . $objPage->title;
				}
				else
				{
					$arrLinks[] = $tl_page->addIcon($objPage->row(), '', null, '', true) . ' <a href="' . $this->addToUrl('node='.$objPage->id) . '">' . $objPage->title . '</a>';
				}

				// Do not show the mounted pages
				if (!$this->User->isAdmin && in_array($objPage->id, $this->User->pagemounts))
				{
					break;
				}

				$intId = $objPage->pid;
			}
			while ($intId > 0 && $objPage->type != 'root');
		}

		// Check whether the node is mounted
		if (!$this->User->isAdmin && !$this->User->hasAccess($arrIds, 'pagemounts'))
		{
			$this->Session->set('tl_page_node', 0);

			$this->log('Page ID '.$intNode.' was not mounted', 'tl_page addBreadcrumb', TL_ERROR);
			$this->redirect('typolight/main.php?act=error');
		}

		// Limit tree
		$arrNodes = array_merge(array($intNode), $this->getChildRecords($intNode, 'tl_page'));
		$objProducts = $this->Database->execute("SELECT pid FROM tl_iso_product_categories WHERE page_id IN (" . implode(',', $arrNodes) . ")");
		if ($objProducts->numRows)
		{
			$GLOBALS['TL_DCA']['tl_iso_products']['list']['sorting']['root'] = $objProducts->fetchEach('pid');
		}
		else
		{
			$berror = '

<ul id="tl_breadcrumb">
  <li>' . $GLOBALS['TL_LANG']['ERR']['breadcrumbEmpty'] . '</li>
</ul>';
		}

		// Add root link
		$arrLinks[] = '<img src="system/themes/' . $this->getTheme() . '/images/pagemounts.gif" width="18" height="18" alt="" /> <a href="' . $this->addToUrl('node=0') . '">' . $GLOBALS['TL_LANG']['MSC']['filterAll'] . '</a>';
		$arrLinks = array_reverse($arrLinks);

		// Insert breadcrumb menu
		$GLOBALS['TL_DCA']['tl_iso_products']['list']['sorting']['breadcrumb'] = '

<ul id="tl_breadcrumb">
  <li>' . implode(' &gt; </li><li>', $arrLinks) . '</li>
</ul>'.$berror;
	}
	
	
	/**
	 * List products in backend.
	 */
	public function getRowLabel($row, $label = '')
	{
		$arrImages = deserialize($row['images']);
		$thumbnail = '&nbsp;';
		
		if (is_array($arrImages) && count($arrImages))
		{
			foreach( $arrImages as $image )
			{
				$strImage = 'isotope/' . strtolower(substr($image['src'], 0, 1)) . '/' . $image['src'];
				
				if (!is_file(TL_ROOT . '/' . $strImage))
					continue;
					
				$thumbnail = sprintf('<img src="%s" alt="%s" align="left" />', $this->getImage($strImage, 34, 34, 'proportional'), $image['alt']);
				break;
			}
		}
		
		if ($row['pid'] > 0)
		{
			$strBuffer = '<div class="iso_product"><div class="thumbnail">'.$thumbnail.'</div><ul>';
			
			$objProductType = $this->Database->execute("SELECT * FROM tl_iso_producttypes WHERE id=".$row['type']);
			
			$fields = deserialize($objProductType->attributes, true);
			
			foreach( $fields as $i )
			{
				if ($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$i]['attributes']['add_to_product_variants'])
				{
					// Format value (based on DC_Table::show(), Contao 2.9.0)
					$value = deserialize($row[$i]);
				
					// Get field value
					if (strlen($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$i]['foreignKey']))
					{
						$temp = array();
						$chunks = explode('.', $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$i]['foreignKey']);
		
						foreach ((array) $value as $v)
						{
							$objKey = $this->Database->prepare("SELECT " . $chunks[1] . " AS value FROM " . $chunks[0] . " WHERE id=?")
													 ->limit(1)
													 ->execute($v);
		
							if ($objKey->numRows)
							{
								$temp[] = $objKey->value;
							}
						}
		
						$row[$i] = implode(', ', $temp);
					}
		
					elseif (is_array($value))
					{
						foreach ($value as $kk=>$vv)
						{
							if (is_array($vv))
							{
								$vals = array_values($vv);
								$value[$kk] = $vals[0].' ('.$vals[1].')';
							}
						}
		
						$row[$i] = implode(', ', $value);
					}
		
					elseif ($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$i]['eval']['rgxp'] == 'date')
					{
						$row[$i] = $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $value);
					}
		
					elseif ($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$i]['eval']['rgxp'] == 'time')
					{
						$row[$i] = $this->parseDate($GLOBALS['TL_CONFIG']['timeFormat'], $value);
					}
		
					elseif ($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$i]['eval']['rgxp'] == 'datim' || in_array($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$i]['flag'], array(5, 6, 7, 8, 9, 10)) || $i == 'tstamp')
					{
						$row[$i] = $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $value);
					}
		
					elseif ($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$i]['inputType'] == 'checkbox' && !$GLOBALS['TL_DCA']['tl_iso_products']['fields'][$i]['eval']['multiple'])
					{
						$row[$i] = strlen($value) ? $GLOBALS['TL_LANG']['MSC']['yes'] : $GLOBALS['TL_LANG']['MSC']['no'];
					}
		
					elseif ($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$i]['inputType'] == 'textarea' && ($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$i]['eval']['allowHtml'] || $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$i]['eval']['preserveTags']))
					{
						$row[$i] = specialchars($value);
					}
		
					elseif (is_array($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$i]['reference']))
					{
						$row[$i] = isset($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$i]['reference'][$row[$i]]) ? ((is_array($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$i]['reference'][$row[$i]])) ? $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$i]['reference'][$row[$i]][0] : $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$i]['reference'][$row[$i]]) : $row[$i];
					}
		
					// Label
					if (count($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$i]['label']))
					{
						$label = is_array($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$i]['label']) ? $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$i]['label'][0] : $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$i]['label'];
					}
		
					else
					{
						$label = is_array($GLOBALS['TL_LANG']['MSC'][$i]) ? $GLOBALS['TL_LANG']['MSC'][$i][0] : $GLOBALS['TL_LANG']['MSC'][$i];
					}
		
					if (!strlen($label))
					{
						$label = $i;
					}

					$strBuffer .= '<li><strong>' . $label . ':</strong> ' . $row[$i] . '</li>';
				}
			}
			
			return $strBuffer . '</ul></div>';
		}
		
		return '<div class="iso_product"><div class="thumbnail">'.$thumbnail.'</div><p>' . $row['name'] . (strlen($row['sku']) ? '<span style="color:#b3b3b3; padding-left:3px;">['.$row['sku'].']</span>' : '') . '</p><div>' . ($row['pid']==0 ? '<em>' . $this->getCategoryList(deserialize($row['pages'])) . '</em>' : '') . '</div></div> ';
	}
	
	
	/**
	 * Returns all allowed product types as array.
	 *
	 * @access public
	 * @param object DataContainer $dc
	 * @return array
	 */
	public function getProductTypes(DataContainer $dc)
	{
		$this->import('BackendUser', 'User');
		
		$arrTypes = $this->User->iso_product_types;
		if (!is_array($arrTypes) || !count($arrTypes))
		{
			$arrTypes = array(0);
		}
		
		$arrProductTypes = array();
		$objProductTypes = $this->Database->execute("SELECT id,name FROM tl_iso_producttypes" . ($this->User->isAdmin ? '' : (" WHERE id IN (".implode(',', $arrTypes).")")) . " ORDER BY name");

		while($objProductTypes->next())
		{
			$arrProductTypes[$objProductTypes->id] = $objProductTypes->name;
		}

		return $arrProductTypes;
	}
	
	
	/**
	 * Produce a list of categories for the backend listing
	 *
	 * @param mixed
	 * @return string
	 */
	private function getCategoryList($varValue)
	{
		$arrPages = array();
		
		if(!is_array($varValue) || !count($varValue))
		{
			return $GLOBALS['TL_LANG']['MSC']['noCategoriesAssociated'];
		}
				
		$arrCategories = array();
		
		foreach( $varValue as $intPage )
		{
			if (!$arrPages[$intPage])
			{
				$objPage = $this->getPageDetails($intPage);
				if(count($objPage->trail))
				{
					$arrPages[$intPage]['title'] = $objPage->title;
								
					$objPages = $this->Database->execute("SELECT * FROM tl_page WHERE id IN (" . implode(',', $objPage->trail) . ") ORDER BY id=" . implode(' DESC, id=', $objPage->trail) . " DESC");
				
								
					$arrHelp = array();
					while( $objPages->next() )
					{
						$arrHelp[] = $objPages->title;
					}
					
					$arrPages[$intPage]['help'] = implode(' » ', $arrHelp);
				}
			}
			
			
			$arrCategories[] = '<a class="tl_tip" longdesc="' . $arrPages[$intPage]['help'] . '" href="' . $this->addToUrl('table=tl_iso_product_categories&id='.$intPage) . '">' . $arrPages[$intPage]['title'] . '</a>';
		}
		
		return $GLOBALS['TL_LANG']['tl_iso_products']['pages'][0] . ': ' . implode(', ', $arrCategories);
	}
	
	
	/**
	 * Autogenerate a page alias if it has not been set yet
	 * @param mixed
	 * @param object
	 * @return string
	 */
	public function generateAlias($varValue, DataContainer $dc)
	{
		$autoAlias = false;

		// Generate alias if there is none
		if (!strlen($varValue))
		{
			$autoAlias = true;
			$varValue = standardize($this->Input->post('name'));
			
			if (!strlen($varValue))
			{
				$varValue = standardize($this->Input->post('sku'));
			}
			
			if (!strlen($varValue))
			{
				$varValue = strlen($dc->activeRecord->name) ? standardize($dc->activeRecord->name) : standardize($dc->activeRecord->sku);
			}
			
			if (!strlen($varValue))
			{
				$varValue = $dc->id;
			}
		}

		$objAlias = $this->Database->prepare("SELECT id FROM tl_iso_products WHERE id=? OR alias=?")
								   ->execute($dc->id, $varValue);

		// Check whether the page alias exists
		if ($objAlias->numRows > 1)
		{
			if (!$autoAlias)
			{
				throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
			}

			$varValue .= '.' . $dc->id;
		}

		return $varValue;
	}
	
	
	/**
	 * Save page ids to tl_iso_product_categories table. This allows to retrieve all products associated to a page.
	 */
	public function saveProductCategories($varValue, DataContainer $dc)
	{
		$arrIds = deserialize($varValue);
		
		if (is_array($arrIds) && count($arrIds))
		{
			$time = time();
			$this->Database->prepare("DELETE FROM tl_iso_product_categories WHERE pid=? AND page_id NOT IN (" . implode(',', $arrIds) . ")")->execute($dc->id);
			$objPages = $this->Database->prepare("SELECT page_id FROM tl_iso_product_categories WHERE pid=?")->execute($dc->id);
			$arrIds = array_diff($arrIds, $objPages->fetchEach('page_id'));
			
			foreach( $arrIds as $id )
			{
				$intSorting = $this->Database->prepare("SELECT sorting FROM tl_iso_product_categories WHERE page_id=? ORDER BY sorting DESC")->limit(1)->execute($id)->sorting;
				$intSorting += 128;
				$this->Database->prepare("INSERT INTO tl_iso_product_categories (pid,tstamp,page_id,sorting) VALUES (?,?,?,?)")->execute($dc->id, $time, $id, $intSorting);
			}
		}
		else
		{
			$this->Database->prepare("DELETE FROM tl_iso_product_categories WHERE pid=?")->execute($dc->id);
		}
	
		return $varValue;
	}
	
	/**
	 * Save page ids to tl_iso_product_categories table. This allows to retrieve all products associated to a page.
	 */
	public function linkProductsToCategories($dc)
	{
		if(!$this->User->isAdmin)
		{
			return '';
		}
		
		$objProducts = $this->Database->prepare("SELECT id, pages FROM tl_iso_products")
									  ->execute();
		
		if(!$objProducts->numRows)
		{
			return '';
		}
		
		while($objProducts->next())
		{
			$arrProducts[$objProducts->id] = deserialize($objProducts->pages);
		}
		
		foreach($arrProducts as $k=>$v)
		{
			
			if (is_array($v) && count($v))
			{
				
				$time = time();
				$this->Database->prepare("DELETE FROM tl_iso_product_categories WHERE pid=? AND page_id NOT IN (" . implode(',', $v) . ")")->execute($k);
				$objPages = $this->Database->prepare("SELECT page_id FROM tl_iso_product_categories WHERE pid=?")->execute($k);
				$arrIds = array_diff($v, $objPages->fetchEach('page_id'));
				
				foreach( $arrIds as $id )
				{
					$intSorting = $this->Database->prepare("SELECT sorting FROM tl_iso_product_categories WHERE page_id=? ORDER BY sorting DESC")->limit(1)->execute($id)->sorting;
					$intSorting += 128;
					$this->Database->prepare("INSERT INTO tl_iso_product_categories (pid,tstamp,page_id,sorting) VALUES (?,?,?,?)")->execute($k, $time, $id, $intSorting);
				}
			}
			else
			{
				$this->Database->prepare("DELETE FROM tl_iso_product_categories WHERE pid=?")->execute($k);
			}
		}
		
	}
	
	/** 
	 * Repair associations between products and categories
	 */
	public function generatePageAssociations()
	{
		if(!$this->Input->get('generateAssoc'))
		{
			return;
		}
		
		$arrCategoryData = array();	
		$arrData = array();
		$arrUpdates = array();
		
		$objCategoryData = $this->Database->prepare("SELECT * FROM tl_iso_product_categories")
										 ->execute();
	
		if(!$objCategoryData->numRows)
		{
			return;
		}
		
		$arrCategoryData = $objCategoryData->fetchAllAssoc();
		
		foreach($arrCategoryData as $row)
		{
			
			$arrData[$row['pid']][] = array('pid'=>$row['pid'], 'page_id'=>$row['page_id']);			
		}
				
		foreach($arrData as $row)
		{		
			$arrValues = array();
			
			$intPid = $row[0]['pid'];
								
			foreach($row as $data)
			{
				$arrValues[] = $data['page_id'];
			}
			
			$this->Database->execute("UPDATE tl_iso_products SET pages='" . serialize($arrValues) . "' WHERE id='" . $intPid . "'");
		}

		/*if(count($arrUpdates))
		{		
			
			implode(';', $arrUpdates));
		}*/
		
	}
	
	
	/**
	 * Generate all combination of product attributes
	 */
	public function generateVariants($dc)
	{
		$objProduct = $this->Database->prepare("SELECT id, pid, language, type, (SELECT attributes FROM tl_iso_producttypes WHERE id=tl_iso_products.type) AS attributes, (SELECT variant_attributes FROM tl_iso_producttypes WHERE id=tl_iso_products.type) AS variant_attributes FROM tl_iso_products WHERE id=?")->limit(1)->execute($dc->id);
		
		$doNotSubmit = false;
		$strBuffer = '';
		$arrOptions = array();
		$arrAttributes = deserialize($objProduct->attributes);
		
		if (is_array($arrAttributes) && count($arrAttributes))
		{
			foreach( $arrAttributes as $attribute )
			{
				if ($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute]['attributes']['add_to_product_variants'])
				{
					$GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute]['eval']['mandatory'] = true;					
					$GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute]['eval']['multiple'] = true;
	
					$objWidget = new CheckBox($this->prepareForWidget($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute], $attribute));						

					if ($this->Input->post('FORM_SUBMIT') == 'tl_product_generate')
					{
						$objWidget->validate();
						
						if ($objWidget->hasErrors())
						{
							$doNotSubmit = true;
						}
						else
						{
							$arrOptions[$attribute] = $objWidget->value;
						}
					}
					
					$strBuffer .= $objWidget->parse();
				}
			}
			
			if ($this->Input->post('FORM_SUBMIT') == 'tl_product_generate' && !$doNotSubmit)
			{
				$time = time();
				$arrCombinations = array();
				
				foreach( $arrOptions as $name => $options )
				{
					$arrTemp = $arrCombinations;
					$arrCombinations = array();
					
					foreach( $options as $option )
					{
						if (!count($arrTemp))
						{
							$arrCombinations[][$name] = $option;
							continue;
						}
						
						foreach( $arrTemp as $temp )
						{
							$temp[$name] = $option;
							$arrCombinations[] = $temp;
						}
					}
				}
				
				foreach( $arrCombinations as $combination )
				{
					$objVariant = $this->Database->prepare("SELECT * FROM tl_iso_products WHERE pid=? AND " . implode('=? AND ', array_keys($combination)) . "=?")
												 ->execute(array_merge(array($objProduct->id), $combination));

					if (!$objVariant->numRows)
					{
						$this->Database->prepare("INSERT INTO tl_iso_products (tstamp,pid,inherit,type," . implode(',', array_keys($combination)) . ") VALUES (?,?,?,?" . str_repeat(',?', count($combination)) . ")")
									   ->execute(array_merge(array($time, $objProduct->id, $objProduct->variant_attributes, $objProduct->type), $combination));
					}
				}
				
				$this->redirect(str_replace('&key=generate', '&key=quick_edit', $this->Environment->request));
			}
		}
		
		// Return form
		return '
<div id="tl_buttons">
<a href="'.ampersand(str_replace('&key=generate', '', $this->Environment->request)).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>

<h2 class="sub_headline">'.sprintf($GLOBALS['TL_LANG']['tl_iso_products']['generate'][1], $dc->id).'</h2>'.$this->getMessages().'

<form action="'.ampersand($this->Environment->request, true).'" id="tl_product_generate" class="tl_form" method="post">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="tl_product_generate" />

<div class="tl_tbox block">
' . $strBuffer . '
</div>

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
  <input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['tl_iso_products']['generate'][0]).'" />
</div>

</div>
</form>';
	}
	
	
	/**
	 * Quickly edit the most common product variant data
	 */
	public function quickEditVariants($dc)
	{
		$arrQuickEditFields = array('sku', 'price', 'shipping_weight', 'stock_quantity');

		$objProduct = $this->Database->prepare("SELECT id, pid, language, type, (SELECT attributes FROM tl_iso_producttypes WHERE id=tl_iso_products.type) AS attributes, (SELECT variant_attributes FROM tl_iso_producttypes WHERE id=tl_iso_products.type) AS variant_attributes FROM tl_iso_products WHERE id=?")->limit(1)->execute($dc->id);
		
		$arrFields = array();
		$arrAttributes = deserialize($objProduct->attributes);
		$arrVarAttributes = deserialize($objProduct->variant_attributes);
		
		if (is_array($arrAttributes) && count($arrAttributes))
		{
			foreach( $arrAttributes as $attribute )
			{
				if ($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute]['attributes']['add_to_product_variants'])
				{
					$arrFields[] = $attribute;
				}
			}
		}

		$objVariants = $this->Database->prepare("SELECT * FROM tl_iso_products WHERE pid=? AND language=''")->execute($dc->id);
		$strBuffer .= '<div id="tl_buttons">
<a href="'.ampersand(str_replace('&key=quick_edit', '', $this->Environment->request)).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>

<h2 class="sub_headline">'.sprintf($GLOBALS['TL_LANG']['tl_iso_products']['quick_edit'][1], $dc->id).'</h2>'.$this->getMessages().'

<form action="'.ampersand($this->Environment->request, true).'" id="tl_product_quick_edit" class="tl_form" method="post">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="tl_product_quick_edit" />

<div class="tl_tbox block">
<table width="100%" border="0" cellpadding="5" cellspacing="0" summary="">
<thead>
<th>' . $GLOBALS['TL_LANG']['tl_iso_products']['variantValuesLabel'] . '</th>';

		foreach($arrQuickEditFields as $field)
		{
			if(in_array($field, $arrVarAttributes))
			{
				$strBuffer .= '<th>'.$GLOBALS['TL_LANG']['tl_iso_products'][$field][0].'</th>';
			}
		}

$strBuffer .= '<th><img src="system/themes/default/images/published.gif" width="16" height="16" alt="' . $GLOBALS['TL_LANG']['tl_iso_products']['published'][0].'" /></th>
</thead>';		
		
		$arrFields = array_flip($arrFields);
		$globalDoNotSubmit = false;
				
		while($objVariants->next())
		{
			$arrWidgets = array();
			$doNotSubmit = false;
			$arrSet = array();
			
			$arrPublished[$objVariants->id] = $objVariants->published;
			
			foreach($arrQuickEditFields as $field)
			{
				if(in_array($field, $arrVarAttributes))
				{
					$strClass = $GLOBALS['BE_FFL'][$GLOBALS['TL_DCA']['tl_iso_products']['fields'][$field]['inputType']];
					$arrWidgets[$field] = new $strClass($this->prepareForWidget($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$field], $field.'[' . $objVariants->id .']', $objVariants->{$field}));
				}
			}

			foreach($arrWidgets as $key=>$objWidget)
			{
								
				switch($key)
				{
					case 'sku':
						$objWidget->class = 'tl_text_2';
						break;
						
					case 'shipping_weight':
						$objWidget->class = 'tl_text_trbl';
						break;
						
					default:
						$objWidget->class = 'tl_text_3';
						break;
				}
			
				if ($this->Input->post('FORM_SUBMIT') == 'tl_product_quick_edit')
				{
					$objWidget->validate();
					
					if ($objWidget->hasErrors())
					{						
						$doNotSubmit = true;
						$globalDoNotSubmit = true;
					}
					else
					{												
						$arrSet[$key] = $objWidget->value;
					}
				}
			}
			
			
			if($this->Input->post('FORM_SUBMIT') == 'tl_product_quick_edit' && !$doNotSubmit)
			{				
				$arrPublished = $this->Input->post('published');
							
				$arrSet['published'] = ($arrPublished[$objVariants->id] ? $arrPublished[$objVariants->id] : '');
				
				//!@todo remove this routine after the next stable release
				$arrSet['inherit'] = array_diff(deserialize($objVariants->inherit, true), $arrQuickEditFields);
				
				$this->Database->prepare("UPDATE tl_iso_products %s WHERE id=?")
							   ->set($arrSet)
							   ->execute($objVariants->id);
			}
			
			$strBuffer .= '
<tr>
	<td>'.implode(', ', array_intersect_key($objVariants->row(), $arrFields)).'</td>';
	foreach($arrQuickEditFields as $field)
	{
		if(in_array($field, $arrVarAttributes))
		{
			$strBuffer .= '<td>'.$arrWidgets[$field]->generate().'</td>';
		}
	}
	
	$strBuffer .= '<td><input type="checkbox" name="published['.$objVariants->id.']" value="1"'.($arrPublished[$objVariants->id] ? ' checked="checked"' : '').' class="tl_checkbox" /></td>
<tr>';
		
		}
		
		if ($this->Input->post('FORM_SUBMIT') == 'tl_product_quick_edit' && !$globalDoNotSubmit)
		{
			if (strlen($this->Input->post('saveNclose')))
			{
				$this->redirect(str_replace('&key=quick_edit', '', $this->Environment->request));
			}
			else
			{
				$this->reload();
			}
		}
		
		return $strBuffer . '
</table>
</div>

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
  <input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['save']).'" />
  <input type="submit" name="saveNclose" id="saveNclose" class="tl_submit" accesskey="c" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['saveNclose']).'" />
</div>

</div>
</form>';		
		
	}
	/**
	 * Import images and other media file for products
	 */
	public function importAssets($dc, $arrNewImages = array())
	{
		$objTree = new FileTree($this->prepareForWidget($GLOBALS['TL_DCA']['tl_iso_products']['fields']['source'], 'source', null, 'source', 'tl_iso_products'));
		
		$intCurrentBatch = 0;
		
		// Import assets
		if ($this->Input->post('FORM_SUBMIT') == 'tl_iso_products_import' && strlen($this->Input->post('source')))
		{
			$this->import('Files');
			
			//$intLimit = (integer)$this->Input->post('batch_size');
			//$intCurrentBatch = ($this->Input->get('current_batch') ? (integer)$this->Input->get('current_batch') : $intCurrentBatch);
			
			$strPath = $this->Input->post('source');
			$arrFiles = scan(TL_ROOT . '/' . $strPath);
			
			if (!count($arrFiles))
			{
				$_SESSION['TL_ERROR'][] = 'No files in this folder';
				$this->reload();
			}
			
			$arrDelete = array();
			$objProducts = $this->Database->prepare("SELECT * FROM tl_iso_products WHERE pid=0")										 
										  ->execute();
			
			while( $objProducts->next() )
			{			
				
				$arrImageNames  = array();
				$arrImages = deserialize($objProducts->images);
	
				if (!is_array($arrImages))
				{
					$arrImages = array();
				}
				else
				{
					foreach($arrImages as $row)
					{
						if($row['src'])
						{
							$arrImageNames[] = $row['src'];
						}
					}	
				}

				$strPattern = '@^(' . ($objProducts->alias ?  '|' . standardize($objProducts->alias) : '') . ($objProducts->sku ? '|' . $objProducts->sku : '') .($objProducts->sku ? '|' . standardize($objProducts->sku) : '') . (count($arrImageNames) ? '|' . implode('|', $arrImageNames) : '') . ')@i';
				
				$arrMatches = preg_grep($strPattern, $arrFiles);

				if (count($arrMatches))
				{
				
					$arrNewImages = array();
					
					foreach( $arrMatches as $file )
					{					
						if (is_dir(TL_ROOT . '/' . $strPath . '/' . $file))
						{
							$arrSubfiles = scan(TL_ROOT . '/' . $strPath . '/' . $file);
							if (count($arrSubfiles))
							{
								foreach( $arrSubfiles as $subfile )
								{
									if (is_file($strPath . '/' . $file . '/' . $subfile))
									{
										$objFile = new File($strPath . '/' . $file . '/' . $subfile);
									
										if ($objFile->isGdImage)
										{
											$arrNewImages[] = $strPath . '/' . $file . '/' . $subfile;
										}
									}
								}
							}
						}
						elseif (is_file(TL_ROOT . '/' . $strPath . '/' . $file))
						{
							$objFile = new File($strPath . '/' . $file);
							
							if ($objFile->isGdImage)
							{
								$arrNewImages[] = $strPath . '/' . $file;
							}
						}
					}
										
					if (count($arrNewImages))
					{
						foreach( $arrNewImages as $strFile )
						{
							$pathinfo = pathinfo(TL_ROOT . '/' . $strFile);
							
							// Make sure directory exists
							$this->Files->mkdir('isotope/' . substr($pathinfo['filename'], 0, 1) . '/');
							
							$strCacheName = $pathinfo['filename'] . '-' . substr(md5_file(TL_ROOT . '/' . $strFile), 0, 8) . '.' . $pathinfo['extension'];
							
							$this->Files->copy($strFile, 'isotope/' . substr($pathinfo['filename'], 0, 1) . '/' . $strCacheName);
							$arrImages[] = array('src'=>$strCacheName);
							$arrDelete[] = $strFile;
							
							$_SESSION['TL_CONFIRM'][] = sprintf('Imported file %s for product "%s"', $pathinfo['filename'] . '.' . $pathinfo['extension'], $objProducts->name);
							
						}
						
						$this->Database->prepare("UPDATE tl_iso_products SET images=? WHERE id=?")->execute(serialize($arrImages), $objProducts->id);

					}
				}
			}
						
			if (count($arrDelete))
			{
				$arrDelete = array_unique($arrDelete);
				
				foreach( $arrDelete as $file )
				{
					$this->Files->delete($file);
				}
			}
			
			$this->reload();
		}

		//$arrBatchValues = array(25,50,100,200);

		// Return form
		$strReturn = '
<div id="tl_buttons">
<a href="'.ampersand(str_replace('&key=import', '', $this->Environment->request)).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>

<h2 class="sub_headline">'.$GLOBALS['TL_LANG']['tl_iso_products']['import'][1].'</h2>'.$this->getMessages().'

<form action="'.ampersand($this->Environment->request, true).'" id="tl_iso_products_import" class="tl_form" method="post">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="tl_iso_products_import" />';
		/*$strReturn .= '<div class="tl_tbox block">
  <h3><label for="batch_size">'.$GLOBALS['TL_LANG']['tl_iso_products']['batch_size'][0].'</label></h3> <select name="batch_size"><option value=""'.($this->Input->post('batch_size') ? ' selected' : '').'>-</option>';*/
		/*  foreach($arrBatchValues as $value)
		  {
				$strReturn .= '<option value="'.$value.'"'.($this->Input->post('batch_size')==$value ? ' selected' : '').'>'.$value.'</option>';
		  }
		$strReturn .= '</select>';
		$strReturn .= (strlen($GLOBALS['TL_LANG']['tl_iso_products']['batch_size'][1]) ? '
		  <p class="tl_help">'.$GLOBALS['TL_LANG']['tl_iso_products']['batch_size'][1].'</p>' : '').'</div>';*/
		  
		return $strReturn . '<div class="tl_tbox block">
  <h3><label for="source">'.$GLOBALS['TL_LANG']['tl_iso_products']['source'][0].'</label> <a href="typolight/files.php" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['fileManager']) . '" onclick="Backend.getScrollOffset(); this.blur(); Backend.openWindow(this, 750, 500); return false;">' . $this->generateImage('filemanager.gif', $GLOBALS['TL_LANG']['MSC']['fileManager'], 'style="vertical-align:text-bottom;"') . '</a></h3>'.$objTree->generate().(strlen($GLOBALS['TL_LANG']['tl_iso_products']['source'][1]) ? '
  <p class="tl_help">'.$GLOBALS['TL_LANG']['tl_iso_products']['source'][1].'</p>' : '').'
</div>

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
<input type="submit" name="save" id="save" class="tl_submit" alt="import product assets" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['tl_iso_products']['import'][0]).'" />
</div>

</div>
</form>';
	}
	
	
	/**
	 * Hide "related" button for variants
	 */
	public function relatedButton($row, $href, $label, $title, $icon, $attributes)
	{
		if ($row['pid'] > 0)
			return '';
		
		return '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
	}
	
	
	/**
	 * Hide generate button for variants and product types without variant support
	 */
	public function generateButton($row, $href, $label, $title, $icon, $attributes)
	{
		if ($row['pid'] > 0)
			return '';
			
		$objType = $this->Database->prepare("SELECT * FROM tl_iso_producttypes WHERE id=?")
								  ->limit(1)
								  ->execute($row['type']);
								  
		if (!$objType->variants)
			return '';
		
		return '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
	}


	/**
	 * Hide generate button for variants and product types without variant support
	 */
	public function quickEditButton($row, $href, $label, $title, $icon, $attributes)
	{
		if ($row['pid'] > 0)
			return '';
			
		$objType = $this->Database->prepare("SELECT * FROM tl_iso_producttypes WHERE id=?")
								  ->limit(1)
								  ->execute($row['type']);
								  
		if (!$objType->variants)
			return '';
		
		return '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
	}
	

	/**
	 * Return the copy page button
	 */
	public function copyProduct($row, $href, $label, $title, $icon, $attributes, $table)
	{
		if ($row['pid'] == 0)
		{
			$href = 'act=copy';
		}

		return '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
	}



	/**
	 * Return the paste page button
	 */
	public function pasteProduct(DataContainer $dc, $row, $table, $cr, $arrClipboard=false)
	{
		$disablePI = false;

		// Disable all buttons if there is a circular reference
		if ($arrClipboard !== false && ($arrClipboard['mode'] == 'cut' && ($cr == 1 || $arrClipboard['id'] == $row['id']) || $arrClipboard['mode'] == 'cutAll' && ($cr == 1 || in_array($row['id'], $arrClipboard['id']))))
		{
			$disablePI = true;
		}

		// Disable buttons for variants
		if ($row['id'] == 0 || ($row['id'] > 0 && $row['pid'] > 0))
		{
			return '';
		}
		
		// Disable "paste into" button for products without variant data
		elseif ($row['id'] > 0)
		{
			$objType = $this->Database->prepare("SELECT * FROM tl_iso_producttypes WHERE id=?")->execute($row['type']);
			
			if (!$objType->variants)
			{
				$disablePI = true;
			}
		}

		// Return the button
		$imagePasteInto = $this->generateImage('pasteinto.gif', sprintf($GLOBALS['TL_LANG'][$table]['pasteinto'][1], $row['id']), 'class="blink"');

		return ($disablePI ? $this->generateImage('pasteinto_.gif', '', 'class="blink"').' ' : '<a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=2&amp;pid='.$row['id'].(!is_array($arrClipboard['id']) ? '&amp;id='.$arrClipboard['id'] : '')).'" title="'.specialchars(sprintf($GLOBALS['TL_LANG'][$table]['pasteinto'][1], $row['id'])).'" onclick="Backend.getScrollOffset();">'.$imagePasteInto.'</a> ');
	}
	
	
	/**
	 * Return the "toggle visibility" button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
	{
		if (strlen($this->Input->get('tid')))
		{
			$this->toggleVisibility($this->Input->get('tid'), ($this->Input->get('state') == 1));
			$this->redirect($this->getReferer());
		}

		// Check permissions AFTER checking the tid, so hacking attempts are logged
		if (!$this->User->isAdmin && !$this->User->hasAccess('tl_iso_products::published', 'alexf'))
		{
			return '';
		}

		$href .= '&amp;tid='.$row['id'].'&amp;state='.($row['published'] ? '' : 1);

		if (!$row['published'])
		{
			$icon = 'invisible.gif';
		}		

		$objProduct = $this->Database->prepare("SELECT * FROM tl_iso_products WHERE id=?")
									 ->limit(1)
									 ->execute($row['id']);

		return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
	}
	
	
	/**
	 * Disable/enable a user group
	 * @param integer
	 * @param boolean
	 */
	public function toggleVisibility($intId, $blnVisible)
	{
		// Check permissions to edit
		$this->Input->setGet('id', $intId);
		$this->Input->setGet('act', 'toggle');
		$this->checkPermission();

		// Check permissions to publish
		if (!$this->User->isAdmin && !$this->User->hasAccess('tl_iso_products::published', 'alexf'))
		{
			$this->log('Not enough permissions to publish/unpublish product ID "'.$intId.'"', 'tl_iso_products toggleVisibility', TL_ERROR);
			$this->redirect('typolight/main.php?act=error');
		}

		$this->createInitialVersion('tl_iso_products', $intId);
	
		// Trigger the save_callback
		if (is_array($GLOBALS['TL_DCA']['tl_iso_products']['fields']['published']['save_callback']))
		{
			foreach ($GLOBALS['TL_DCA']['tl_iso_products']['fields']['published']['save_callback'] as $callback)
			{
				$this->import($callback[0]);
				$blnVisible = $this->$callback[0]->$callback[1]($blnVisible, $this);
			}
		}

		// Update the database
		$this->Database->prepare("UPDATE tl_iso_products SET published='" . ($blnVisible ? 1 : '') . "' WHERE id=?")
					   ->execute($intId);

		$this->createNewVersion('tl_iso_products', $intId);
	}
	
	
	/**
	 * Build palette for the current product type / variant
	 */
	public function buildPaletteString($dc)
	{
		if (!strlen($this->Input->get('act')) && !strlen($this->Input->get('key')))
			return;
			
		// Set default product type
		$GLOBALS['TL_DCA']['tl_iso_products']['fields']['type']['default'] = $this->Database->execute("SELECT id FROM tl_iso_producttypes ORDER BY fallback DESC, name")->id;
		
		// Load the current product
		$objProduct = $this->Database->prepare("SELECT id, pid, language, type, (SELECT attributes FROM tl_iso_producttypes WHERE id=tl_iso_products.type) AS attributes, (SELECT variant_attributes FROM tl_iso_producttypes WHERE id=tl_iso_products.type) AS variant_attributes FROM tl_iso_products WHERE id=?")->limit(1)->execute($dc->id);
			
		if ($objProduct->pid > 0)
		{
			$objParent = $this->Database->prepare("SELECT * FROM tl_iso_products WHERE id=?")->limit(1)->execute($objProduct->pid);
			
			if ($objProduct->type != $objParent->type)
			{
				$this->Database->prepare("UPDATE tl_iso_products p1 SET type=(SELECT type FROM (SELECT * FROM tl_iso_products) AS p2 WHERE p1.pid=p2.id) WHERE p1.id=?")->execute($this->Input->get('id'));
				$this->reload();
			}
		}
		
		// Enable advanced prices
		if ($objProduct->prices)
		{
			$GLOBALS['TL_DCA']['tl_iso_products']['fields']['prices']['attributes'] = $GLOBALS['TL_DCA']['tl_iso_products']['fields']['price']['attributes'];
			$GLOBALS['TL_DCA']['tl_iso_products']['fields']['price'] = $GLOBALS['TL_DCA']['tl_iso_products']['fields']['prices'];
		}
		
		$arrInherit = array();
		$arrPalette = array();
		
		// Variant
		if ($objProduct->pid > 0)
		{
			$arrAttributes = deserialize($objProduct->attributes);
			$arrPalette['variant_legend'][] = 'variant_attributes,inherit';
			
			if (is_array($arrAttributes) && count($arrAttributes))
			{
				foreach( $arrAttributes as $attribute )
				{
					if ($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute]['attributes']['add_to_product_variants'])
					{
						$arrFields[] = $attribute;
						$GLOBALS['TL_DCA']['tl_iso_products']['fields']['variant_attributes']['options'][] = $attribute;
					}
				}
			}
			
			$arrFields = deserialize($objProduct->variant_attributes, true);
		}
		else
		{
			$arrFields = deserialize($objProduct->attributes, true);
		}
		
		if (is_array($arrFields) && count($arrFields))
		{
			foreach( $arrFields as $field )
			{
				// Field is not an attribute
				if (!is_array($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$field]) || !strlen($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$field]['attributes']['legend']))
					continue;
				
				// Do not show variant options
				if ($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$field]['attributes']['add_to_product_variants'])
					continue;
					
				// Field cannot be edited in variant
				if ($objProduct->pid > 0 && $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$field]['attributes']['inherit'])
					continue;

				$arrPalette[$GLOBALS['TL_DCA']['tl_iso_products']['fields'][$field]['attributes']['legend']][] = $field;
				
				if (!in_array($field, array('sku', 'price', 'shipping_weight', 'stock_quantity', 'published')))
				{
					$arrInherit[$field] = strlen($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$field]['label'][0]) ? $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$field]['label'][0] : $field;
				}
			}
		}
		
		// Build
		$arrLegends = array();
		foreach($arrPalette as $legend=>$fields)
		{
			$arrLegends[] = '{' . $legend . '},' . implode(',', $fields);
		}
		
		// Set inherit options
		$GLOBALS['TL_DCA']['tl_iso_products']['fields']['inherit']['options'] = $arrInherit;

		// Add palettes
		$GLOBALS['TL_DCA']['tl_iso_products']['palettes'][$objProduct->type] = implode(';', $arrLegends);
	}
	
	
	/**
	 * Initialize the tl_iso_products DCA
	 */	
	public function loadProductsDCA()
	{
		$objAttributes = $this->Database->execute("SELECT * FROM tl_iso_attributes");
		
		
		// add DCA for form fields
		while ( $objAttributes->next() )
		{
			$arrData = array
			(
				'label'				=> array($objAttributes->name, $objAttributes->description),
				'inputType'			=> ((TL_MODE == 'BE' && strlen($GLOBALS['ISO_ATTR'][$objAttributes->type]['backend'])) ? $GLOBALS['ISO_ATTR'][$objAttributes->type]['backend'] : ((TL_MODE == 'FE' && strlen($GLOBALS['ISO_ATTR'][$objAttributes->type]['frontend'])) ? $GLOBALS['ISO_ATTR'][$objAttributes->type]['frontend'] : $objAttributes->type)),
				'attributes'		=> $objAttributes->row(),
				'save_callback'		=> array
				(
					array('tl_iso_products','saveField'),
				),
			);
			
			if ($objAttributes->is_be_filterable) $arrData['filter'] = true;
			if ($objAttributes->is_be_searchable) $arrData['search'] = true;
			if ($objAttributes->is_required || $objAttributes->add_to_product_variants) $arrData['eval']['mandatory'] = true;
			if ($objAttributes->rgxp) $arrData['eval']['rgxp'] = $objAttributes->rgxp;
			if ($objAttributes->multiple) $arrData['eval']['multiple'] = $objAttributes->multiple;
			
			// check for options lookup 
			switch ($objAttributes->type)
			{
				case 'datetime':
					$arrData['eval']['rgxp'] = 'date';
					$arrData['eval']['datepicker'] = $this->getDatePickerString();
					break;
					
				case 'text':
					$arrData['eval']['tl_class'] = 'long';
					break;
			
				case 'textarea':
					if($objAttributes->use_rich_text_editor)
					{
						$arrData['eval']['rte'] = 'tinyMCE';
					}
					break;

				case 'file':
				case 'media':
					$arrData['eval']['cols'] = 4;
					//if($objAttributes->show_files) $arrData['eval']['files'] = true;
					//$arrData['eval']['fieldType'] = 'radio';
					break;
					
				default:
					$arrData['eval']['multiple'] = $objAttributes->type == 'options' ? false : $arrData['eval']['multiple'];
					if ($objAttributes->use_alternate_source && strlen($objAttributes->list_source_table) > 0 && strlen($objAttributes->list_source_field) > 0)
					{
						$arrData['foreignKey'] = $objAttributes->list_source_table . '.' . $objAttributes->list_source_field;
					}
					else
					{
						$arrData['options'] = array();
						$arrOptions = deserialize($objAttributes->option_list);
						
						if (is_array($arrOptions) && count($arrOptions))
						{
							$strGroup = '';
							foreach ($arrOptions as $option)
							{
								if (!strlen($option['value']))
								{
									$arrData['eval']['includeBlankOption'] = true;
									$arrData['eval']['blankOptionLabel'] = $option['label'];
									continue;
								}
								elseif ($option['group'])
								{
									$strGroup = $option['value'];
									continue;
								}
								
								if (strlen($strGroup))
								{
									$arrData['options'][$strGroup][$option['value']] = $option['label'];
								}
								else
								{
									$arrData['options'][$option['value']] = $option['label'];
								}
							}
						}
					}
					break;
			}
			
			if (is_array($GLOBALS['ISO_ATTR'][$objAttributes->type]['callback']) && count($GLOBALS['ISO_ATTR'][$objAttributes->type]['callback']))
			{
				foreach( $GLOBALS['ISO_ATTR'][$objAttributes->type]['callback'] as $callback )
				{
					$this->import($callback[0]);
					$arrData = $this->{$callback[0]}->{$callback[1]}($objAttributes->field_name, $arrData);
				}
			}
			
			$GLOBALS['TL_DCA']['tl_iso_products']['fields'][$objAttributes->field_name] = $arrData;
		}				
	}
	
	
	public function saveField($varValue, DataContainer $dc)
	{
		$objAttribute = $this->Database->prepare("SELECT * FROM tl_iso_attributes WHERE field_name=?")
									   ->limit(1)
									   ->execute($dc->field);
		
		if($objAttribute->numRows < 1)
		{
			throw new Exception('Not a valid record id!');
		}
		
		if($objAttribute->is_filterable)
		{
			$this->saveFilterValuesToCategories($varValue, $dc);
		}
		
		return $varValue;
	}
	
		
	/**
	 * Wrapper for the Product-Filter Collection associative table logic.  Grabs all necessary values in order to update the PFC table.
	 *
	 * @param string
	 * @param object
	 * @return string
	 */
	public function saveFilterValuesToCategories($varValue, DataContainer $dc, $id=0)
	{		
		if(is_null($varValue) || (is_int($varValue) && $varValue == 0))
		{
			return $varValue;
		}
		
		//For import needs, this is an override of the current record ID because when importing we're
		//not utlizing the DataContainer.  We should separate these functions with an intermediary function so that this logic
		//which is repeated across various other functions can be fed just an integer value instead of the more specific
		//DataContainer and its corresponding values.	
		if($id!=0)
		{
			$intId = $id;
		}
		else
		{
			$intId = $dc->id;
		}
						
		//Get the current attribute set		
		$objAttributeID = $this->Database->prepare("SELECT id FROM tl_iso_attributes WHERE field_name=?")
										 ->limit(1)
										 ->execute($dc->field);
		
		if($objAttributeID->numRows < 1)
		{
			return $varValue;
		}
		
		$attributeID = $objAttributeID->id;
		
		
		//Gather all records pertaining to the current attribute set in the aggregate table
		$objAllPageInfo = $this->Database->prepare("SELECT pid, value_collection FROM tl_filter_values_to_categories WHERE attribute_id=?")->execute($attributeID);
		
		if($objAllPageInfo->numRows > 0)
		{
			//Contains pid which is the reference to a given page, and attribute_id which is the reference to a given filter.
			$arrAllPageInfo = $objAllPageInfo->fetchAllAssoc();
		}
			
		//Get the value submitted for this particular attribute
		$objRecordValues = $this->Database->prepare("SELECT pages, " . $dc->field . " FROM tl_iso_products WHERE id=?")
													->limit(1)
													->execute($dc->id);
		if($objRecordValues->numRows < 1)
		{
			return $varValue;
		}
		
		$arrNewPageList = deserialize($objRecordValues->pages);
		
		if(is_string($arrNewPageList))
		{
			$arrNewPageList = array();
		}
				
		$this->updateFilterValuesToCategories($arrNewPageList, $arrAllPageInfo, $dc, $attributeID, $varValue);
	
		return $varValue;
	}
	
	
	
	/**
	 * updatePFCAggregate - Update our aggregate reference table which is used to build collections of products out of multiple attribute sets. This logic maintains the records by page of associated products and storeTables.
	 *
	 * @param variant
	 * @param object
	 * @param string
	 *
	 */
	private function updateFilterValuesToCategories($arrPageList, $arrAllPageInfo, DataContainer $dc, $attributeID, $varCurrValue)
	{		
		if(sizeof($arrPageList) < 1)
		{
			$arrPageList[] = 0;
		}
		
		if(empty($varCurrValue) || (is_int($varCurrValue) && $varCurrValue==0))
		{
			return;
		}
		
		$arrCurrValues[] = $varCurrValue;
		
		//Check Existing records first to avoid duplicate entries
		$objPFCInfo = $this->Database->prepare("SELECT id, pid, attribute_id, value_collection FROM tl_filter_values_to_categories WHERE pid IN (" . join(",", $arrPageList) . ") AND attribute_id=?")
									->execute($attributeID);
		
		if($objPFCInfo->numRows < 1)
		{
			
			// If there is no existing PFC record, then we just insert. Insert into table the association
			foreach($arrPageList as $intPageNum)
			{				
				$arrSet = array();
				
				$arrSet = array(
					'pid'					=> $intPageNum,
					'attribute_id'			=> $attributeID,
					'value_collection'		=> $arrCurrValues,
				);
				
				$this->Database->prepare("INSERT INTO tl_filter_values_to_categories %s")->set($arrSet)->execute();
			}
			
			return;
		}
		
		
		$arrPFCInfo = $objPFCInfo->fetchAllAssoc();	//Existing records are stored in an array
		
		$arrProducts = array();
		
		$arrPIDs = array();
		
		foreach($arrPFCInfo as $row)	//PIDs that already exist in the tl_filter_values_to_categories table
		{
			$arrPIDs[] = $row['pid'];
		}
		
		
		// For each existing page that DID in the past have this product ID associated with it, but NOW the submitted list does not include that page id, remove it
		foreach($arrAllPageInfo as $page)
		{
			$arrExistingValues = array();
			
			//Get the product ID collection of the current existing page
			$arrExistingValues = deserialize($page['value_collection']);
			
			//If the current existing page id does not exist in the list of pages collected from the form submit, then 
			//remove the product id from the page in question.			
			
			//If the product id exists in the product list for this page, which is not part of the product page list now...  Remove from the product_ids collection and update.
						
				/** TO DO - REWRITE & HANDLE MULITPLE FILTER VALUES IF ATTRIBUTE DOES MULTIPLE **/
					
				if(in_array($varCurrValue, $arrExistingValues))		//Does this need to be more strict - that is, bound to a particular pid when comparing?
				{
									
					$key = array_search($varCurrValue, $arrExistingValues); //get the corresponding key.
										
					//If we find that the product id submitted does, in fact exist in the existing product collection for this page, then we remove it.
				
					//Do any other products in this category share the filter value?  If not then we can safely remove it
					$objProductsAssociatedWithFilterValue = $this->Database->prepare("SELECT id, pages FROM tl_iso_products WHERE " . $dc->field . "=?")->execute($varCurrValue);
					
											
					if($objProductsAssociatedWithFilterValue->numRows < 1)	//if there are no occurrences of this filter value in any product, then ok.
					{
						unset($arrExistingValues[$key]);
					}
					else
					{
						$arrOtherProductsPages = $objProductsAssociatedWithFilterValue->fetchEach('pages');	
													
						$blnPreserveFilterValue = false;		//reset every row.  if we end up false at the end we need to unset.
						
						foreach($arrOtherProductsPages as $pageRow)
						{	
							$rowInfo = deserialize($pageRow, true);
							
							foreach($arrPageList as $currPage)
							{				
								if(in_array($currPage, $rowInfo))
								{
									$blnPreserveFilterValue = true;
									break;
								}
							}
						}
						
						if(!$blnPreserveFilterValue) //if this filter value is used by any other product in any of the categories associated
						{	
							//with the given product, then we cannot remove the filter value from the record.							
							unset($arrExistingValues[$key]);
						}
					}						
					
					if(is_array($arrExistingValues) && sizeof($arrExistingValues)>0)
					{
						 $this->Database->prepare("UPDATE tl_filter_values_to_categories SET value_collection=? WHERE pid=? AND attribute_id=?")
								   		->execute(serialize($arrExistingValues), $page['pid'], $attributeID);
					}
				}

			
			//For each page record already in the table, we grab the product id list and modify it to include this product ID if it isn't existing in the product ID collection.
			
			foreach($arrPFCInfo as $page)
			{
				//Each page record we start with a fresh products array to update the record.
				$arrExistingValues = array();
				
				$arrExistingPages[] = $page['pid'];
				// Since these are serialized, we have to deserialize them before we can do any work on the record.
				$arrExistingValues = deserialize($page['value_collection']);
									
				foreach($arrPageList as $pageToBeUpdated)
				{
					if((int)$pageToBeUpdated==$page['pid'])	//If this page 
					{
						//If the product ID doesn't not already have an association to the current page, then add it to the list of product IDs for that page.
						if(!in_array($varCurrValue, $arrExistingValues))
						{
							$arrExistingValues[] = $varCurrValue;	//add the product id in.
						}
					}
									
					// Update existing association
					$this->Database->prepare("UPDATE tl_filter_values_to_categories SET value_collection=? WHERE pid=? AND attribute_id=?")
								   ->execute(serialize($arrExistingValues), $page['pid'], $attributeID);
				}			
			}
		
		
		}
		
		//New Pages to add that aren't in the current collection
		foreach($arrPageList as $intPageNum)
		{	
			if(!in_array((int)$intPageNum, $arrExistingPages))
			{
				
				$arrSet = array();
				$arrValues = array();
				
				$arrValues[] = $varCurrValue;
				
				$arrSet = array(
					'value_collection'		=> serialize($arrValues),
					'pid'					=> $intPageNum,
					'attribute_id'			=> $attributeID
				);
				
				$this->Database->prepare("INSERT INTO tl_filter_values_to_categories %s")->set($arrSet)->execute();
			}
		}
	}
}

