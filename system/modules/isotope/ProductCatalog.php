<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 *
 * The TYPOlight webCMS is an accessible web content management system that 
 * specializes in accessibility and generates W3C-compliant HTML code. It 
 * provides a wide range of functionality to develop professional websites 
 * including a built-in search engine, form generator, file and user manager, 
 * CSS engine, multi-language support and many more. For more information and 
 * additional TYPOlight applications like the TYPOlight MVC Framework please 
 * visit the project website http://www.typolight.org.
 *
 * PHP version 5
 * @copyright  Fred Bliss 2008
 * @author     Fred Bliss / Portions Written By Martin Komara and John Brand
 * @package    CatalogModule 
 * @license    GPL 
 * @filesource
 */


/**
 * Class ProductCatalog 
 *
 * @copyright  Fred Bliss 2008 
 * @author     Fred Bliss / Portions Authored by Martin Komara and John Brand 
 * @package    Controller
 */
class ProductCatalog extends Backend
{
	//set the store.
	public function __construct()
	{	
		parent::__construct();
		
		$this->import('Isotope');				
		$this->import('IsotopeStore', 'Store');		
	}
	
	private function createTable()
	{
		$this->Database->execute(sprintf($this->createTableStatement, 'tl_product_data'));
	}

	protected $sqlDef = array
	(
		'integer'        => "int(10) NULL default NULL",
		'decimal'       => "double NULL default NULL",
		'shorttext'		=> "varchar(128) NOT NULL default ''",
		'text'          => "varchar(255) NOT NULL default ''",
		'longtext'      => "text NULL",
		'datetime'		=>	"int(10) unsigned NOT NULL default '0'",
		'select'        => "int(10) NOT NULL default 0",
		'checkbox'      => "char(1) NOT NULL default ''",
		'options'		=> "text NULL",
		'file'          => "text NULL",
		'media'			=> "varchar(255) NOT NULL default '0'",
	);
	
	protected $arrForm = array();
	protected $arrFields = array();
	protected $arrTypes = array('text','password','textarea','select','radio','checkbox','upload', 'hidden');
	protected $arrList = array ('tstamp','pages','new_import'/*,'add_audio_file','add_video_file'*/);	//Basic required fields
	protected $arrDefault = array ('id', 'tstamp','pages','type','new_import');
	protected $basePaletteAttributes = '{general_legend},type,pages,';
	protected $arrCountMax = array();
	protected $arrCountFree = array();
	protected $arrData = array();

	protected $systemColumns = array('id', 'pid', 'sorting', 'tstamp');
	
	protected $renameColumnStatement = "ALTER TABLE tl_product_data CHANGE COLUMN %s %s %s";
	
	protected $createColumnStatement = "ALTER TABLE tl_product_data ADD %s %s";
	
	protected $dropColumnStatement = "ALTER TABLE tl_product_data DROP COLUMN %s";

	protected $createTableStatement = "CREATE TABLE `%s` (
			`id` int(10) unsigned NOT NULL auto_increment,
		    `pid` int(10) unsigned NOT NULL default '0',
		    `sorting` int(10) unsigned NOT NULL default '0',
			`tstamp` int(10) unsigned NOT NULL default '0',
			`pages` text NULL,
			`type` varchar(255) NOT NULL default '',
			`new_import` char(1) NOT NULL default '',
  			`old_images_list` text NULL,
			PRIMARY KEY  (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	
	/*		    `audio_source` varchar(32) NOT NULL default '',
  			`audio_jumpTo` text NULL,
  			`audio_url` varchar(255) NOT NULL default '',
			`video_source` varchar(32) NOT NULL default '',
  			`video_jumpTo` varchar(255) NOT NULL default '',
  			`video_url` text NULL,
			`add_audio_file` char(1) NOT NULL default '0',
			`add_video_file` char(1) NOT NULL default '0',
			`option_collection` text NULL,
	*/
		
	protected $arrPreExistingRecordInfo;

	
	/**
	 * ProductCatalog HOOKS: loadProductCatalogDCA, ValidateFormField, ProcessFormData 
	 */	
	
	public function loadProductCatalogDCA($strTable)
	{		
		if(!$this->Database->tableExists('tl_product_data'))
		{
			$this->createTable();
		}	
		
		
		//Check for any missing standard attributes and build a list which can then be added into the table tl_product_data.		
		foreach($GLOBALS['ISO_ATTR'] as $arrSet)
		{
		
			if(!$this->Database->fieldExists($arrSet['field_name'], 'tl_product_data'))
			{
				$arrDefaultColumns[$arrSet['type']] = $arrSet['field_name'];
			}
								
			$objAttributeExists = $this->Database->prepare("SELECT COUNT(*) AS count FROM tl_product_attributes WHERE field_name=?")
													   ->limit(1)
													   ->execute($arrSet['field_name']);
			if($objAttributeExists->count < 1)
			{
				$arrAttributesToInsert[] = $arrSet;
			}
			
		}
			
		if(sizeof($arrDefaultColumns))
		{

			foreach($arrDefaultColumns as $k=>$v)
			{
				$this->addDefaultAttribute($v, $k);
			}
		}
		
		if(sizeof($arrAttributesToInsert))
		{		
			$sorting = $this->getNextSortValue('tl_product_attributes');
			
			foreach($arrAttributesToInsert as $row)
			{			
				$this->insertAttributeRecord($row, $sorting);
			
				$sorting+=128;
			}
		}		
		$this->initializeFields();	//Get field data from tl_product_attributes.  Stored in this->arrFields.
		
		// setup global array first
		$GLOBALS['TL_DCA']['tl_product_data'] = array
		(
		
			// Config
			'config' => array
			(
				'dataContainer'               => 'Table',
				'enableVersioning'            => false,
				'doNotCopyRecords'            => true,
				'doNotDeleteRecords'          => false,
				'switchToEdit'                => true,
				'onload_callback'			  => array
				(
					array('MediaManagement', 'createMediaDirectoryStructure')
				),
				'onsubmit_callback'			  => array
				(
					array('ProductCatalog', 'saveProduct')
				)
			),
		
			// List
			'list' => array
			(
				'sorting' => array
				(
					'mode'                    => 1,
					'fields'                  => array('sorting'),
					'flag'                    => 1,
					'panelLayout'             => 'sort,filter;search,limit',
					//'headerFields'            => array('name'),
					//'child_record_callback'   => array('ProductCatalog','getRowLabel'),
//					'paste_button_callback'   => array('ProductCatalog', 'pastButton'),
				),
				'label' => array
				(
					'fields'                  => array(),
					'format'                  => '%s',
				),
				'global_operations' => array
				(
					
					/*'export' => array
					(
						'label'               => &$GLOBALS['TL_LANG']['tl_product_data']['export'],
						'href'                => 'act=export',
						'class'               => 'header_css_import', // for css icon
						'attributes'          => 'onclick="Backend.getScrollOffset();"'
					),*/
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
						'label'               => &$GLOBALS['TL_LANG']['tl_product_data']['edit'],
						'href'                => 'act=edit',
						'icon'                => 'edit.gif'
					),
					
					'cut' => array
					(
						'label'               => &$GLOBALS['TL_LANG']['tl_product_data']['cut'],
						'href'                => 'act=paste&amp;mode=cut',
						'icon'                => 'cut.gif',
						'attributes'          => 'onclick="Backend.getScrollOffset();"'
					),
		
					'delete' => array
					(
						'label'               => &$GLOBALS['TL_LANG']['tl_product_data']['delete'],
						'href'                => 'act=delete',
						'icon'                => 'delete.gif',
						'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
					),
		
					'show' => array
					(
						'label'               => &$GLOBALS['TL_LANG']['tl_product_data']['show'],
						'href'                => 'act=show',
						'icon'                => 'show.gif'
					)
		
				)
		
			),
		
		
		);
		
		foreach($this->arrFields as $field)
		{
			foreach($field as $k=>$v)
			{
				if($k=='is_hidden_on_backend' && $v!='1')
				{
					$arrFieldCollection[] = $field['field_name'];				
				}
			}
		}			
				
		$GLOBALS['TL_DCA']['tl_product_data']['list']['label']['fields'] = array_merge($this->arrList, count($arrFieldCollection) ? $arrFieldCollection : array());
		$GLOBALS['TL_DCA']['tl_product_data']['list']['label']['format'] = '<span style="color:#b3b3b3; padding-right:3px;">[%s]</span>' . (count($this->arrFields) ? join(', ', array_fill(0,count($this->arrFields),'%s')) : '');
		$GLOBALS['TL_DCA']['tl_product_data']['list']['label']['label_callback'] = array('ProductCatalog','getRowLabel');

		// add palettes
		
		//TODO: Make selectors dynamic
		//$GLOBALS['TL_DCA']['tl_product_data']['palettes']['__selector__'] = array('add_audio_file','add_video_file');
		$GLOBALS['TL_DCA']['tl_product_data']['palettes']['__selector__'] = array('type');
		
		//$arrAdditionalSelectors = $this->getSelectors();
		
		//$GLOBALS['TL_DCA']['tl_product_data']['palettes']['__selector__'] = array_merge($GLOBALS['TL_DCA']['tl_product_data']['palettes']['__selector__'], $arrAdditionalSelectors);
		
		//TODO: Make palettes dynamic - start with the basic fields and add additionals for the default palette, while loading the palettes as defined by
		// each product type from tl_product_types.
					
		$arrProductTypePalettes = $this->getProductTypePalettes();

		$GLOBALS['TL_DCA']['tl_product_data']['palettes'] = array_merge($GLOBALS['TL_DCA']['tl_product_data']['palettes'],$arrProductTypePalettes); 
		//$GLOBALS['TL_DCA']['tl_product_data']['subpalettes']['add_audio_file'] = 'audio_source,audio_jumpTo,audio_url';
		//$GLOBALS['TL_DCA']['tl_product_data']['subpalettes']['add_video_file'] = 'video_source,video_jumpTo,video_url';
		//$GLOBALS['TL_DCA']['tl_product_data']['subpalettes'] = $this->getSubpalettes();
		
		// first add common DCA fields
		$GLOBALS['TL_DCA']['tl_product_data']['fields']['type'] = 
		array
		(
			'label'					  =>  &$GLOBALS['TL_LANG']['tl_product_data']['type'],
			'inputType'				  => 'select',
			'eval'					  => array('mandatory'=>true, 'includeBlankOption'=>true, 'submitOnChange'=>true),
			'options_callback'		  => array('ProductCatalog','getProductTypes')
		);
		
		$GLOBALS['TL_DCA']['tl_product_data']['fields']['tstamp'] =
		array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_data']['tstamp'],
			'search'                  => false,
			'sorting'				  => true,
			'flag'                    => 6,
			'eval'                    => array('rgxp'=>'datim') 
		);	
			
