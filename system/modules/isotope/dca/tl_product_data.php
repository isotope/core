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


/**
 * Table tl_product_data 
 */
$GLOBALS['TL_DCA']['tl_product_data'] = array
(

	// Config
	'config' => array
	(
		'label'                       => &$GLOBALS['TL_LANG']['MOD']['product_manager'][0],
		'dataContainer'               => 'ProductData',
		'enableVersioning'			  => true,
		'closed'					  => true,
		'ctable'					  => array('tl_product_downloads', 'tl_product_categories'),
		'ltable'					  => 'tl_product_types.languages',
		'lref'						  => 'type',
		'onload_callback'			  => array
		(
			array('tl_product_data', 'checkPermission'),
			array('tl_product_data', 'addBreadcrumb'),
			array('tl_product_data', 'generatePageAssociations')
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
			'paste_button_callback'   => array('tl_product_data', 'pasteProduct'),
		),
		'label' => array
		(
			'fields'                  => array('name'),
			'format'                  => '%s',
			'label_callback'		  => array('tl_product_data', 'getRowLabel'),
		),
		'global_operations' => array
		(
			'new_product' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_data']['new_product'],
				'href'                => 'act=create',
				'class'				  => 'header_new',
				'attributes'          => 'onclick="Backend.getScrollOffset();"',
			),
			'new_variant' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_data']['new_variant'],
				'href'                => 'act=paste&mode=create',
				'class'				  => 'header_new',
				'attributes'          => 'onclick="Backend.getScrollOffset();"',
			),
			'tools' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_data']['tools'],
				'href'                => '',
				'class'               => 'header_isotope_tools',
				'attributes'          => 'onclick="Backend.getScrollOffset();" style="display:none"'
			),
			'import' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_data']['import'],
				'href'                => 'key=import',
				'class'               => 'header_import_assets isotope-tools',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
			'link'	=> array
			(
				'label'				  => &$GLOBALS['TL_LANG']['tl_product_data']['link'],
				'href'				  => 'key=link',
				'class'				  => 'header_product_category_link isotope-tools',
				'attributes'		  => 'onclick="Backend.getScrollOffset();"'
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_data']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif',
				'button_callback'     => array('tl_product_data', 'editProduct'),
				'attributes'          => 'class="contextmenu"'
			),
			'quick_edit' => array
			(
				'label'				  => &$GLOBALS['TL_LANG']['tl_product_data']['quick_edit'],
				'href'				  => 'key=quick_edit',
				'icon'				  => 'system/modules/isotope/html/icon-quick_edit.png',
				'button_callback'	  => array('tl_product_data', 'quickEditButton'),
				'attributes'          => 'class="edit-header"'
			),
			'copy' => array
			(
				'label'				  => &$GLOBALS['TL_LANG']['tl_product_data']['copy'],
				'href'                => 'act=paste&amp;mode=copy&amp;childs=1',
				'icon'                => 'copy.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();"',
				'button_callback'     => array('tl_product_data', 'copyProduct')
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_data']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
				'button_callback'     => array('tl_product_data', 'deleteProduct')
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_data']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),
			'generate' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_data']['generate'],
				'href'                => 'key=generate',
				'icon'				  => 'system/modules/isotope/html/icon-generate.png',
				'button_callback'	  => array('tl_product_data', 'generateButton'),
			),
			'related' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_data']['related'],
				'href'                => 'table=tl_related_products',
				'icon'                => 'system/modules/isotope/html/icon-related.png',
				'button_callback'	  => array('tl_product_data', 'relatedButton'),
			),
			'downloads' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_data']['downloads'],
				'href'                => 'table=tl_product_downloads',
				'icon'                => 'system/modules/isotope/html/attach.png',
				'button_callback'	  => array('tl_product_data', 'downloadsButton'),
			),
		),
	),
	
	// Palettes
	'palettes' => array
	(
		'__selector__'				=> array('type', 'stock_enabled'),
		'default'					=> '{general_legend},type,alias',
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
			'label'					=>  &$GLOBALS['TL_LANG']['tl_product_data']['type'],
			'filter'				=> true,
			'inputType'				=> 'select',
			'options_callback'		=> array('tl_product_data', 'getProductTypes'),
			'foreignKey'			=> (strlen($this->Input->get('table')) ? 'tl_product_types.name' : ''),
			'eval'					=> array('mandatory'=>true, 'includeBlankOption'=>true, 'submitOnChange'=>true),
			'attributes'			=> array('legend'=>'general_legend', 'fixed'=>true, 'inherit'=>true),
		),
		'pages' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['pages'],
			'filter'				=> true,
			'inputType'				=> 'pageTree',
			'foreignKey'			=> 'tl_page.title',
			'eval'					=> array('mandatory'=>false, 'multiple'=>true, 'fieldType'=>'checkbox'),
			'attributes'			=> array('legend'=>'general_legend', 'fixed'=>true, 'inherit'=>true),
			'save_callback'			=> array
			(
				array('tl_product_data','saveProductCategories'),
			),
		),
		'inherit' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['inherit'],
			'inputType'				=> 'inheritCheckbox',
			'eval'					=> array('multiple'=>true),
		),
		'alias' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['alias'],
			'search'				=> true,
			'sorting'				=> true,
			'inputType'				=> 'text',
			'eval'					=> array('rgxp'=>'alnum', 'doNotCopy'=>true, 'spaceToUnderscore'=>true, 'maxlength'=>128, 'tl_class'=>'w50'),
			'attributes'			=> array('legend'=>'general_legend', 'fixed'=>true, 'inherit'=>true),
			'save_callback' => array
			(
				array('tl_product_data', 'generateAlias'),
			)
		),
		'sku' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['sku'],
			'search'				=> true,
			'sorting'				=> true,
			'inputType'				=> 'text',
			'eval'					=> array('maxlength'=>128, 'tl_class'=>'w50'),
			'attributes'			=> array('mandatory'=>true, 'legend'=>'general_legend'),
		),
		'name' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['name'],
			'search'				=> true,
			'sorting'				=> true,
			'inputType'				=> 'text',
			'eval'					=> array('mandatory'=>true, 'tl_class'=>'clr long'),
			'attributes'			=> array('legend'=>'general_legend', 'multilingual'=>true, 'fixed'=>true, 'is_order_by_enabled'=>true),
		),
		'teaser' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['teaser'],
			'search'				=> true,
			'inputType'				=> 'textarea',
			'eval'					=> array('style'=>'height:80px'),
			'attributes'			=> array('legend'=>'general_legend', 'multilingual'=>true),
		),
		'description' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['description'],
			'search'				=> true,
			'inputType'				=> 'textarea',
			'eval'					=> array('mandatory'=>true, 'rte'=>'tinyMCE'),
			'attributes'			=> array('legend'=>'general_legend', 'multilingual'=>true),
		),
		'price' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['price'],
			'sorting'				=> true,
			'inputType'				=> 'text',
			'eval'					=> array('mandatory'=>true, 'maxlength'=>255, 'rgxp'=>'digits', 'tl_class'=>'w50'),
			'attributes'			=> array('legend'=>'pricing_legend', 'is_order_by_enabled'=>true),
		),
		'price_override' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['price_override'],
			'inputType'				=> 'text',
			'eval'					=> array('maxlength'=>255, 'tl_class'=>'w50'),
			'attributes'			=> array('legend'=>'pricing_legend'),
		),
		'max_order_quantity' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['max_order_quantity'],
			'inputType'				=> 'text',
			'eval'					=> array('rgxp'=>'digits', 'disabled'=>'disabled'),
			'attributes'			=> array('legend'=>'inventory_legend'),
		),
		'stock_enabled' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['stock_enabled'],
			'inputType'				=> 'checkbox',
			'eval'					=> array('submitOnChange'=>true, 'tl_class'=>'clr'),
			'attributes'			=> array('legend'=>'inventory_legend'),
		),
		'stock_quantity' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['stock_quantity'],
			'inputType'				=> 'text',
			'eval'					=> array(/*'mandatory'=>true, */'rgxp'=>'digits', 'disabled'=>'disabled', 'tl_class'=>'w50'),
		),
		'stock_oversell' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['stock_oversell'],
			'inputType'				=> 'checkbox',
			'eval'					=> array('tl_class'=>'w50 m12'),
		),
		'weight' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['weight'],
			'inputType'				=> 'text',
			'eval'					=> array('rgxp'=>'digits', 'disabled'=>'disabled', 'tl_class'=>'w50'),
			'attributes'			=> array('legend'=>'shipping_legend'),
		),
		'shipping_exempt' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['shipping_exempt'],
			'inputType'				=> 'checkbox',
			'eval'					=> array('tl_class'=>'w50'),
			'attributes'			=> array('legend'=>'shipping_legend'),
		),
		'tax_class' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['tax_class'],
			'inputType'				=> 'select',
			'foreignKey'			=> 'tl_tax_class.name',
			'attributes'			=> array('legend'=>'tax_legend'),
			'eval'					=> array('includeBlankOption'=>true),
		),
		'images' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['images'],
			'inputType'				=> 'mediaManager',
			'attributes'			=> array('legend'=>'media_legend', 'fixed'=>true),
		),
		'published' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['published'],
			'filter'				=> true,
			'inputType'				=> 'checkbox',
			'eval'					=> array('doNotCopy'=>true),
			'attributes'			=> array('legend'=>'publish_legend', 'fixed'=>true),
		),
		'start' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['start'],
			'inputType'				=> 'text',
			'eval'					=> array('rgxp'=>'date', 'datepicker'=>$this->getDatePickerString(), 'tl_class'=>'w50 wizard'),
			'attributes'			=> array('legend'=>'publish_legend'),
		),
		'stop' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['stop'],
			'inputType'				=> 'text',
			'eval'					=> array('rgxp'=>'date', 'datepicker'=>$this->getDatePickerString(), 'tl_class'=>'w50 wizard'),
			'attributes'			=> array('legend'=>'publish_legend'),
		),
		'source' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['source'],
			'eval'					=> array('mandatory'=>true, 'required'=>true, 'fieldType'=>'radio'),
		),
		'variant_attributes' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['variant_attributes'],
			'inputType'				=> 'variantWizard',
			'options'				=> array(),
			'eval'					=> array('doNotSaveEmpty'=>true),
		),
	),
);