		$GLOBALS['TL_DCA']['tl_product_data']['fields']['pages'] =
		array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_data']['pages'],
			'inputType'				  => 'pageTree',
			'search'                  => false,
			'filter'				  => true,
			'sorting'				  => true,
			'flag'                    => 1,
			'eval'                    => array('mandatory'=>true,'fieldType'=>'checkbox', 'multiple'=>true, 'helpwizard'=>true),
			'reference'			      => $this->getPageLabels(),
			'save_callback'			  => array
			(
				array('ProductCatalog','saveProductToCategories')
			)
			//'explanation'             => 'pageCategories'
		);	
		/*
		$GLOBALS['TL_DCA']['tl_product_data']['fields']['add_audio_file'] = array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_data']['add_audio_file'],
			'default'				  => 'internal',
			'filter'                  => false,
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange'=>true)
		);
		
		$GLOBALS['TL_DCA']['tl_product_data']['fields']['add_video_file'] = array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_data']['add_video_file'],
			'default'				  => 'internal',
			'filter'                  => false,
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange'=>true)
		);
	
		$GLOBALS['TL_DCA']['tl_product_data']['fields']['audio_source'] = array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_data']['audio_source'],
			'default'                 => 'internal',
			'filter'                  => false,
			'inputType'               => 'radio',
			'options'                 => array('internal', 'external'),
			'reference'               => &$GLOBALS['TL_LANG']['tl_product_data'],
			'eval'                    => array('helpwizard'=>true)
		);
				
		$GLOBALS['TL_DCA']['tl_product_data']['fields']['audio_jumpTo'] = array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_data']['audio_jumpTo'],
			'inputType'               => 'fileTree',
			'eval'                    => array('fieldType'=>'radio', 'files'=>true, 'helpwizard'=>true)
		);
		
		$GLOBALS['TL_DCA']['tl_product_data']['fields']['audio_url'] = array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_data']['audio_url'],
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('decodeEntities'=>true, 'maxlength'=>255)
		);
		
		$GLOBALS['TL_DCA']['tl_product_data']['fields']['video_source'] = array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_data']['video_source'],
			'default'                 => 'internal',
			'filter'                  => false,
			'inputType'               => 'radio',
			'options'                 => array('internal', 'external'),
			'reference'               => &$GLOBALS['TL_LANG']['tl_product_data'],
			'eval'                    => array('helpwizard'=>true)
		);		
		$GLOBALS['TL_DCA']['tl_product_data']['fields']['video_jumpTo'] = array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_data']['video_jumpTo'],
			'inputType'               => 'fileTree',
			'eval'                    => array('fieldType'=>'radio', 'files'=>true, 'helpwizard'=>true)
		);
		
		$GLOBALS['TL_DCA']['tl_product_data']['fields']['video_url'] = array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_data']['video_url'],
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('decodeEntities'=>true, 'maxlength'=>255)
		);

		$GLOBALS['TL_DCA']['tl_product_data']['fields']['option_collection'] = array
		(
			'label'					  => &$GLOBALS['TL_LANG']['tl_product_data']['option_collection'],
			'inputType'				  => 'productOptionWizard',
			'load_callback'			  => array
			(
				array('ProductCatalog','loadProductOptions')
			),
			'save_callback'			  => array
			(
				array('ProductCatalog','saveProductOptions')
			)
		);*/
		
		// add DCA for form fields
		foreach ($this->arrFields as $key=>$field) 
		{
			
			$eval = array();
			//if ($field['mandatory']) $eval['mandatory'] = 'true';
			if ($field['is_required']) $eval['mandatory'] = 'true';
			if ($field['rgxp']) $eval['rgxp'] = $field['rgxp'];
			if ($field['multiple']) $eval['multiple'] = $field['multiple'];
	
			// check for options lookup 
			$inputType = '';
			switch ($field['type'])
			{
				case 'integer':
				case 'decimal':
				case 'datetime':
					$inputType = 'text';
					break;
					
				case 'shorttext':
					$inputType = 'text';
					break;
					
				case 'longtext':
					$inputType = 'textarea';
					
					if($field['use_rich_text_editor'])
					{
						$eval['rte'] = 'tinyMCE';
					}
					break;

				case 'file':
				case 'media':
					$inputType = 'mediaManager';
					$eval['cols'] = 4;
					//if($field['show_files']) $eval['files'] = true;
					//$eval['fieldType'] = 'radio';
					break;
					
				case 'options':
					$inputType = 'radio';
					$eval['multiple'] = false;
					if($field['use_alternate_source']==1)
					{
						if(strlen($field['list_source_table']) > 0 && strlen($field['list_source_field']) > 0)
						{
							$strForeignKey = $field['list_source_table'] . '.' . $field['list_source_field'];
						
						}
					}else{
					
						$arrValues = array();
						$arrOptionsList = deserialize($field['option_list']);
						
						
						foreach ($arrOptionsList as $arrOptions)
						{
							/*if ($arrOptions['default'])
							{
								$arrValues[] = $arrOptions['value'];
							}*/
							
							$arrValues[$arrOptions['value']] = $arrOptions['label'];
						}											
						
					}
					break;
					
				case 'select':
					$inputType = 'select';
					//$inputType = 'productOptionsWizard';
					
					if($field['use_alternate_source']==1)
					{
						if(strlen($field['list_source_table']) > 0 && strlen($field['list_source_field']) > 0)
						{
							$strForeignKey = $field['list_source_table'] . '.' . $field['list_source_field'];
						
						}
					}else{
					
						$arrValues = array();
						$arrOptionsList = deserialize($field['option_list']);
						
						if(sizeof($arrOptionList))
						{												
							foreach ($arrOptionsList as $arrOptions)
							{
								/*if ($arrOptions['default'])
								{
									$arrValues[] = $arrOptions['value'];
								}*/
								
								$arrValues[$arrOptions['value']] = $arrOptions['label'];
							}											
						}
					}	
					
					//optional?
					$eval['includeBlankOption'] = true;
					break;
					
				default:
					$inputType = $field['type'];
					break;
			}
			
			$filter = ($this->arrForm['useFilter'] && $this->arrForm['filterField'] == $key);

			$GLOBALS['TL_DCA']['tl_product_data']['fields'][$key] =
				array
				(
					'label'           => array($field['name'], $field['description']),
					'inputType'				=> $inputType,
					'search'          => !$filter,
					'filter'         	=> $filter,
					'eval'            => $eval,
					'load_callback'		=> array
					(
						array('ProductCatalog','loadField')
					),
					'save_callback'		=> array
					(
						array('ProductCatalog','saveField')
					)
				);
			
			if (strlen($field['options'])) 
			{
				$options = deserialize($field['options']);
				foreach ($options as $option) {
					$optionList[$option['value']] = $option['label'];
				}
				$GLOBALS['TL_DCA']['tl_product_data']['fields'][$key]['options'] = array_keys($optionList);
				$GLOBALS['TL_DCA']['tl_product_data']['fields'][$key]['reference'] = $optionList;
				unset($optionList);

			}
			
			if(strlen($strForeignKey) && $field['type'] == 'select')
			{
				$GLOBALS['TL_DCA']['tl_product_data']['fields'][$key]['foreignKey'] = $strForeignKey;
				$strForeignKey = "";
			}
			
			if(is_array($arrValues) && $field['type'] == 'select')
			{
				$GLOBALS['TL_DCA']['tl_product_data']['fields'][$key]['eval']['includeBlankOption'] = true;
				$GLOBALS['TL_DCA']['tl_product_data']['fields'][$key]['options'] = $arrValues;
			}
			
			if (!empty($field['load_callback']))
			{
	
				$arrCallbackSet = explode(',',$field['load_callback']);

				if(is_array($arrCallbackSet))
				{
					foreach($arrCallbackSet as $callback)
					{
						$arrCallbacks[] = explode(".", $callback);
					}
																		
					$GLOBALS['TL_DCA']['tl_product_data']['fields'][$key]['load_callback'] = $arrCallbacks;
					
				}else{
					$GLOBALS['TL_DCA']['tl_product_data']['fields'][$key]['load_callback'] = array(
						explode(".", $field['load_callback'])
					);
				}
			}
			
			if (!empty($field['save_callback']))
			{
				$arrCallbackSet = explode(',',$field['save_callback']);
								
				if(is_array($arrCallbackSet))
				{
					$arrCallbacks = array(); // reset the callback array
					foreach($arrCallbackSet as $callback)
					{
						$arrCallbacks[] = explode(".", $callback);
					}
																		
					$GLOBALS['TL_DCA']['tl_product_data']['fields'][$key]['save_callback'] = $arrCallbacks;
					
				}else{
					$GLOBALS['TL_DCA']['tl_product_data']['fields'][$key]['save_callback'] = array(
						explode(".", $field['save_callback'])
					);
				}
					
			}
			
		}
		
		return $strTable;
		
	}
	
	public function saveProductOptions($varValue, DataContainer $dc)
	{
		//** Data structure example **//
		/*
			array(2) {
			  [0]=>			/// ROWS
			  array(2) {	
			    [0]=>
			    string(2) "17"	//the select at 0, 0
			    [1]=>
			    string(2) "18"  //the select at 0, 1
			  }
			  [1]=>
			  array(2) {
			    [0]=>
			    string(2) "17"	//the select at 1, 0
			    [1]=>
			    string(2) "18"  //the select at 1, 1
			  }
			}
			
			array(2) {
			  [0]=>
			  array(2) {
			    [0]=>
			    string(3) "red" 	//the value at 0, 0
			    [1]=>
			    string(5) "small"	//the value at 0, 1
			  }
			  [1]=>
			  array(2) {
			    [0]=>
			    string(3) "red"		//the value at 1, 0
			    [1]=>
			    string(6) "medium"  //the value at 1, 1
			  }
			}
		
			and we will transform this into the following structure...
			
			array(2) { 			// Number of total rows
				
				[0] =>
				array(2) {
					[0] => array(2)
					{
						'attribute'		=> string(2) "17",
						'value'			=> string(3) "red"
					},
					[1] => array(2)
					{
						'attribute'		=> string(2) "18",
						'value'			=> string(5) "small"					
					}
				},
				[1] =>
				array(2) {
					[0] => array(2)
					{
						'attribute'		=> string(2) "17",
						'value'			=> string(3) "red"
					},
					[1] => array(2)
					{
						'attribute'		=> string(2) "18",
						'value'			=> string(6) "medium"					
					}
				
				}
			
			}
		
		*/
		$arrAttributes = deserialize($varValue);	//because the first thing that happens is this, on save.	
		$arrValues = $this->Input->post($dc->field . '_values'); 
	
		$arrCompositeValues = array();
	
		for($x=0; $x<sizeof($arrAttributes); $x++)
		{
			
			for($y=0; $y<sizeof($arrValues); $y++)
			{	
				
					
					$arrAttributeValuePairs[] = array
					(
						'x'				=> $x,
						'y'				=> $y,
						'attribute'		=> $arrAttributes[$x][$y],
						'value'			=> $arrValues[$x][$y]					
					);						
					
			}		
		
		}
		
		return serialize($arrAttributeValuePairs);
		
	}
	
	public function loadProductOptions($varValue, DataContainer $dc)
	{
		$arrAttributeValuePairs = deserialize($varValue);
		
		$arrAttributes = array();
		$arrValues = array();
		
		if(sizeof($arrAttributeValuePairs)<1)
		{
			return;
		}
		
		foreach($arrAttributeValuePairs as $row)
		{
			
				$x = (integer)$row['x'];
				$y = (integer)$row['y'];
				
				$arrAttributes[$x][$y] = $row['attribute'];
				
				$arrValues[$x][$y] = $row['value'];
				
				/*
				$varValue[$valuePair['x']] = array
				(
					$valuePair['x']			=>	$valuePair[$valuePair['x']][$valuePair['attribute']],
					$valuePair['x']+1		=>	$valuePair[$valuePair['x']+1][$valuePair['attribute']]
				);
				
				$arrValues[$row['x']] = array
				(
					$row['x']		=>	$valuePair[$row['x']]['value'],
					$row['x']+1		=>  $valuePair[$row['x']+1]['value']
				);*/
		
		}	
			$_SESSION['FORM_DATA'][$dc->field . '_values'] = $arrValues;
			//$_SESSION['FORM_DATA'][$dc->field] = $arrAttributes;
			
			//serialize($_SESSION['FORM_DATA'][$dc->field.'_values']);
			//
			//$varValue = $arrAttributes;
			return $arrAttributes;
			
			/*array(2) {
			  [0]=>			/// ROWS
			  array(2) {	
			    [0]=>
			    string(2) "17"	//the select at 0, 0
			    [1]=>
			    string(2) "18"  //the select at 0, 1
			  }
			  [1]=>
			  array(2) {
			    [0]=>
			    string(2) "17"	//the select at 1, 0
			    [1]=>
			    string(2) "18"  //the select at 1, 1
			  }
			}*/
			
	
	
	}
	
	public function loadField($varValue, DataContainer $dc)
	{
		
		// HOOK: loadField callback
		if (array_key_exists('loadField', $GLOBALS['TL_HOOKS']) && is_array($GLOBALS['TL_HOOKS']['loadField']))
		{
			foreach ($GLOBALS['TL_HOOKS']['loadField'] as $callback)
			{
				$this->import($callback[0]);
				$this->$callback[0]->$callback[1]($varValue, $dc);
			}
		}

		
		return $varValue;
	}
	
	public function saveField($varValue, DataContainer $dc)
	{
	
		$objAttribute = $this->Database->prepare("SELECT * FROM tl_product_attributes WHERE field_name=?")
									   ->limit(1)
									   ->execute($dc->field);
		
		if($objAttribute->numRows < 1)
		{
			throw new Exception('Not a valid record id!');
		}
		
		if($objAttribute->is_filterable)
				$this->saveFilterValuesToCategories($varValue, $dc);
		
		//if($objAttribute->is_order_by_enabled)
				
		//if($objAttribute->is_searchable)
		
		//if($objAttribute->is_used_for_price_rules)
		
			
		// HOOK: loadField callback
		if (array_key_exists('saveField', $GLOBALS['TL_HOOKS']) && is_array($GLOBALS['TL_HOOKS']['saveField']))
		{
			foreach ($GLOBALS['TL_HOOKS']['saveField'] as $callback)
			{
				$this->import($callback[0]);
				$varValue = $this->$callback[0]->$callback[1]($varValue, $dc);
			}
		}
		
		return $varValue;
		
	}
	
	public function saveProduct(DataContainer $dc)
	{
		
		/*if(!$this->Input->get('begin'))
		{
			$intBegin = 0;
		}else{
			$intBegin = $this->Input->get('begin');
		}
		
		if(!$this->Input->get('end'))
		{
			$intEnd = 30;
		}else{
			$intEnd = $this->Input->get('end');
		}*/
		
		$this->import('MediaManagement');
		
		$objIsNewImport = $this->Database->prepare("SELECT id, pages, name, sku, alias, description, teaser, main_image FROM tl_product_data WHERE new_import=? AND id=?")
										 ->execute(1, $dc->id);
		
		
		
		if($objIsNewImport->numRows > 0)
		{		
			//$arrNewImports = $objIsNewImport->fetchAllAssoc();
			
			while($objIsNewImport->next())
			{
			
				
				
				if(strlen($objIsNewImport->sku) < 1)
				{
					$strSKU = $this->generateSKU('', $dc, $dc->id);
				}
				else
				{
					$strSKU = $objIsNewImport->sku;
				}
				
				if(strlen($objIsNewImport->alias) < 1)
				{
					$strAlias = $this->generateAlias('', $dc, $dc->id);
				}
				else
				{
					$strAlias = $objIsNewImport->alias;
				}
				
				if(strlen($objIsNewImport->teaser) < 1)
				{
					$strTeaser = $this->generateTeaser($objIsNewImport->description, $dc, $dc->id, 'import');
				}
				else
				{
					$strTeaser = $objIsNewImport->teaser;
				}
				
				$strSerializedValues = $this->prepareCategories($objIsNewImport->pages, $dc, $objIsNewImport->id);
	
				//$this->MediaManagement->thumbnailImportedImages($objIsNewImport->main_image, $dc, $dc->id, $strAlias);
				
				//$this->MediaManagement->thumbnailCurrentImageForListing($objIsNewImport->main_image, $dc);
								
				$this->Database->prepare("UPDATE tl_product_data SET sku=?, alias=?, teaser=?, pages=?, visibility=1, new_import=0 WHERE id=?")
							   ->execute($strSKU, $strAlias, $strTeaser, $strSerializedValues, $dc->id);
				
								
				$this->saveProductToCategories($strSerializedValues, $dc, $dc->id);
				
				//Not yet..
				//$this->saveFilterValuesToCategories($objIsNewImport->pages, $dc, $dc->id);
			}
			/*
			if(count($arrNewImports) < 30)
			{
			
				$blnEnd = true;
			}
			
			$intBegin += 30;
			$intEnd += 30;
			
			$strURL = 'main.php?do=products_and_attributes&table=tl_product_data&act=edit&id=667&begin=' . $intBegin . '&end=' . $intEnd;
			
			if(!$blnEnd)
			{
				header($strURL);
			}*/
		}
						
		// HOOK: save product callback
		if (array_key_exists('saveProduct', $GLOBALS['TL_HOOKS']) && is_array($GLOBALS['TL_HOOKS']['saveProduct']))
		{
			foreach ($GLOBALS['TL_HOOKS']['saveProduct'] as $callback)
			{
				$this->import($callback[0]);
				$this->$callback[0]->$callback[1]($dc, 'tl_product_data');
			}
		}
		
			
	}
	
	protected function getProductTypePalettes()
	{
		$objProductTypes = $this->Database->prepare("SELECT name, alias, attributes FROM tl_product_types")
									  ->execute();
		
		if($objProductTypes->numRows < 1)
		{
			return array();
		}
			
		
		$objAttributes = $this->Database->prepare("SELECT id, is_hidden_on_backend FROM tl_product_attributes")->execute();
		
		if($objAttributes->numRows < 1)
		{
			throw new Exception('No product attributes found!');		//TODO - language specific error message.
		}
				
		$arrAttributes = $objAttributes->fetchAllAssoc();
			
		foreach($arrAttributes as $attribute)
		{
			$arrHiddenAttributes[$attribute['id']] = $attribute['is_hidden_on_backend'];
		
		}		
		
		$arrPalettes['default'] = $this->basePaletteAttributes;
		
		while($objProductTypes->next())
		{
			$arrEnabledAttributes = deserialize($objProductTypes->attributes);	
					
			foreach($arrEnabledAttributes as $attribute)
			{
				$intIndex = (integer)$attribute;
				
				if($arrHiddenAttributes[$intIndex]=='1')
				{		
					continue;	
				}
				
				$arrFieldCollection[] = $intIndex;
			}
									
			$strAttributes = $this->buildPaletteString($arrFieldCollection);
			
			$arrPalettes[$objProductTypes->alias] = $strAttributes;
			
		}
				
		return $arrPalettes;
	}
	
	protected function getSelectors()
	{
		return array();
	
	}
	
	
	private function buildPaletteString($arrAttributes)
	{
		$strFields = join(',', $arrAttributes);
	
		$objFieldGroups = $this->Database->prepare("SELECT field_name, fieldGroup FROM tl_product_attributes WHERE id IN(" . $strFields . ") ORDER BY sorting")
										 ->execute();
		
		if($objFieldGroups->numRows < 1)
		{
			throw new Exception('No fields returned.');
		}
		
		//Create an array grouped by field group
		while($objFieldGroups->next())
		{
			$arrFieldsAndGroups[$objFieldGroups->fieldGroup][] = $objFieldGroups->field_name;
		}
			
			//var_dump($arrFieldsAndGroups['media_legend']);
		//This is necessary because otherwise, attributes that do not fall in sequential order in terms of cardinality then get placed out of order in the
		//palette string.  This allows us to not have to worry about that but ensuring groups are in the correct order.
		foreach($GLOBALS['ISO_MSC']['tl_product_data']['groups_ordering'] as $group)
		{
			$arrOrderedFieldGroups[$group] = $arrFieldsAndGroups[$group];
		}
	
	
		$strPalette = $this->basePaletteAttributes;
		
		//Build
		foreach($arrOrderedFieldGroups as $k=>$v)
		{
			if($k!='general_legend')
			{
				$strPalette .= '{' . $k . '},';
			}else{
				$strPalette .= ',';
			}
			
			if(is_null($v))
			{
				continue;
			}
			
			$strPalette .= join(',', $v) . ';';
		}

		return $strPalette;
	}
	
	protected function getPageLabels()
	{
		$objPageLabels = $this->Database->prepare("SELECT id,title FROM tl_page")
							 ->execute();
							 
		if($objPageLabels->numRows<1)
		{
			return array();
		}
		
		$arrPageLabels= $objPageLabels->fetchAllAssoc();
		
		foreach ($arrPageLabels as $page)
		{
			$arrPages[$page['id']] = $page['title'];
		}
		
		return $arrPages;
	}
	
	
	protected function prepareCategories($varValue, DataContainer $dc)
	{
		if(is_null($varValue) || strlen(trim($varValue)) < 1)
		{
			return '';
		}
		//Potentially the delimiter could be different.  May want to try and figure it out autommatically.
		if(!is_array(deserialize($varValue)))
		{
			if(strpos($varValue, ','))
			{
				$arrPages = explode(',', $varValue);
				if(sizeof($arrPages) < 1 || strlen($arrPages[0])<1)
				{
					return '';
				}
			}else{
				$arrPages[] = $varValue;	//singular value
			}
			
			$arrPages = serialize($arrPages);
			
			return $arrPages;
		}
		
		return $varValue;
		
	}
	
	protected function setRecordNewImportValue($varValue, DataContainer $dc, $id=0)
	{
		//For import needs, this is an override of the current record ID because when importing we're
		//not utlizing the DataContainer.  We should separate these functions with an intermediary function so that this logic
		//which is repeated across various other functions can be fed just an integer value instead of the more specific
		//DataContainer and its corresponding values.
		if($id!=0)
		{
			$intId = $id;
		}else{
			$intId = $dc->id;
		}
		
		
		$this->Database->prepare("UPDATE tl_product_data SET new_import=? WHERE id=? and new_import=1")
								->execute(0, $intId);
	}
	
	
	public function generateMappingAttributeList()
	{
		$arrOptions = array();
		$arrAttributes = array();
		
		$objAttributes = $this->Database->prepare("SELECT field_name FROM tl_product_attributes WHERE pid=?")
										->execute($set['id']);
								
		if($objAttributes->numRows < 1)
		{
			return false;
		}
				
		$arrAttributes = $objAttributes->fetchAllAssoc();
			
		foreach($arrAttributes as $attribute)
		{						
			$arrOptions[] = array
			(
				'value' => $attribute['field_name'],
				'label' => $attribute['name']
			);
		}
					
		return $arrOptions;
	}
	
	
	private function initializeFields() 
	{
		$objFields = $this->Database->prepare("SELECT * FROM tl_product_attributes ORDER BY sorting ASC")
									->execute();
		
		while ($objFields->next())
		{
			$this->arrFields[$objFields->field_name] = $objFields->row();
		}
		
	}
		
	
	public function renameColumn($varValue, DataContainer $dc)
	{
		
		$varValue = strtolower($this->mysqlStandardize($varValue));
		
		if (!preg_match('/^[a-z_][a-z\d_]*$/i', $varValue))
		{
			throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['invalidColumnName'], $varValue));
		}
		if (in_array($varValue, $this->systemColumns))
		{
			throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['systemColumn'], $varValue));
		}
		
		//Get pertinent field data.
		$objField = $this->Database->prepare("SELECT id, type, field_name FROM tl_product_attributes WHERE id=?")
								   ->limit(1)
								   ->execute($dc->id);
			
		// check duplicate form_field name
		$objItems = $this->Database->prepare("SELECT id FROM tl_product_attributes WHERE pid=? AND id<>? AND name=?")
								   ->execute($objField->pid, $objField->id, $varValue);
		
		if ($objItems->numRows)
		{
			throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['columnExists'], $varValue));
		}
		

		$fieldType = $objField->type ? $objField->type : 'text';
		$fieldName = $objField->field_name;
		
		if ($this->Database->fieldExists($fieldName, 'tl_product_data'))
		{
			if ($objField->field_name != $varValue)
			{
				$statement = sprintf($this->renameColumnStatement, $fieldName, $varValue, $this->sqlDef[$fieldType]);
			}
		}
		else
		{
			$statement = sprintf($this->createColumnStatement, $varValue, $this->sqlDef[$fieldType]);
		}
		
		if (strlen($statement))
			$this->Database->execute($statement);
		
		//Create the field name for quick reference in code.
//		$this->Database->prepare("UPDATE tl_product_attributes SET field_name='" . $varValue . "' WHERE id=?")
//					   ->execute($dc->id);
		
		return $varValue;
	}
	
	
	/** 
	 * Add a default attribute to the tl_product_data table from a source other than normal editing operations, for example, the ISO_ATTR global array
	 *
	 * @access public
	 * @param variant $varValue
	 * @param object $dc
	 * @param string $fieldType
	 * @return $varValue;
	 */
	public function addDefaultAttribute($varValue, $fieldType)
	{
		if (!preg_match('/^[a-z_][a-z\d_]*$/i', $varValue))
		{
			throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['invalidColumnName'], $varValue));
		}
		
		if (in_array($varValue, $this->systemColumns))
		{
			throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['systemColumn'], $varValue));
		}
		
		$statement = sprintf($this->createColumnStatement, $varValue, $this->sqlDef[$fieldType]);
		
				
		$this->Database->execute($statement);
		
		return $varValue;
	}
	
	/** 
	 * Insert a new attribute record from a source other than normal table operations (for example, from default attributes defined in ISO_ATTR global array
	 *
	 * @access public
	 * @param array $arrSet
	 * @param integer $intSorting
	 * @return void;
	 */
	public function insertAttributeRecord($arrSet, $intSorting = 0)
	{
		$arrSet['sorting'] = $intSorting;
		
		$this->Database->prepare("INSERT INTO tl_product_attributes %s")->set($arrSet)->execute();		
	
		return;
	}
	
	/**
	 * Returns all allowed product types as array.
	 * 
	 * @todo returns string in case of error, should return array
	 *
	 * @access public
	 * @param object DataContainer $dc
	 * @return array
	 */
	public function getProductTypes(DataContainer $dc)
	{
		$arrOptions = array();

		$objProductTypes = $this->Database->execute("SELECT name, alias FROM tl_product_types");
		
		if($objProductTypes->numRows < 1)
		{
			return array();
		}								

		while($objProductTypes->next())
		{
			$arrOptions[$objProductTypes->alias] = $objProductTypes->name;
		}

		return $arrOptions;
	}

	
	/** 
	 * Get the next sorting value if it exists for a given table.
	 * 
	 * @access public
	 * @param string $strTable
	 * @return integer;
	 */
	public function getNextSortValue($strTable)
	{
		if($this->Database->fieldExists('sorting', $strTable))
		{
			$objSorting = $this->Database->prepare("SELECT MAX(sorting) as maxSort FROM " . $strTable)
										 ->execute();
			
			return $objSorting->maxSort + 128;
		}
		
		return 0;
	}


	public function changeColumn($varValue, DataContainer $dc)
	{
		
		$objField = $this->Database->prepare("SELECT id, type, name FROM tl_product_attributes WHERE id=?")
				->limit(1)
				->execute($dc->id);
						
		if ($objField->numRows == 0)
		{
				return $varValue;
		}
	
		$fieldName = $objField->name;
		$fieldType = $objField->type;
		
		if ($varValue != $fieldType)
		{
			if ($varValue != $fieldType)
			{
				$this->Database->execute(sprintf($this->createColumnStatement, $fieldName, $this->sqlDefColumn[$varValue]));
			}
		}
		
		return $varValue;
	}

	
	/**
	 * Row label.
	 *
	 * @access public
	 * @param array $row
	 * @param string $label
	 * @param object $dc
	 * @return string
	 */
	public function getRowLabel($row, $label = '')
	{
		
		$this->initializeFields();
		
		//$output = '<div><span><img src="' . $row['thumbnail_image'] . '" width="100" alt="' . $row['name'] . '" align="left" style="padding-right: 8px;" /><strong>' . $row['name'] . '</strong></span><div><span style="color:#b3b3b3;"><strong>$' . $row['price'] . '</strong></span></div><br /><br /><div><em>Categories: ' . $this->getCategoryList(deserialize($row['pages'])) . '</em></div></div> ';
		
		$key = $row['visibility'] ? 'published' : 'unpublished';
		
		$arrImages = explode(',', $row['main_image']);
		
		$thumbnail = strlen($row['main_image']) > 0 ? $GLOBALS['TL_CONFIG']['isotope_upload_path'] . '/' . $GLOBALS['TL_CONFIG']['isotope_base_path'] . '/' . substr($row['alias'], 0, 1) . '/' . $row['alias'] . '/images/' . $GLOBALS['TL_LANG']['MSC']['gallery_thumbnail_images_folder'] . '/' . $arrImages[0] : ' width="50" height="50';
		
		//$thumbnail = $GLOBALS['TL_CONFIG']['isotope_upload_path'] . '/' . $GLOBALS['TL_CONFIG']['isotope_base_path'] . '/' . substr($row['alias'], 0, 1) . '/' . $row['alias'] . '/images/' . $GLOBALS['TL_LANG']['MSC']['gallery_thumbnail_images_folder'] . '/' . $arrImages[0];
		
		$output = '<div style="margin-top:5px!important;margin-bottom:0px!important;" class="cte_type ' . $key . '"><div><span><img src="' . $thumbnail . '" alt="' . $row['name'] . '" align="left" style="padding-right: 8px;" /><strong>' . $row['name'] . '</strong></span><div><span style="color:#b3b3b3;"><strong>' . $this->Isotope->formatPriceWithCurrency($row['price']) . '</strong></span></div><br /><br /><div><em>' . $GLOBALS['TL_LANG']['tl_product_data']['pages'][0] . ': ' . $this->getCategoryList(deserialize($row['pages'])) . '</em></div></div></div> ';
		
		$fields = array();
		
		return $output;
	}
	
	/**
	 * Autogenerate an article alias if it has not been set yet
	 * @param mixed
	 * @param object
	 * @return string
	 */
	public function generateAlias($varValue, DataContainer $dc, $id=0)
	{
		//For import needs, this is an override of the current record ID because when importing we're
		//not utlizing the DataContainer.  We should separate these functions with an intermediary function so that this logic
		//which is repeated across various other functions can be fed just an integer value instead of the more specific
		//DataContainer and its corresponding values.
		if($id!=0)
		{
			$intId = $id;
		}else{
			$intId = $dc->id;
		}
		
		
		$autoAlias = true;

		// Generate alias if there is none
		if (!strlen($varValue))
		{
			$objProductName = $this->Database->prepare("SELECT name FROM tl_product_data WHERE id=?")
									   ->limit(1)
									   ->execute($intId);

			$autoAlias = true;
			$varValue = standardize($objProductName->name);
		}

		$objAlias = $this->Database->prepare("SELECT id FROM tl_product_data WHERE id=? OR alias=?")
								   ->execute($intId, $varValue);

		// Check whether the page alias exists
		if ($objAlias->numRows > 1)
		{
			if (!$autoAlias)
			{
				throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
			}

			$varValue .= '.' . $intId;
		}

		return strtolower($varValue);
	}
	
	/**
	 * Autogenerate an article alias if it has not been set yet
	 * @param mixed
	 * @param object
	 * @return string
	 */
	public function generateSKU($varValue, DataContainer $dc, $id=0)
	{
		//For import needs, this is an override of the current record ID because when importing we're
		//not utlizing the DataContainer.  We should separate these functions with an intermediary function so that this logic
		//which is repeated across various other functions can be fed just an integer value instead of the more specific
		//DataContainer and its corresponding values.
		if($id!=0)
		{
			$intId = $id;
		}else{
			$intId = $dc->id;
		}
		
		$autoAlias = true;

		// Generate alias if there is none
		if (!strlen($varValue))
		{
			$objProductName = $this->Database->prepare("SELECT id, new_import, name, sku FROM tl_product_data WHERE id=?")
									   ->limit(1)
									   ->execute($intId);

			$autoAlias = true;
			
			if($objProductName->new_import!=1)
			{
				if(!strlen($objProductName->sku))
				{
					$varValue = standardize($objProductName->product_name);
				}
			}
		}

		$objAlias = $this->Database->prepare("SELECT id FROM tl_product_data WHERE id=? OR sku=?")
								   ->execute($intId, $varValue);

		// Check whether the page alias exists
		if ($objAlias->numRows > 1)
		{
			if (!$autoAlias)
			{
				throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
			}

			$varValue .= '_' . $intId;
		}

		return $varValue;
	}
	
	/**
	 *
	 * @param mixed
	 * @param object
	 * @return string
	 */
	public function generateTeaser($varValue, DataContainer $dc, $id=0, $strMode='')
	{
		//For import needs, this is an override of the current record ID because when importing we're
		//not utlizing the DataContainer.  We should separate these functions with an intermediary function so that this logic
		//which is repeated across various other functions can be fed just an integer value instead of the more specific
		//DataContainer and its corresponding values.
		if($id!=0)
		{
			$intId = $id;
		}else{
			$intId = $dc->id;
		}
		
		$string = substr($varValue, 0, $GLOBALS['TL_LANG']['MSC']['teaserLength']);
							
		if(!strpos($string, "."))
		{
			//Get the position of the first period after the first X number of characters
			$intFirstPeriod = strpos($varValue, ".", $intLength);
			
			$intFirstPeriod++;
			
			$string = substr($varValue, 0, $intFirstPeriod);
		}
		
		if(strlen($string) < 1)
		{
			return '';
		}
			
		$char = strtolower(strlen($string));
								
		while ($char > 0)
		{
			if ($string{$char} == ".")
			{
				break;
			}else{
			
				$char = $char - 1;
			}
		}
		
		$char++;
		
		$string = substr($string, 0, $char); 	
							
		$objCurrentTeaser = $this->Database->prepare("SELECT teaser FROM tl_product_data WHERE id=?")
										   ->limit(1)
										   ->execute($intId);
		
		if($objCurrentTeaser->numRows > 0)
		{
			if(strlen($objCurrentTeaser->teaser) < 1)
			{
				$this->Database->prepare("UPDATE tl_product_data SET teaser=? WHERE id=?")
								->execute($string, $intId);
			}
		}
		
		if($strMode!='import')
		{
			return $varValue;
		}else{
			return $string;
		}
	}
	
	/**
	 * Produce a list of categories for the backend listing
	 *
	 * @param variant
	 * @return string
	 */
	private function getCategoryList($varValue)
	{
		if(!is_array($varValue) || sizeof($varValue) < 1)
		{
			return $GLOBALS['TL_LANG']['MSC']['noCategoriesAssociated'];
		}
		
		$objCategories = $this->Database->prepare("SELECT title FROM tl_page WHERE id IN (" . join(",", $varValue) . ")")
										->execute();
		
		if($objCategories->numRows < 1)
		{
			return $GLOBALS['TL_LANG']['MSC']['noAssociatedCategories'];
		}
		
		$arrCategories = $objCategories->fetchEach('title');
		
		return join(", ", $arrCategories);
	}
	
	/**
	 * Standardize an attribute title to valid mysql field characters and case
	 *
	 * @param string
	 * @return string
	 */
	public static function mysqlStandardize($strName)
	{
		$varValue = utf8_romanize($strName);
		
		$varValue = preg_replace('/[^a-zA-Z0-9 _-]+/i', '', $varValue);
		$varValue = preg_replace('/ +/i', '_', $varValue);
		
		return $varValue;
	}
	
	
	/**
	 * Wrapper for the Category-Attribute-Product associative table logic.  Grabs all necessary values in order to update the CAP table.
	 *
	 * @param string
	 * @param object
	 * @return string
	 */
	public function saveProductToCategories($varValue, DataContainer $dc, $id=0)
	{	
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
		
		// New way of storing cap_aggregate. One product per row!! .  This nukes all and redoes them each time the product is saved.  Much more simple.
		$arrNewPageList = deserialize($varValue);
		
		
		$this->Database->prepare("DELETE FROM tl_product_to_category WHERE product_id=?")->execute($intId);
		
		if (is_array($arrNewPageList) && count($arrNewPageList))
		{
			$time = time();
			$arrQuery = array();
			$arrValues = array();
			
			$intSorting = $this->getNextSortValue('tl_product_to_category');
			
			foreach( $arrNewPageList as $intPage )
			{
				$arrQuery[] = '(?, ?, ?, ?)';
				
				$arrValues[] = $intPage;
				$arrValues[] = $intSorting;
				$arrValues[] = $time;
				$arrValues[] = $intId;
				$intSorting+=128;
			}
			
			if (count($arrQuery))
			{					   
				$this->Database->prepare("INSERT INTO tl_product_to_category (pid, sorting, tstamp, product_id) VALUES ".implode(', ', $arrQuery))->execute($arrValues);
			}
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
		}else{
			$intId = $dc->id;
		}

						
		//Get the current attribute set		
		$objAttributeID = $this->Database->prepare("SELECT id FROM tl_product_attributes WHERE field_name=?")
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
		$objRecordValues = $this->Database->prepare("SELECT pages, " . $dc->field . " FROM tl_product_data WHERE id=?")
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
						$objProductsAssociatedWithFilterValue = $this->Database->prepare("SELECT id, pages FROM tl_product_data WHERE " . $dc->field . "=?")->execute($varCurrValue);
						
												
						if($objProductsAssociatedWithFilterValue->numRows < 1)	//if there are no occurrences of this filter value in any product, then ok.
						{
							unset($arrExistingValues[$key]);
						}else{
						
							$arrOtherProductsPages = $objProductsAssociatedWithFilterValue->fetchEach('pages');	
														
							$blnPreserveFilterValue = false;		//reset every row.  if we end up false at the end we need to unset.
							
							foreach($arrOtherProductsPages as $pageRow)
							{	
								$rowInfo = deserialize($pageRow);
								
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
				
		return;
	}
	
	
	public function getDefaultDca()
	{
		$this->loadLanguageFile('tl_product_data');
		
		return array
		(
			'config' => array 
			(
				'dataContainer'               => 'Table',
				'switchToEdit'                => true, 
				'enableVersioning'            => false
			),
			
			'list' => array
			(
				'sorting' => array
				(
					'mode'                    => 4,
					'flag'                    => 12,
					'panelLayout'             => 'filter,limit;search,sort',
					'headerFields'            => array('name', 'tstamp')
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
						'label'               => &$GLOBALS['TL_LANG']['tl_product_data']['edit'],
						'href'                => 'act=edit',
						'icon'                => 'edit.gif',
					),
					'copy' => array
					(
						'label'               => &$GLOBALS['TL_LANG']['tl_product_data']['copy'],
						'href'                => 'act=copy',
						'icon'                => 'copy.gif'
					),
					'cut' => array
					(
						'label'               => &$GLOBALS['TL_LANG']['tl_product_data']['cut'],
						'href'                => 'act=paste&amp;mode=cut',
						'icon'                => 'cut.gif',
						'attributes'          => 'onclick="Backend.getScrollOffset();"'
					), 
					'delete' => array
					(
						'label'               => &$GLOBALS['TL_LANG']['tl_product_data']['delete'],
						'href'                => 'act=delete',
						'icon'                => 'delete.gif',
						'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
					),
					'show' => array
					(
						'label'               => &$GLOBALS['TL_LANG']['tl_product_data']['show'],
						'href'                => 'act=show',
						'icon'                => 'show.gif'
					)
				),
			),
			
			'palettes' => array
			(
			),
			
			'subpalettes' => array
			(
			),
			
			'fields' => array
			(
			
			)
		);
	}
		
	
	private function generateTitle($strFormat, $values)
	{
		$fields = $GLOBALS['TL_DCA']['tl_product_data']['list']['label']['fields'];
		preg_match_all('/{{([^}]+)}}/', $strFormat, $matches);
		//$strFormat = '';
		foreach ($matches[1] as $match)
		{
			$params = split('::', $match);
			$fieldConf = $GLOBALS['TL_DCA']['tl_product_data']['fields'][$params[0]];
			if ($fieldConf)
			{	
				$replace = $values[$params[0]];
				if ($params[1])
				{
					switch ($fieldConf['eval']['isotope']['type'])
					{
						case 'file':
								if ($fieldConf['eval']['isotope']['showImage'])
								{ 
									$replace = $this->generateThumbnail($replace, $params[1], $fieldConf['label'][0]);
								}
								break;

						case 'checkbox':
								// only use image if checkbox == true
								$replace = ($replace ? $this->generateThumbnail($replace, $params[1], $fieldConf['label'][0]) : '');
								break;

						default:;

					}					
				}
				$strFormat = str_replace('{{'.$match.'}}', $replace, $strFormat);
			}
		}
		return $strFormat;
	}


	private function generateThumbnail($value, $query, $label)
	{
		// parse query parameters if set
		parse_str($query, $params);
		$src = $params['src'] ? $params['src'] :  $value;

		if (strpos($src, '/') === false)
		{
			$src = sprintf('system/themes/%s/images/%s', $this->getTheme(), $src);
		}

		if (!file_exists(TL_ROOT.'/'.$src))
		{
			return '';
		}

		//$size = getimagesize(TL_ROOT.'/'.$src);
		return '<img src="' . $this->getImage($src, $params['w'], $params['h']) . '" alt="'.specialchars($label).'" />';

	}
    
	private function formatTitle($value, &$fieldConf)
	{
		if (strlen($value))
		{
			switch ($fieldConf['eval']['isotope']['formatFunction'])
			{
				case 'string':
						$value = sprintf($fieldConf['eval']['isotope']['formatStr'], $value);
						break;
						
				case 'number':
						$decimalPlaces = is_numeric($fieldConf['eval']['catalog']['formatStr']) ? 
								intval($fieldConf['eval']['catalog']['formatStr']) : 
								0;
						$value = number_format($value, $decimalPlaces, 
								$GLOBALS['TL_LANG']['MSC']['decimalSeparator'],
								$GLOBALS['TL_LANG']['MSC']['thousandsSeparator']);
						break;
						
				case 'money':
						$value = money_format($fieldConf['eval']['isotope']['formatStr'], $value);
						break;
						
				case 'date':
						$value = date($fieldConf['eval']['isotope']['formatStr'], $value);
						break;
						
				default:
						if ($fieldConf['eval']['rgxp'] == 'date' || $fieldConf['eval']['rgxp'] == 'datim')
						{
								$value = date($GLOBALS['TL_CONFIG'][$fieldConf['eval']['rgxp'].'Format'], $value);
						}
			}
			
			if ($fieldConf['eval']['isotope']['type'] == 'checkbox' && $value)
			{
				$value = $fieldConf['label'][0];
			}
		}
				
		return $value;
	}
	
	public function importAlternateSourceToCollection($varValue, DataContainer $dc)
	{
		$objTableField = $this->Database->prepare("SELECT list_source_table FROM tl_product_attributes WHERE id=?")
										->execute($dc->id);
		
		
		
		if($objTableField->numRows < 1)
		{
			return $varValue;
		}
		
		$objAlternateSourceData = $this->Database->prepare("SELECT id, " . $varValue . " FROM " . $objTableField->list_source_table)
												 ->execute();
												 
		if($objAlternateSourceData->numRows < 1)
		{
			return $varValue;
		}
		
		$arrAlternateSourceData = $objAlternateSourceData->fetchAllAssoc();
		
		foreach($arrAlternateSourceData as $row)
		{
			$arrCollection[] = array
			(
				'value'	=> $row['id'],
				'label'	=> $row[$varValue]
			);
		}
				
						
		$this->Database->prepare("UPDATE tl_product_attributes SET option_list=?, use_alternate_source=0 WHERE id=?")->execute(serialize($arrCollection), $dc->id);
	
		return $varValue;
	}
	
	
	
	/**
	 * Re-generate tl_product_to_category from pages field.
	 * 
	 * @access public
	 * @return void
	 */
	public function repairCAP($dc)
	{
		// Delete all
		$this->Database->prepare("TRUNCATE tl_product_to_category")->execute();	//Truncate to reset index value.
		
		$objProducts = $this->Database->execute("SELECT id,pages FROM " . $objAttributeSet->storeTable);
	
		$time = time();
		$arrQuery = array();
		$arrValues = array();
		
		while( $objProducts->next() )
		{
			$arrPages = deserialize($objProducts->pages);
			
			if (is_array($arrPages) && count($arrPages))
			{
				foreach( $arrPages as $intPage )
				{
					$arrQuery[] = '(?, ?, ?, ?, ?, ?, ?)';
					
					$arrValues[] = $intPage;
					$arrValues[] = '0';
					$arrValues[] = $time;
					$arrValues[] = $objProducts->id;
				}
			}
		}
		
		if (count($arrQuery))
		{
			$this->Database->prepare("INSERT INTO tl_product_to_category (pid, sorting, tstamp, product_id) VALUES ".implode(', ', $arrQuery))->execute($arrValues);
		}

		
		$this->redirect(str_replace('key=repairCAP', '', $this->Environment->request));
	}
		
}


?>