class tl_product_data extends Backend
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
		$objType = $this->Database->prepare("SELECT * FROM tl_product_types WHERE id=?")
								  ->limit(1)
								  ->execute($row['type']);

		if (!$objType->downloads || $row['pid'] > 0)
			return '';
			
		$objDownloads = $this->Database->prepare("SELECT COUNT(*) AS total FROM tl_product_downloads WHERE pid=?")->execute($row['id']);
			
		return '<div style="padding:2px 0"><a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).' '.sprintf($GLOBALS['TL_LANG']['MSC']['downloadCount'], $objDownloads->total).'</a></div>';
	}
	
	
	/**
	 * Only list product types a user is allowed to see.
	 */
	public function checkPermission($dc)
	{
		if (strlen($this->Input->get('act')) && $this->Input->get('mode') != 'create')
		{
			$GLOBALS['TL_DCA']['tl_product_data']['config']['closed'] = false;
		}

		// Hide "add variant" button if no products with variants enabled exist
		if (!$this->Database->execute("SELECT * FROM tl_product_data LEFT JOIN tl_product_types ON tl_product_data.type=tl_product_types.id WHERE tl_product_types.variants='1'")->numRows)
		{
			unset($GLOBALS['TL_DCA']['tl_product_data']['list']['global_operations']['new_variant']);
		}
		
		//$this->import('BackendUser', 'User');
				
		/*if ($this->User->isAdmin)
		{			
		
			$objPid = $this->Database->prepare("SELECT pid FROM tl_product_data WHERE id=?")
									 ->limit(1)
									 ->execute($dc->id);
			
			if(!$objPid->numRows)
			{
				$intPid = 0;
			}
			else
			{
				$intPid = $objPid->pid;
			}
			
			$arrProducts = $this->Database->execute("SELECT id FROM tl_product_data WHERE pid=" . $intPid)->fetchEach('id');
		
			if (!is_array($arrProducts) || !count($arrProducts))
			{
				$arrProducts = array(0);
			}
			
			$GLOBALS['TL_DCA']['tl_product_data']['list']['sorting']['root'] = $arrProducts;
			
			if (strlen($this->Input->get('id')) && !in_array($this->Input->get('id'), $arrProducts))
			{
				$this->redirect('typolight/main.php?act=error');
			}
			
			// Add access rights to new pages
			if ($this->Input->get('act') == 'create')
			{
				$GLOBALS['TL_DCA']['tl_page']['fields']['includeChmod']['default'] = 1;
			}
		}
		else
		{		
			$arrTypes = is_array($this->User->iso_product_types) ? $this->User->iso_product_types : array(0);
			
			$arrProducts = $this->Database->execute("SELECT id FROM tl_product_data WHERE pid=0 AND type IN ('','" . implode("','", $arrTypes) . "')")->fetchEach('id');
			
			if (!is_array($arrProducts) || !count($arrProducts))
			{
				$arrProducts = array(0);
			}
			
			$GLOBALS['TL_DCA']['tl_product_data']['list']['sorting']['root'] = $arrProducts;
			
			if (strlen($this->Input->get('id')) && !in_array($this->Input->get('id'), $arrProducts))
			{
				$this->redirect('typolight/main.php?act=error');
			}
		}*/
		
/*
		$objProducts = $this->Database->execute("SELECT id FROM tl_product_data WHERE pid=0");
		
		echo $objProducts->numRows;
		
		if ($objProducts->numRows)
		{
			$GLOBALS['TL_DCA']['tl_product_data']['list']['sorting']['root'] = $objProducts->fetchEach('id');
		}
*/
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
		$objProducts = $this->Database->execute("SELECT pid FROM tl_product_categories WHERE page_id IN (" . implode(',', $arrNodes) . ")");
		if ($objProducts->numRows)
		{
			$GLOBALS['TL_DCA']['tl_product_data']['list']['sorting']['root'] = $objProducts->fetchEach('pid');
		}
		else
		{
			$GLOBALS['TL_DCA']['tl_product_data']['list']['sorting']['root'] = array(0);
		}

		// Add root link
		$arrLinks[] = '<img src="system/themes/' . $this->getTheme() . '/images/pagemounts.gif" width="18" height="18" alt="" /> <a href="' . $this->addToUrl('node=0') . '">' . $GLOBALS['TL_LANG']['MSC']['filterAll'] . '</a>';
		$arrLinks = array_reverse($arrLinks);

		// Insert breadcrumb menu
		$GLOBALS['TL_DCA']['tl_product_data']['list']['sorting']['breadcrumb'] .= '

<ul id="tl_breadcrumb">
  <li>' . implode(' &gt; </li><li>', $arrLinks) . '</li>
</ul>';
	}
	
	
	/**
	 * List products in backend.
	 */
	public function getRowLabel($row, $label = '')
	{
		$arrImages = deserialize($row['images']);
		$thumbnail = '';
		
		if (is_array($arrImages) && count($arrImages))
		{
			foreach( $arrImages as $image )
			{
				$strImage = 'isotope/' . substr($image['src'], 0, 1) . '/' . $image['src'];
				
				if (!is_file(TL_ROOT . '/' . $strImage))
					continue;
					
				$thumbnail = sprintf('<img src="%s" alt="%s" align="left" style="padding-right: 8px;" />', $this->getImage($strImage, 50, 50), $image['alt']);
				break;
			}
		}
		
		if ($row['pid'] > 0)
		{
			return sprintf('<div class="iso_product">%s</div>', $this->getVariantValues($row));
		}
		
		return '<div class="iso_product"><strong>' . $row['name'] . '</strong><div>' . ($row['pid']==0 ? '<em>' . $GLOBALS['TL_LANG']['tl_product_data']['pages'][0] .': ' . $this->getCategoryList(deserialize($row['pages'])) . '</em>' : '') . '</div></div> ';
	}
	
	public function getVariantValues($row)
	{	
		$objVariantAttributes = $this->Database->prepare("SELECT name, field_name FROM tl_product_attributes WHERE add_to_product_variants=?")
								  				->execute(1);
		if(!$objVariantAttributes->numRows)
		{
			return '';
		}
		
		while($objVariantAttributes->next())
		{
			$strField = $objVariantAttributes->field_name;
			
			if(array_key_exists($strField, $row))
			{
				$arrVariantValues[] = array
				(
					'label'		=> $objVariantAttributes->name,
					'value'		=> $row[$strField]
				);
			}
		}

		if(count($arrVariantValues))
		{
			$strReturn = '<ul>';
			
				foreach($arrVariantValues as $record)
				{
					$strReturn .= '<li><strong>' . $record['label'] . ':</strong> ' . $record['value'] . '</li>';
				}		
			
			$strReturn .= '</ul>';			
		}
		
		return $strReturn;
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
		$objProductTypes = $this->Database->execute("SELECT id,name FROM tl_product_types" . ($this->User->isAdmin ? '' : (" WHERE id IN (".implode(',', $arrTypes).")")) . " ORDER BY name");

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
					
					$arrPages[$intPage]['help'] = implode(' , ', $arrHelp);
				}
			}
			
			
			$arrCategories[] = '<a class="tl_tip" longdesc="' . $arrPages[$intPage]['help'] . '" href="' . $this->addToUrl('table=tl_product_categories&id='.$intPage) . '">' . $arrPages[$intPage]['title'] . '</a>';
		}
		
		return implode(', ', $arrCategories);
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
			$objProduct = $this->Database->prepare("SELECT sku, name FROM tl_product_data WHERE id=?")
										 ->limit(1)
										 ->execute($dc->id);

			$autoAlias = true;
			$varValue = strlen($objProduct->name) ? standardize($objProduct->name) : standardize($objProduct->sku);
		}

		$objAlias = $this->Database->prepare("SELECT id FROM tl_product_data WHERE id=? OR alias=?")
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
	 * Save page ids to tl_product_categories table. This allows to retrieve all products associated to a page.
	 */
	public function saveProductCategories($varValue, DataContainer $dc)
	{
		$arrIds = deserialize($varValue);
		
		if (is_array($arrIds) && count($arrIds))
		{
			$time = time();
			$this->Database->prepare("DELETE FROM tl_product_categories WHERE pid=? AND page_id NOT IN (" . implode(',', $arrIds) . ")")->execute($dc->id);
			$objPages = $this->Database->prepare("SELECT page_id FROM tl_product_categories WHERE pid=?")->execute($dc->id);
			$arrIds = array_diff($arrIds, $objPages->fetchEach('page_id'));
			
			foreach( $arrIds as $id )
			{
				$intSorting = $this->Database->prepare("SELECT sorting FROM tl_product_categories WHERE page_id=? ORDER BY sorting DESC")->limit(1)->execute($id)->sorting;
				$intSorting += 128;
				$this->Database->prepare("INSERT INTO tl_product_categories (pid,tstamp,page_id,sorting) VALUES (?,?,?,?)")->execute($dc->id, $time, $id, $intSorting);
			}
		}
		else
		{
			$this->Database->prepare("DELETE FROM tl_product_categories WHERE pid=?")->execute($dc->id);
		}
	
		return $varValue;
	}
	
	/**
	 * Save page ids to tl_product_categories table. This allows to retrieve all products associated to a page.
	 */
	public function linkProductsToCategories($dc)
	{
		if(!$this->User->isAdmin)
		{
			return '';
		}
		
		$objProducts = $this->Database->prepare("SELECT id, pages FROM tl_product_data")
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
				$this->Database->prepare("DELETE FROM tl_product_categories WHERE pid=? AND page_id NOT IN (" . implode(',', $v) . ")")->execute($k);
				$objPages = $this->Database->prepare("SELECT page_id FROM tl_product_categories WHERE pid=?")->execute($k);
				$arrIds = array_diff($v, $objPages->fetchEach('page_id'));
				
				foreach( $arrIds as $id )
				{
					$intSorting = $this->Database->prepare("SELECT sorting FROM tl_product_categories WHERE page_id=? ORDER BY sorting DESC")->limit(1)->execute($id)->sorting;
					$intSorting += 128;
					$this->Database->prepare("INSERT INTO tl_product_categories (pid,tstamp,page_id,sorting) VALUES (?,?,?,?)")->execute($k, $time, $id, $intSorting);
				}
			}
			else
			{
				$this->Database->prepare("DELETE FROM tl_product_categories WHERE pid=?")->execute($k);
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
		
		$objCategoryData = $this->Database->prepare("SELECT * FROM tl_product_categories")
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
			
			$this->Database->execute("UPDATE tl_product_data SET pages='" . serialize($arrValues) . "' WHERE id='" . $intPid . "'");
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
		$objProduct = $this->Database->prepare("SELECT id, pid, language, type, (SELECT attributes FROM tl_product_types WHERE id=tl_product_data.type) AS attributes, (SELECT variant_attributes FROM tl_product_types WHERE id=tl_product_data.type) AS variant_attributes FROM tl_product_data WHERE id=?")->limit(1)->execute($dc->id);
		
		$doNotSubmit = false;
		$strBuffer = '';
		$arrOptions = array();
		$arrAttributes = deserialize($objProduct->attributes);
		
		if (is_array($arrAttributes) && count($arrAttributes))
		{
			foreach( $arrAttributes as $attribute )
			{
				if ($GLOBALS['TL_DCA']['tl_product_data']['fields'][$attribute]['attributes']['add_to_product_variants'])
				{
					$GLOBALS['TL_DCA']['tl_product_data']['fields'][$attribute]['eval']['multiple'] = true;
					$GLOBALS['TL_DCA']['tl_product_data']['fields'][$attribute]['eval']['mandatory'] = true;
					
					$objWidget = new CheckBox($this->prepareForWidget($GLOBALS['TL_DCA']['tl_product_data']['fields'][$attribute], $attribute));
					
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
					$objVariant = $this->Database->prepare("SELECT * FROM tl_product_data WHERE pid=? AND " . implode('=? AND ', array_keys($combination)) . "=?")
												 ->execute(array_merge(array($objProduct->id), $combination));

					if (!$objVariant->numRows)
					{
						$this->Database->prepare("INSERT INTO tl_product_data (tstamp,pid,inherit,type," . implode(',', array_keys($combination)) . ") VALUES (?,?,?,?" . str_repeat(',?', count($combination)) . ")")
									   ->execute(array_merge(array($time, $objProduct->id, $objProduct->variant_attributes, $objProduct->type), $combination));
					}
				}
				
				$this->redirect(str_replace('&key=generate', '', $this->Environment->request));
			}
		}
		
		// Return form
		return '
<div id="tl_buttons">
<a href="'.ampersand(str_replace('&key=generate', '', $this->Environment->request)).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>

<h2 class="sub_headline">'.sprintf($GLOBALS['TL_LANG']['tl_product_data']['generate'][1], $dc->id).'</h2>'.$this->getMessages().'

<form action="'.ampersand($this->Environment->request, true).'" id="tl_product_generate" class="tl_form" method="post">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="tl_product_generate" />

<div class="tl_tbox block">
' . $strBuffer . '
</div>

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
  <input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['tl_product_data']['generate'][0]).'" />
</div>

</div>
</form>';
	}
	
	
	/**
	 * Quickly edit the most common product variant data
	 */
	public function quickEditVariants($dc)
	{
		$arrQuickEditFields = array('sku','price','weight','stock_quantity');

		$objProduct = $this->Database->prepare("SELECT id, pid, language, type, (SELECT attributes FROM tl_product_types WHERE id=tl_product_data.type) AS attributes, (SELECT variant_attributes FROM tl_product_types WHERE id=tl_product_data.type) AS variant_attributes FROM tl_product_data WHERE id=?")->limit(1)->execute($dc->id);
		
		$arrFields = array();
		$arrAttributes = deserialize($objProduct->attributes);
		$arrVarAttributes = deserialize($objProduct->variant_attributes);
		
		if (is_array($arrAttributes) && count($arrAttributes))
		{
			foreach( $arrAttributes as $attribute )
			{
				if ($GLOBALS['TL_DCA']['tl_product_data']['fields'][$attribute]['attributes']['add_to_product_variants'])
				{
					$arrFields[] = $attribute;
				}
			}
		}

		$objVariants = $this->Database->prepare("SELECT * FROM tl_product_data WHERE pid=? AND language=''")->execute($dc->id);
		$strBuffer .= '<div id="tl_buttons">
<a href="'.ampersand(str_replace('&key=quick_edit', '', $this->Environment->request)).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>

<h2 class="sub_headline">'.sprintf($GLOBALS['TL_LANG']['tl_product_data']['quick_edit'][1], $dc->id).'</h2>'.$this->getMessages().'

<form action="'.ampersand($this->Environment->request, true).'" id="tl_product_quick_edit" class="tl_form" method="post">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="tl_product_quick_edit" />

<div class="tl_tbox block">
<table width="100%" border="0" cellpadding="5" cellspacing="0" summary="">
<thead>
<th>' . $GLOBALS['TL_LANG']['tl_product_data']['variantValuesLabel'] . '</th>';

		foreach($arrQuickEditFields as $field)
		{
			if(in_array($field, $arrVarAttributes))
			{
				$strBuffer .= '<th>'.$GLOBALS['TL_LANG']['tl_product_data'][$field][0].'</th>';
			}
		}

$strBuffer .= '<th><img src="system/themes/default/images/published.gif" width="16" height="16" alt="' . $GLOBALS['TL_LANG']['tl_product_data']['published'][0].'" /></th>
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
					$arrWidgets[$field] = new TextField($this->prepareForWidget($GLOBALS['TL_DCA']['tl_product_data']['fields'][$field], $field.'[' . $objVariants->id .']', $objVariants->{$field}));
				}
			}
			/*
			$arrWidgets['sku'] = new TextField($this->prepareForWidget($GLOBALS['TL_DCA']['tl_product_data']['fields']['sku'], 'sku[' . $objVariants->id . ']', $objVariants->sku));
			
			$arrWidgets['price'] = new TextField($this->prepareForWidget($GLOBALS['TL_DCA']['tl_product_data']['fields']['price'], 'price[' . $objVariants->id . ']', $objVariants->price));
			
			$arrWidgets['weight'] = new TextField($this->prepareForWidget($GLOBALS['TL_DCA']['tl_product_data']['fields']['weight'], 'weight[' . $objVariants->id . ']', $objVariants->weight));
			
			$arrWidgets['stock_quantity'] = new TextField($this->prepareForWidget($GLOBALS['TL_DCA']['tl_product_data']['fields']['stock_quantity'], 'stock_quantity[' . $objVariants->id . ']', $objVariants->stock_quantity));
			*/

			foreach($arrWidgets as $key=>$objWidget)
			{
								
				switch($key)
				{
					case 'sku':
						$objWidget->class = 'tl_text_2';
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
		
				$this->Database->prepare("UPDATE tl_product_data %s WHERE id=?")
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
	/*
	'<td>'.$arrWidgets['sku']->generate().'</td>
	<td>'.$arrWidgets['price']->generate().'</td>
	<td>'.$arrWidgets['weight']->generate().'</td>
	<td>'.$arrWidgets['stock_quantity']->generate().'</td>*/
	
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
		$objTree = new FileTree($this->prepareForWidget($GLOBALS['TL_DCA']['tl_product_data']['fields']['source'], 'source', null, 'source', 'tl_product_data'));
		
		$intCurrentBatch = 0;
		
		// Import assets
		if ($this->Input->post('FORM_SUBMIT') == 'tl_product_data_import' && strlen($this->Input->post('source')))
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
			$objProducts = $this->Database->prepare("SELECT * FROM tl_product_data WHERE pid=0")										 
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
						
						$this->Database->prepare("UPDATE tl_product_data SET images=? WHERE id=?")->execute(serialize($arrImages), $objProducts->id);

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

<h2 class="sub_headline">'.$GLOBALS['TL_LANG']['tl_product_data']['import'][1].'</h2>'.$this->getMessages().'

<form action="'.ampersand($this->Environment->request, true).'" id="tl_product_data_import" class="tl_form" method="post">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="tl_product_data_import" />';
		/*$strReturn .= '<div class="tl_tbox block">
  <h3><label for="batch_size">'.$GLOBALS['TL_LANG']['tl_product_data']['batch_size'][0].'</label></h3> <select name="batch_size"><option value=""'.($this->Input->post('batch_size') ? ' selected' : '').'>-</option>';*/
		/*  foreach($arrBatchValues as $value)
		  {
				$strReturn .= '<option value="'.$value.'"'.($this->Input->post('batch_size')==$value ? ' selected' : '').'>'.$value.'</option>';
		  }
		$strReturn .= '</select>';
		$strReturn .= (strlen($GLOBALS['TL_LANG']['tl_product_data']['batch_size'][1]) ? '
		  <p class="tl_help">'.$GLOBALS['TL_LANG']['tl_product_data']['batch_size'][1].'</p>' : '').'</div>';*/
		  
		return $strReturn . '<div class="tl_tbox block">
  <h3><label for="source">'.$GLOBALS['TL_LANG']['tl_product_data']['source'][0].'</label> <a href="typolight/files.php" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['fileManager']) . '" onclick="Backend.getScrollOffset(); this.blur(); Backend.openWindow(this, 750, 500); return false;">' . $this->generateImage('filemanager.gif', $GLOBALS['TL_LANG']['MSC']['fileManager'], 'style="vertical-align:text-bottom;"') . '</a></h3>'.$objTree->generate().(strlen($GLOBALS['TL_LANG']['tl_product_data']['source'][1]) ? '
  <p class="tl_help">'.$GLOBALS['TL_LANG']['tl_product_data']['source'][1].'</p>' : '').'
</div>

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
<input type="submit" name="save" id="save" class="tl_submit" alt="import product assets" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['tl_product_data']['import'][0]).'" />
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
			
		$objType = $this->Database->prepare("SELECT * FROM tl_product_types WHERE id=?")
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
			
		$objType = $this->Database->prepare("SELECT * FROM tl_product_types WHERE id=?")
								  ->limit(1)
								  ->execute($row['type']);
								  
		if (!$objType->variants)
			return '';
		
		return '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
	}
	
	/**
	 * Return the edit page button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function editProduct($row, $href, $label, $title, $icon, $attributes)
	{
		$objParentType = $this->Database->prepare("SELECT type FROM tl_product_data WHERE pid=? OR id=?")
										->limit(1)
										->execute($row['id'], $row['id']);
		
		if(!$objParentType->numRows)
		{
			return '';
		}
		
		return ($this->User->isAdmin || (in_array($objParentType->type, $this->User->iso_product_types))) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
	
	}


	/**
	 * Return the copy page button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function copyProduct($row, $href, $label, $title, $icon, $attributes, $table)
	{
		if ($GLOBALS['TL_DCA'][$table]['config']['closed'])
		{
			return '';
		}

		return ($this->User->isAdmin || (in_array($row['type'], $this->User->iso_product_types))) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
	}



	/**
	 * Return the paste page button
	 * @param object
	 * @param array
	 * @param string
	 * @param boolean
	 * @param array
	 * @return string
	 */
	public function pasteProduct(DataContainer $dc, $row, $table, $cr, $arrClipboard=false)
	{
		$disablePI = false;

		// Disable all buttons if there is a circular reference
		if ($arrClipboard !== false && ($arrClipboard['mode'] == 'cut' && ($cr == 1 || $arrClipboard['id'] == $row['id']) || $arrClipboard['mode'] == 'cutAll' && ($cr == 1 || in_array($row['id'], $arrClipboard['id']))))
		{
			$disablePI = true;
		}

//!@todo check permission for products
/*
		// Check permissions if the user is not an administrator
		if (!$this->User->isAdmin)
		{
			// Disable "paste into" button if there is no permission 2 for the current page
			if (!$disablePI && $row['pid']>0)
			{
				$disablePI = true;
			}

			$objProduct = $this->Database->prepare("SELECT * FROM " . $table . " WHERE id=?")
									  ->limit(1)
									  ->execute($row['pid']);

			// Disable "paste after" button if there is no permission 2 for the parent page
			if (!$disablePA && $objProduct->numRows)
			{
				if (!$this->User->isAllowed(2, $objProduct->fetchAssoc()))
				{
					$disablePA = true;
				}
			}

			// Disable "paste after" button if the parent page is a root page and the user is not an administrator
			if (!$disablePA && ($row['pid'] < 1 || in_array($row['id'], $dc->rootIds)))
			{
				$disablePA = true;
			}
		}
*/

		// Disable buttons for variants
		if ($row['id'] == 0 || ($row['id'] > 0 && $row['pid'] > 0))
		{
			return '';
		}
		
		// Disable "paste into" button for products without variant data
		elseif ($row['id'] > 0)
		{
			$objType = $this->Database->prepare("SELECT * FROM tl_product_types WHERE id=?")->execute($row['type']);
			
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
	 * Return the delete product button
	 */
	//!@todo make sure this is correct (isAllowed should only be used for pages).
	public function deleteProduct($row, $href, $label, $title, $icon, $attributes)
	{
		$root = func_get_arg(7);
		return ($this->User->isAdmin || (in_array($row['type'], $this->User->iso_product_types) && $this->User->isAllowed(3, $row) && !in_array($row['id'], $root))) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
	}
}

