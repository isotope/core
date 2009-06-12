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
	private function createTable($storeTable)
	{
		$this->Database->execute(sprintf($this->createTableStatement, $storeTable));
	}

	private function alterTable($storeTable, $prevStoreTable)
	{
		$this->Database->execute(sprintf("ALTER TABLE `%s` RENAME TO `%s`", $prevStoreTable, $storeTable));
	}

	private function dropTable($storeTable)
	{
		$this->Database->execute(sprintf($this->dropTableStatement, $storeTable));
	}


	protected $sqlDef = array
	(
		'integer'        => "int(10) NULL default NULL",
		'decimal'       => "double NULL default NULL",
		'shorttext'		=> "varchar(128) NOT NULL default ''",
		'text'          => "varchar(255) NOT NULL default ''",
		'longtext'      => "text NULL",
		'datetime'		=>	"int(10) unsigned NOT NULL default '0'",
//		'date'          => "varchar(10) NOT NULL default ''",
		'select'        => "int(10) NOT NULL default 0",
		//'tags'          => "text NULL",
		'checkbox'      => "char(1) NOT NULL default ''",
		'options'		=> "text NULL",
		//'url'           => "varchar(255) NOT NULL default ''",
		'file'          => "text NULL",
		'media'			=> "varchar(255) NOT NULL default '0'",
		//added by thyon
		//'alias'       => "varchar(64) NOT NULL default ''",
		//added by andreas.schempp
		//'taxonomy'		=> "text NULL",
	);
	
	protected $arrForm = array();
	protected $arrFields = array();
	protected $arrTypes = array('text','password','textarea','select','radio','checkbox','upload', 'hidden');
	protected $arrList = array ('tstamp','pages','new_import'/*,'add_audio_file','add_video_file'*/);	//Basic required fields
	protected $arrDefault = array ('id', 'tstamp');
	protected $arrCountMax = array();
	protected $arrCountFree = array();
	protected $arrData = array();

	protected $systemColumns = array('id', 'pid', 'sorting', 'tstamp');
	
	protected $renameColumnStatement = "ALTER TABLE %s CHANGE COLUMN %s %s %s";
	
	protected $createColumnStatement = "ALTER TABLE %s ADD %s %s";
	
	protected $dropColumnStatement = "ALTER TABLE %s DROP COLUMN %s";

	protected $createTableStatement = "
		CREATE TABLE `%s` (
			`id` int(10) unsigned NOT NULL auto_increment,
		    `pid` int(10) unsigned NOT NULL default '0',
		    `sorting` int(10) unsigned NOT NULL default '0',
			`tstamp` int(10) unsigned NOT NULL default '0',
			`pages` text NULL,
			`new_import` char(1) NOT NULL default '0',
		    `audio_source` varchar(32) NOT NULL default '',
  			`audio_jumpTo` text NULL,
  			`audio_url` varchar(255) NOT NULL default '',
			`video_source` varchar(32) NOT NULL default '',
  			`video_jumpTo` varchar(255) NOT NULL default '',
  			`video_url` text NULL,
			`add_audio_file` char(1) NOT NULL default '0',
			`add_video_file` char(1) NOT NULL default '0',
			`option_collection` text NULL,
			PRIMARY KEY  (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8";
		
	protected $dropTableStatement = "DROP TABLE `%s`";

	protected $strCurrentStoreTable;

	protected $arrPreExistingRecordInfo;

	public function initializeDCA($strTable)
	{
		
		$this->import('Database');
		$this->import('Input');

		$objTable = $this->Database->prepare("SELECT storeTable FROM tl_product_attribute_sets where id=?")
				->limit(1)
				->execute(CURRENT_ID); // you can use $this->Input->get('id') as well I guess.
					
		// load dca and/or languages
		$this->loadProductCatalogDCA(CURRENT_ID);

		return $objTable->storeTable;
	}
	
	/**
	 * ProductCatalog HOOKS: loadProductCatalogDCA, ValidateFormField, ProcessFormData 
	 */	
	
	public function loadProductCatalogDCA($formid)
	{
		
		$this->initializeAttributeSet($formid);
		
		//var_dump($this->arrFields);

		$storeTable = $this->arrForm['storeTable'];
		
		$this->strCurrentStoreTable = $storeTable;
		
		// Import labels
		$GLOBALS['TL_LANG'][$storeTable] = &$GLOBALS['TL_LANG']['tl_product_data'];

		// setup global array first
		$GLOBALS['TL_DCA'][$storeTable] = array
		(
		
			// Config
			'config' => array
			(
				'dataContainer'               => 'Table',
				'ptable'                      => 'tl_product_attribute_sets',
		//		'notEditable'                 => true,
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
					'mode'                    => 4,
					'fields'                  => array('sorting'),
					'flag'                    => 1,
					'panelLayout'             => 'sort,filter;search,limit',
					'headerFields'            => array('name'),
					'child_record_callback'   => array('ProductCatalog','getRowLabel'),
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
		
		$GLOBALS['TL_DCA'][$storeTable]['list']['label']['fields'] = array_merge($this->arrList, count($arrFieldCollection) ? $arrFieldCollection : array());
		$GLOBALS['TL_DCA'][$storeTable]['list']['label']['format'] = '<span style="color:#b3b3b3; padding-right:3px;">[%s]</span>'
					. (count($this->arrFields) ? join(', ', array_fill(0,count($this->arrFields),'%s')) : '');
		$GLOBALS['TL_DCA'][$storeTable]['list']['label']['label_callback'] = array('ProductCatalog','getRowLabel');

		
		// add palettes
		$GLOBALS['TL_DCA'][$storeTable]['palettes']['__selector__'] = array('add_audio_file','add_video_file');
		$GLOBALS['TL_DCA'][$storeTable]['palettes']['default'] = join(',',$GLOBALS['TL_DCA'][$storeTable]['list']['label']['fields']) . ';option_collection';
		$GLOBALS['TL_DCA'][$storeTable]['subpalettes']['add_audio_file'] = 'audio_source,audio_jumpTo,audio_url';
		$GLOBALS['TL_DCA'][$storeTable]['subpalettes']['add_video_file'] = 'video_source,video_jumpTo,video_url';
		
		// first add common DCA: tstamp, ip
		$GLOBALS['TL_DCA'][$storeTable]['fields']['tstamp'] =
		array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_data']['tstamp'],
			'search'                  => false,
			'sorting'				  => true,
			'flag'                    => 6,
			'eval'                    => array('rgxp'=>'datim') 
		);	
			
		$GLOBALS['TL_DCA'][$storeTable]['fields']['pages'] =
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
				array('ProductCatalog','executeCAPAggregation')
			)
			//'explanation'             => 'pageCategories'
		);	
		
		$GLOBALS['TL_DCA'][$storeTable]['fields']['add_audio_file'] = array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_data']['add_audio_file'],
			'default'				  => 'internal',
			'filter'                  => false,
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange'=>true)
		);
		
		$GLOBALS['TL_DCA'][$storeTable]['fields']['add_video_file'] = array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_data']['add_video_file'],
			'default'				  => 'internal',
			'filter'                  => false,
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange'=>true)
		);
	
		$GLOBALS['TL_DCA'][$storeTable]['fields']['audio_source'] = array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_data']['audio_source'],
			'default'                 => 'internal',
			'filter'                  => false,
			'inputType'               => 'radio',
			'options'                 => array('internal', 'external'),
			'reference'               => &$GLOBALS['TL_LANG']['tl_product_data'],
			'eval'                    => array('helpwizard'=>true)
		);
				
		$GLOBALS['TL_DCA'][$storeTable]['fields']['audio_jumpTo'] = array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_data']['audio_jumpTo'],
			'inputType'               => 'fileTree',
			'eval'                    => array('fieldType'=>'radio', 'files'=>true, 'helpwizard'=>true)
		);
		
		$GLOBALS['TL_DCA'][$storeTable]['fields']['audio_url'] = array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_data']['audio_url'],
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('decodeEntities'=>true, 'maxlength'=>255)
		);
		
		$GLOBALS['TL_DCA'][$storeTable]['fields']['video_source'] = array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_data']['video_source'],
			'default'                 => 'internal',
			'filter'                  => false,
			'inputType'               => 'radio',
			'options'                 => array('internal', 'external'),
			'reference'               => &$GLOBALS['TL_LANG']['tl_product_data'],
			'eval'                    => array('helpwizard'=>true)
		);		
		$GLOBALS['TL_DCA'][$storeTable]['fields']['video_jumpTo'] = array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_data']['video_jumpTo'],
			'inputType'               => 'fileTree',
			'eval'                    => array('fieldType'=>'radio', 'files'=>true, 'helpwizard'=>true)
		);
		
		$GLOBALS['TL_DCA'][$storeTable]['fields']['video_url'] = array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_data']['video_url'],
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('decodeEntities'=>true, 'maxlength'=>255)
		);
		
		$GLOBALS['TL_DCA'][$storeTable]['fields']['option_collection'] = array
		(
			'label'					  => &$GLOBALS['TL_LANG']['tl_product_data']['option_collection'],
			'inputType'				  => 'productOptionWizard',
			'save_callback'			  => array
			(
				array('ProductCatalog','saveProductOptions')
			)
		);
		
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
					$inputType = 'checkbox';
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
					
				case 'checkbox':
					$inputType = 'checkbox';
					//$eval['multiple'] = true;
					break;
				
				case 'select':
					$inputType = 'select';
					
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
					
				default:
					$inputType = $field['type'];
					break;
			}
			
			$filter = ($this->arrForm['useFilter'] && $this->arrForm['filterField'] == $key);

			$GLOBALS['TL_DCA'][$storeTable]['fields'][$key] =
				array
				(
					'label'           => array($field['name'], $field['description']),
					'inputType'				=> $inputType,
					'search'          => !$filter,
					'filter'         	=> $filter,
					'eval'            => $eval
				);
							
			if (strlen($field['options'])) 
			{
				$options = deserialize($field['options']);
				foreach ($options as $option) {
					$optionList[$option['value']] = $option['label'];
				}
				$GLOBALS['TL_DCA'][$storeTable]['fields'][$key]['options'] = array_keys($optionList);
				$GLOBALS['TL_DCA'][$storeTable]['fields'][$key]['reference'] = $optionList;
				unset($optionList);

			}
			
			if(strlen($strForeignKey) && $field['type'] == 'select')
			{
				$GLOBALS['TL_DCA'][$storeTable]['fields'][$key]['foreignKey'] = $strForeignKey;
				$strForeignKey = "";
			}
			
			if(is_array($arrValues) && $field['type'] == 'select')
			{
				$GLOBALS['TL_DCA'][$storeTable]['fields'][$key]['eval']['includeBlankOption'] = true;
				$GLOBALS['TL_DCA'][$storeTable]['fields'][$key]['options'] = $arrValues;
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
																		
					$GLOBALS['TL_DCA'][$storeTable]['fields'][$key]['load_callback'] = $arrCallbacks;
					
				}else{
					$GLOBALS['TL_DCA'][$storeTable]['fields'][$key]['load_callback'] = array(
						explode(".", $field['load_callback'])
					);
				}
			}
			
			if (!empty($field['save_callback']))
			{
				$arrCallbackSet = explode(',',$field['save_callback']);
								
				if(is_array($arrCallbackSet))
				{
					foreach($arrCallbackSet as $callback)
					{
						$arrCallbacks[] = explode(".", $callback);
					}
																		
					$GLOBALS['TL_DCA'][$storeTable]['fields'][$key]['save_callback'] = $arrCallbacks;
					
				}else{
					$GLOBALS['TL_DCA'][$storeTable]['fields'][$key]['save_callback'] = array(
						explode(".", $field['save_callback'])
					);
				}
				
			}
			
		}
		
		
	}
	
	public function saveProductOptions($varValue, DataContainer $dc)
	{

		$arrValues = deserialize($varValue);
		
		echo $this->Input->post($dc->field . '_values');
		exit;
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
		
		$objIsNewImport = $this->Database->prepare("SELECT id, pages, product_name, product_sku, product_alias, product_description, product_teaser, product_images FROM " . $dc->table . " WHERE new_import=1 AND id=?")
										 ->limit(1)
										 ->execute($dc->id);
		
		
		
		if($objIsNewImport->numRows > 0)
		{		
			//$arrNewImports = $objIsNewImport->fetchAllAssoc();
			
			/*foreach($arrNewImports as $record)
			{
			*/
				if(strlen($objIsNewImport->product_sku) < 1)
				{
					$strSKU = $this->generateSKU('', $dc, $dc->id);
				}
				else
				{
					$strSKU = $objIsNewImport->product_sku;
				}
				
				if(strlen($objIsNewImport->product_alias) < 1)
				{
					$strAlias = $this->generateAlias('', $dc, $dc->id);
				}
				else
				{
					$strAlias = $objIsNewImport->product_alias;
				}
				
				if(strlen($objIsNewImport->product_teaser) < 1)
				{
					$strTeaser = $this->generateTeaser($objIsNewImport->product_description, $dc, $dc->id, 'import');
				}
				else
				{
					$strTeaser = $objIsNewImport->product_teaser;
				}
				
				//$strSerializedValues = $this->prepareCategories($record['pages'], $dc, $record['id']);
	
				//$this->MediaManagement->thumbnailImportedImages($objIsNewImport->product_images, $dc, $dc->id, $strAlias);
				
				//$this->MediaManagement->thumbnailCurrentImageForListing($objIsNewImport->product_images, $dc);
								
				$this->Database->prepare("UPDATE " . $dc->table . " SET product_sku=?, product_alias=?, product_teaser=?, pages=?, new_import=0 WHERE id=?")
							   ->execute($strSKU, $strAlias, $strTeaser, $objIsNewImport->pages, $dc->id);
				
								
				$this->executeCAPAggregation($objIsNewImport->pages, $dc, $dc->id);
				
				//Not yet..
				//$this->executePFCAggregation($objIsNewImport->pages, $dc, $dc->id);
			/*}
			
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
				$this->$callback[0]->$callback[1]($dc, $storeTable);
			}
		}
		
			
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
		//Potentially the delimiter could be different.  May want to try and figure it out autommatically.
		if(!is_array(deserialize($varValue)))
		{
			if(strpos($varValue, ','))
			{
				$arrPages = explode(',', $varValue);
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
			$intID = $id;
		}else{
			$intID = $dc->id;
		}
		
		
		$this->Database->prepare("UPDATE " . $dc->table . " SET new_import=? WHERE id=? and new_import=1")
								->execute(0, $intID);
	}
	
	public function generateMappingAttributeList()
	{
		$arrOptions = array();
		$arrAttributes = array();
		
		$objAttributeSets = $this->Database->prepare("SELECT id, name, storeTable FROM tl_product_attribute_sets")
										   ->execute();
		
		if($objAttributeSets->numRows < 1)
		{
			return array();
		}

		$arrAttributeSetData = $objAttributeSets->fetchAllAssoc();
		
		foreach($arrAttributeSetData as $set)
		{
				$objAttributes = $this->Database->prepare("SELECT name FROM tl_product_attributes WHERE pid=?")
												->execute($set['id']);
								
				if($objAttributes->numRows < 1)
				{
					return false;
				}
				
				$arrAttributes = $objAttributes->fetchAllAssoc();
				
				//var_dump($arrAttributes);
				foreach($arrAttributes as $attribute)
				{						
					$arrOptions[$set['name']][] = array
					(
						'value' => strtolower($this->mysqlStandardize($attribute['name'])),
						'label' => $attribute['name']
					);
				}
								
				
		}
		
		
		
		//var_dump($arrOptions);
				
		return $arrOptions;
	}
	
	public function renameTable($varValue, DataContainer $dc)
	{		
		if (!preg_match('/^[a-z_][a-z\d_]*$/iu', $varValue))
		{
			throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['invalidStoreTable'], $varValue));
		}
		
		$objType = $this->Database->prepare("SELECT storeTable, noTable FROM tl_product_attribute_sets WHERE id=?")
								  ->limit(1)
								  ->execute($dc->id);
				
		if ($objType->numRows == 0)
		{
			return $varValue;
		}
			
		if ($objType->noTable)
		{
			if (!$this->Database->tableExists($varValue))
			{
					throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['tableDoesNotExist'], $varValue));
			}
			
			return $varValue;
		}
		
		if ((!strlen($objType->storeTable) || $objType->storeTable != $varValue) && $this->Database->tableExists($varValue))
		{
			throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['tableExists'], $varValue)); 
		}
		elseif (strlen($objType->storeTable))
		{
			$statement = $this->alterTable($varValue, $objType->storeTable);
		}
		else
		{
			$statement = $this->createTable($varValue);
		}
		
		return $varValue;
	}
	
	private function initializeAttributeSet($formId) 
	{
		$objForm = $this->Database->prepare("SELECT * FROM tl_product_attribute_sets WHERE id=?")
											->limit(1)
											->execute($formId);
		
		if ($objForm->numRows)
		{
			$this->arrForm = $objForm->fetchAssoc();
			$this->initializeFields($formId);


			$_SESSION['isotope']['store_id'] = $this->arrForm['store_id'];
		}

	}
	
	private function initializeFields($formId) 
	{
		$objFields = $this->Database->prepare("SELECT * FROM tl_product_attributes WHERE pid=? ORDER BY sorting ASC")
							->execute($formId);
		
		while ($objFields->next())
		{
			/*if (in_array($objFields->type, $this->arrTypes))	// A good way to double check that you have the attribute types you need for each attribute.
			{*/
			
				$this->arrFields[$objFields->field_name] = $objFields->row();
			/*}*/
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
		
		$objField = $this->Database->prepare("SELECT f.storeTable, ff.pid, ff.id, ff.type, ff.field_name FROM tl_product_attribute_sets f, tl_product_attributes ff WHERE f.id=ff.pid AND ff.id=?")
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
		
		if ($this->Database->fieldExists($objField->field_name, $objField->storeTable))
		{
			if ($objField->field_name != $varValue)
			{
				$statement = sprintf($this->renameColumnStatement, $objField->storeTable, $objField->field_name, $varValue, $this->sqlDef[$fieldType]);
			}
		}
		else
		{
			$statement = sprintf($this->createColumnStatement, $objField->storeTable, $varValue, $this->sqlDef[$fieldType]);
		}
		
		if (strlen($statement))
			$this->Database->execute($statement);
		
		//Create the field name for quick reference in code.
//		$this->Database->prepare("UPDATE tl_product_attributes SET field_name='" . $varValue . "' WHERE id=?")
//					   ->execute($dc->id);
		
		return $varValue;
	}
	
	
	
	public function addDefaultAttribute($varValue, DataContainer $dc, $storeTable, $fieldType)
	{
		if (!preg_match('/^[a-z_][a-z\d_]*$/i', $varValue))
		{
			throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['invalidColumnName'], $varValue));
		}
		if (in_array($varValue, $this->systemColumns))
		{
			throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['systemColumn'], $varValue));
		}
		
		$statement = sprintf($this->createColumnStatement, $storeTable, $varValue, $this->sqlDef[$fieldType]);
		
				
		$this->Database->execute($statement);
		
		return $varValue;
	}
	
	
	public function changeColumn($varValue, DataContainer $dc)
	{
		
		$objField = $this->Database->prepare("SELECT f.storeTable, ff.id, ff.type, ff.name FROM tl_product_attribute_sets f, tl_product_attributes ff WHERE f.id=ff.pid AND ff.id=?")
				->limit(1)
				->execute($dc->id);
						
		if ($objField->numRows == 0 || !strlen($objField->storeTable))
		{
				return $varValue;
		}
		
		$storeTable = $objField->storeTable;
		$field_name = $objField->name;
		$fieldType = $objField->type;
		
		if ($varValue != $fieldType)
		{
			if ($varValue != $fieldType)
			{
				$this->dropColumn($storeTable, $field_name);
				$this->Database->execute(sprintf($this->createColumnStatement, $storeTable, $field_name, $this->sqlDefColumn[$varValue]));
			}
			else if ($this->Database->fieldExists($field_name, $storeTable))
			{
				$this->dropColumn($storeTable, $field_name);	
			}
			
		}
		
		return $varValue;
	}
	
	
	public function loadProductAttributes(DataContainer $dc)
	{
		if (!$this->checkProductCatalog()) 
		{
			return;
		}
		
		$act = $this->Input->get('act');
		switch ($act) 
		{
			case 'deleteAll':
			case 'delete': 
	
				if ($act == 'delete')
				{
					$ids = array($dc->id);
				}
				else
				{
					$session = $this->Session->getData();
					$ids = $session['CURRENT']['IDS'];
				}
				$this->deleteColumn($ids);
				break;
				
			default:;
		}
	}
	
	public function deleteColumn($ids)
	{
			$objType = $this->Database->prepare("SELECT f.field_name, t.storeTable, t.noTable FROM tl_product_attributes f INNER JOIN tl_product_attribute_sets t ON f.pid = t.id WHERE f.id IN (?)")
					->execute(implode(',', $ids));
							
			while ($objType->next())
			{
					$field_name = $objType->field_name;
					$storeTable = $objType->storeTable;
					$noTable = $objType->noTable;
					
					if ($noTable)
					{
							continue;
					}
							
					if ($this->Database->fieldExists($field_name, $storeTable))
					{
							$this->dropColumn($storeTable, $field_name);
					}
			}
	}
	
	public function dropColumn($storeTable, $field_name)
	{
			$this->Database->execute(sprintf($this->dropColumnStatement, $storeTable, $field_name));
	}
	
	public function insertDefaultAttributes(DataContainer $dc, $storeTable)
	{
		$intSorting = 128;
	
		foreach($GLOBALS['ISO_ATTR'] as $arrSet)
		{
			foreach($arrSet as $k=>$v)
			{
				switch($k)
				{
					case 'pid':
						$arrSet[$k] = $dc->id;
						break;
						
					case 'tstamp':
						$arrSet[$k] = time();
						break;
						
					case 'sorting':
						$arrSet[$k] = $intSorting;
						break;
						
					case 'name':
						$arrSet[$k] = strlen($GLOBALS['TL_LANG']['ISO_ATTR'][$arrSet['field_name']][0]) ? $GLOBALS['TL_LANG']['ISO_ATTR'][$arrSet['field_name']][0] : $v;
						break;
						
					case 'description':
						$arrSet[$k] = strlen($GLOBALS['TL_LANG']['ISO_ATTR'][$arrSet['field_name']][1]) ? $GLOBALS['TL_LANG']['ISO_ATTR'][$arrSet['field_name']][1] : $v;
						break;
				}
			}	
			
			
			$this->Database->prepare("INSERT INTO tl_product_attributes %s")->set($arrSet)->execute();
		
			$fieldName = $this->mysqlStandardize($arrSet['name']);
				
			$this->addDefaultAttribute(strtolower($fieldName), $dc, $storeTable, $arrSet['type']);
			
			$intSorting += 128;
		}	
					
		return $storeTable;
	
	}
	
	
	/**
	 * Row label.
	 * 
	 * @todo initializeAttributeSet() is called for each row and continuously fetches the same database results.
	 *
	 * @access public
	 * @param array $row
	 * @param string $label
	 * @param object $dc
	 * @return string
	 */
	public function getRowLabel($row, $label, $dc)
	{
		$this->initializeAttributeSet($dc->id);
		$this->import('Isotope');

		//$output = '<div><span><img src="' . $row['product_thumbnail_image'] . '" width="100" alt="' . $row['product_name'] . '" align="left" style="padding-right: 8px;" /><strong>' . $row['product_name'] . '</strong></span><div><span style="color:#b3b3b3;"><strong>$' . $row['product_price'] . '</strong></span></div><br /><br /><div><em>Categories: ' . $this->getCategoryList(deserialize($row['pages'])) . '</em></div></div> ';
		
		$key = $row['product_visibility'] ? 'published' : 'unpublished';
		
		$arrImages = explode(',', $row['product_images']);
		
		$thumbnail = strlen($row['product_images']) > 0 ? $GLOBALS['TL_CONFIG']['isotope_upload_path'] . '/' . $GLOBALS['TL_CONFIG']['isotope_base_path'] . '/' . substr($row['product_alias'], 0, 1) . '/' . $row['product_alias'] . '/images/' . $GLOBALS['TL_LANG']['MSC']['gallery_thumbnail_images_folder'] . '/' . $arrImages[0] : ' width="50" height="50';
		
		//$thumbnail = $GLOBALS['TL_CONFIG']['isotope_upload_path'] . '/' . $GLOBALS['TL_CONFIG']['isotope_base_path'] . '/' . substr($row['product_alias'], 0, 1) . '/' . $row['product_alias'] . '/images/' . $GLOBALS['TL_LANG']['MSC']['gallery_thumbnail_images_folder'] . '/' . $arrImages[0];
		
		$output = '<div style="margin-top:5px!important;margin-bottom:0px!important;" class="cte_type ' . $key . '"><div><span><img src="' . $thumbnail . '" alt="' . $row['product_name'] . '" align="left" style="padding-right: 8px;" /><strong>' . $row['product_name'] . '</strong></span><div><span style="color:#b3b3b3;"><strong>' . $this->Isotope->formatPriceWithCurrency($row['product_price']) . '</strong></span></div><br /><br /><div><em>' . $GLOBALS['TL_LANG']['tl_product_data']['pages'][0] . ': ' . $this->getCategoryList(deserialize($row['pages'])) . '</em></div></div></div> ';
		
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
			$intID = $id;
		}else{
			$intID = $dc->id;
		}
		
		
		$autoAlias = true;

		// Generate alias if there is none
		if (!strlen($varValue))
		{
			$objProductName = $this->Database->prepare("SELECT product_name FROM " . $this->strCurrentStoreTable . " WHERE id=?")
									   ->limit(1)
									   ->execute($intID);

			$autoAlias = true;
			$varValue = standardize($objProductName->product_name);
		}

		$objAlias = $this->Database->prepare("SELECT id FROM " . $this->strCurrentStoreTable . " WHERE id=? OR product_alias=?")
								   ->execute($intID, $varValue);

		// Check whether the page alias exists
		if ($objAlias->numRows > 1)
		{
			if (!$autoAlias)
			{
				throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
			}

			$varValue .= '.' . $intID;
		}

		return $varValue;
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
			$intID = $id;
		}else{
			$intID = $dc->id;
		}
		
		$autoAlias = true;

		// Generate alias if there is none
		if (!strlen($varValue))
		{
			$objProductName = $this->Database->prepare("SELECT id, new_import, product_name, product_sku FROM " . $this->strCurrentStoreTable . " WHERE id=?")
									   ->limit(1)
									   ->execute($intID);

			$autoAlias = true;
			
			if($objProductName->new_import!=1)
			{
				if(!strlen($objProductName->product_sku))
				{
					$varValue = standardize($objProductName->product_name);
				}
			}
		}

		$objAlias = $this->Database->prepare("SELECT id FROM " . $this->strCurrentStoreTable . " WHERE id=? OR product_sku=?")
								   ->execute($intID, $varValue);

		// Check whether the page alias exists
		if ($objAlias->numRows > 1)
		{
			if (!$autoAlias)
			{
				throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
			}

			$varValue .= '_' . $intID;
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
			$intID = $id;
		}else{
			$intID = $dc->id;
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
							
		$objCurrentTeaser = $this->Database->prepare("SELECT product_teaser FROM " . $this->strCurrentStoreTable . " WHERE id=?")
										   ->limit(1)
										   ->execute($intID);
		
		if($objCurrentTeaser->numRows > 0)
		{
			if(strlen($objCurrentTeaser->product_teaser) < 1)
			{
				$this->Database->prepare("UPDATE " . $this->strCurrentStoreTable . " SET product_teaser=? WHERE id=?")
								->execute($string, $intID);
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
	public function executeCAPAggregation($varValue, DataContainer $dc, $id=0)
	{	
		//For import needs, this is an override of the current record ID because when importing we're
		//not utlizing the DataContainer.  We should separate these functions with an intermediary function so that this logic
		//which is repeated across various other functions can be fed just an integer value instead of the more specific
		//DataContainer and its corresponding values.			

		if($id!=0)
		{
			$intID = $id;
		}
		else
		{
			$intID = $dc->id;
		}
		
		
//		$arrNewPageList = deserialize($varValue);
//		$arrAllPageInfo = array();
						
		$objField = $this->Database->prepare("SELECT id, store_id FROM tl_product_attribute_sets WHERE storeTable=?")
				->limit(1)
				->execute($this->strCurrentStoreTable);
			
		if ($objField->numRows < 1)
		{
			return array();
		}
		
		$id = $objField->id;
		$storeID = $objField->store_id;
		
/*
		$objAllPages = $this->Database->prepare("SELECT pid, product_ids FROM tl_cap_aggregate WHERE attribute_set_id=?")->execute($id);
		
		if($objAllPages->numRows > 0)
		{
			$arrAllPageInfo = $objAllPages->fetchAllAssoc();
		}
*/

				
//		$this->updateCAPAggregate($arrNewPageList, $arrAllPageInfo, $dc, $this->strCurrentStoreTable, $id, $storeID, $intID);
		
		
		
		
		// New way of storing cap_aggregate. One product per row!!
		$arrNewPageList = deserialize($varValue);
		$this->Database->prepare("DELETE FROM tl_cap_aggregate WHERE product_id=? AND storeTable=?")->execute($intID, $this->strCurrentStoreTable);
		
		if (is_array($arrNewPageList) && count($arrNewPageList))
		{
			$time = time();
			$arrQuery = array();
			$arrValues = array();
			
			foreach( $arrNewPageList as $intPage )
			{
				$arrQuery[] = '(?, ?, ?, ?, ?, ?, ?)';
				
				$arrValues[] = $intPage;
				$arrValues[] = '0';
				$arrValues[] = $time;
				$arrValues[] = $this->strCurrentStoreTable;
				$arrValues[] = $intID;
				$arrValues[] = $id;
				$arrValues[] = $storeID;
			}
			
			if (count($arrQuery))
			{
				$this->Database->prepare("INSERT INTO tl_cap_aggregate (pid, sorting, tstamp, storeTable, product_id, attribute_set_id, store_id) VALUES ".implode(', ', $arrQuery))->execute($arrValues);
			}
		}
		
		
	
		return $varValue;
	}
	
/*
		
	public function batchUpdateCAPAggregate()
	{
	
	}
	
*/
	/**
	 * updateCAPAggregate - Update our aggregate reference table which is used to build collections of products out of multiple attribute sets. This logic maintains the records by page of associated products and storeTables.
	 *
	 * @param variant
	 * @param object
	 * @param string
	 *
	 */
/*
	private function updateCAPAggregate($arrPageList, $arrAllPageInfo, DataContainer $dc, $storeTable, $attributeSetID, $storeID, $id=0)
	{
		//For import needs, this is an override of the current record ID because when importing we're
		//not utlizing the DataContainer.  We should separate these functions with an intermediary function so that this logic
		//which is repeated across various other functions can be fed just an integer value instead of the more specific
		//DataContainer and its corresponding values.
		if($id!=0)
		{
			$intID = $id;
		}else{
			$intID = $dc->id;
		}
		
		
		$objCAPInfo = $this->Database->prepare("SELECT id, pid, product_ids FROM tl_cap_aggregate WHERE attribute_set_id=? AND pid IN (" . join(",", $arrPageList) . ")")
										->execute($attributeSetID);
		//var_dump($arrAllPageInfo);
		
		if($objCAPInfo->numRows < 1)
		{
			// Insert into table the association
			foreach($arrPageList as $intPageNum)
			{				
				$arrSet = array();
				$arrProduct = array();
				
				$arrProduct[] = $intID;
				
				$arrSet = array(
					'product_ids'			=> serialize($arrProduct),
					'pid'					=> $intPageNum,
					'storeTable'			=> $storeTable,
					'attribute_set_id' 		=> $attributeSetID,
					'store_id'				=> $storeID
				);
				
				$this->Database->prepare("INSERT INTO tl_cap_aggregate %s")->set($arrSet)->execute();
			}
			
			return;
						
		}
		
		$arrCAPInfo = $objCAPInfo->fetchAllAssoc();
		
		//var_dump($arrCAPInfo);
		
		$arrProducts = array();
					
		// For each existing page that DID in the past have this product ID associated with it, but NOW the submitted list does not include that page id, remove it
		foreach($arrAllPageInfo as $page)
		{
			$arrExistingProducts = array();
			
			//Get the product ID collection of the current existing page
			$arrExistingProducts = deserialize($page['product_ids']);
			
			//If the current existing page id does not exist in the list of pages collected from the form submit, then 
			//remove the product id from the page in question.			
							//If the product id exists in the product list for this page, which is not part of the product page list now...  Remove from the product_ids collection and update.
			if(!in_array($page['pid'], $arrCAPInfo))
			{
				if(in_array($intID, $arrExistingProducts))
				{
					
					$key = array_search($intID, $arrExistingProducts);
									
					//If we find that the product id submitted does, in fact exist in the existing product collection for this page, then we remove it.
					//if(!empty($key))
					//{	
						unset($arrExistingProducts[$key]);
					//
						//var_dump($arrExistingProducts);
						//echo "<br /><br />";
						
						//var_dump($arrExistingProducts);
						$this->Database->prepare("UPDATE tl_cap_aggregate SET product_ids=? WHERE pid=? AND attribute_set_id=?")
									   ->execute(serialize($arrExistingProducts), $page['pid'], $attributeSetID);
						
					//}
				}
			}

		}
		//For each page record already in the table, we grab the product id list and modify it to include this product ID if it doesn't existing in the product ID collection.
		
		foreach($arrCAPInfo as $page)
		{
			//Each page record we start with a fresh products array to update the record.
			$arrExistingProducts = array();
			
			$arrExistingPages[] = $page['pid'];
			// Since these are serialized, we have to deserialize them before we can do any work on the record.
			$arrExistingProducts = deserialize($page['product_ids']);
								
			foreach($arrPageList as $pageToBeUpdated)
			{
				if((int)$pageToBeUpdated==$page['pid'])	//If this page 
				{
					//If the product ID doesn't not already have an association to the current page, then add it to the list of product IDs for that page.
					if(!in_array($intID, $arrExistingProducts))
					{
						$arrExistingProducts[] = $intID;	//add the product id in.
					}
				}				
								
				// Update existing association
				$this->Database->prepare("UPDATE tl_cap_aggregate SET product_ids=? WHERE pid=? AND attribute_set_id=?")
							   ->execute(serialize($arrExistingProducts), $page['pid'], $attributeSetID);
			}			
		}
		
		
		//New Pages to add that aren't in the current collection
		
		foreach($arrPageList as $intPageNum)
		{	
			if(!in_array((int)$intPageNum, $arrExistingPages))
			{
				
				$arrSet = array();
				$arrProduct = array();
				
				$arrProduct[] = $intID;
				
				$arrSet = array(
					'product_ids'			=> serialize($arrProduct),
					'pid'					=> $intPageNum,
					'storeTable'			=> $storeTable,
					'attribute_set_id' 		=> $attributeSetID,
					'store_id'				=> $storeID
				);
				
				$this->Database->prepare("INSERT INTO tl_cap_aggregate %s")->set($arrSet)->execute();
			}
		}			
				
		return;
	}
*/
	
	
	/**
	 * Wrapper for the Product-Filter Collection associative table logic.  Grabs all necessary values in order to update the PFC table.
	 *
	 * @param string
	 * @param object
	 * @return string
	 */
	public function executePFCAggregation($varValue, DataContainer $dc, $id=0)
	{		
		
				
		if(is_null($varValue) || $varValue == 0)
		{
			return $varValue;
		}
		//For import needs, this is an override of the current record ID because when importing we're
		//not utlizing the DataContainer.  We should separate these functions with an intermediary function so that this logic
		//which is repeated across various other functions can be fed just an integer value instead of the more specific
		//DataContainer and its corresponding values.	
		if($id!=0)
		{
			$intID = $id;
		}else{
			$intID = $dc->id;
		}
		
		//Send the pages selected into an array
		//$arrNewPageList = deserialize($varValue);
		$arrAllPageInfo = array();
						
		//Get the current attribute set
		$objAttributeSetID = $this->Database->prepare("SELECT id, store_id FROM tl_product_attribute_sets WHERE storeTable=?")
											->limit(1)
											->execute($this->strCurrentStoreTable);
			
		if ($objAttributeSetID->numRows < 1)
		{
			return $varValue;
		}
		
		
		//Attribute set ID, currently used to narrow down return records from the aggregate table to those based on attribute set.  This 
		//will change when attributes become global!
		$attributeSetID = $objAttributeSetID->id;
		$storeID = $objAttributeSetID->store_id;
		
		$objAttributeID = $this->Database->prepare("SELECT id FROM tl_product_attributes WHERE pid=? AND field_name=?")
										 ->limit(1)
										 ->execute($attributeSetID, $dc->field);
		
		if($objAttributeID->numRows < 1)
		{
			return $varValue;
		}
		
		$attributeID = $objAttributeID->id;
		
	
		//Gather all records pertaining to the current attribute set in the aggregate table
		$objAllPageInfo = $this->Database->prepare("SELECT pid, value_collection FROM tl_pfc_aggregate WHERE attribute_set_id=? AND attribute_id=?")->execute($attributeSetID, $attributeID);
		
		if($objAllPageInfo->numRows > 0)
		{
			//Contains pid which is the reference to a given page, and attribute_id which is the reference to a given filter.
			$arrAllPageInfo = $objAllPageInfo->fetchAllAssoc();
		}
			
		//Get the value submitted for this particular attribute
		$objRecordValues = $this->Database->prepare("SELECT pages, " . $dc->field . " FROM " . $this->strCurrentStoreTable . " WHERE id=?")
													->limit(1)
													->execute($dc->id);
		if($objRecordValues->numRows < 1)
		{
			return $varValue;
		}
		
		$arrNewPageList = deserialize($objRecordValues->pages);
				
		$this->updatePFCAggregate($arrNewPageList, $arrAllPageInfo, $dc, $this->strCurrentStoreTable, $attributeSetID, $attributeID, $storeID, $varValue);
	
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
	private function updatePFCAggregate($arrPageList, $arrAllPageInfo, DataContainer $dc, $storeTable, $attributeSetID, $attributeID, $storeID, $varCurrValue)
	{		
		
		if(sizeof($arrPageList) < 1)
		{		
			$arrPageList[] = 0;
		}
		
		if(empty($varCurrValue) || $varCurrValue==0)
		{
			
			return;
		}
		
		$arrCurrValues[] = $varCurrValue;
		
		
		
				//Check Existing records first to avoid duplicate entries
		$objPFCInfo = $this->Database->prepare("SELECT id, pid, attribute_id, value_collection FROM tl_pfc_aggregate WHERE pid IN (" . join(",", $arrPageList) . ") AND attribute_set_id=? AND attribute_id=? AND store_id=?")
									->execute($attributeSetID, $attributeID, $storeID);
		

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
					'attribute_set_id'		=> $attributeSetID,
					'store_id'				=> $storeID
				);
				
				
				$this->Database->prepare("INSERT INTO tl_pfc_aggregate %s")->set($arrSet)->execute();
			}
			
			return;
						
		}
		
		$arrPFCInfo = $objPFCInfo->fetchAllAssoc();	//Existing records are stored in an array
		
		$arrProducts = array();
		
		
		// For each existing page that DID in the past have this product ID associated with it, but NOW the submitted list does not include that page id, remove it
		
		foreach($arrAllPageInfo as $page)
		{
			$arrExistingValues = array();
			
			//Get the product ID collection of the current existing page
			$arrExistingValues = deserialize($page['value_collection']);
			
			//If the current existing page id does not exist in the list of pages collected from the form submit, then 
			//remove the product id from the page in question.			
			
			//If the product id exists in the product list for this page, which is not part of the product page list now...  Remove from the product_ids collection and update.
						
			if(!in_array($page['pid'], $arrPFCInfo['pid']))
			{
				if(in_array($varCurrValue, $arrExistingValues))		//Does this need to be more strict - that is, bound to a particular pid when comparing?
				{
					/*echo 'artist: ' . $varCurrValue . '<br /><br />';
					var_dump($arrExistingValues);
					exit;
					echo $key;
					exit;	*/			
					
					$key = array_search($varCurrValue, $arrExistingValues);
										
					//If we find that the product id submitted does, in fact exist in the existing product collection for this page, then we remove it.
					//if(!empty($key))
					//{	
//						echo 'yes ::: page: ' . $page['pid'];
						
						//Do any other products in this category share the filter value?  If not then we can safely remove it
						$objProductsAssociatedWithFilterValue = $this->Database->prepare("SELECT id, pages FROM " . $storeTable . " WHERE " . $dc->field . "=?")->execute($varCurrValue);
						
												
						if($objProductsAssociatedWithFilterValue->numRows < 1)	//if there are no occurrences of this filter value in any product, then ok.
						{
							unset($arrExistingValues[$key]);
						}
						
						$arrOtherProductsPages = $objProductsAssociatedWithFilterValue->fetchEach('pages');

						$blnPreserveFilterValue = false;		//reset every row.  if we end up false at the end we need to unset.
						
						foreach($arrOtherProductsPages as $pageRow)
						{	
							foreach($arrPageList as $currPage)
							{				
								if(in_array($currPage, $pageRow))
								{
									//echo $currPage . ' ::: ' . var_dump($pageRow);
									//exit;
									
									$blnPreserveFilterValue = true;
									break;
								}
							
							}
						}
						
						if(!$blnPreserveFilterValue) //if this filter value is used by any other product in any of the categories associated
						{	
							//echo 'yes we are unsetting it';		//with the given product, then we cannot remove the filter value from the record.
							//exit;
							
							//unset($arrExistingValues[$key]);
						}
						
						
						//var_dump($arrExistingProducts);
						//echo "<br /><br />";
						
						//var_dump($arrExistingProducts);
						$this->Database->prepare("UPDATE tl_pfc_aggregate SET value_collection=? WHERE pid=? AND attribute_set_id=? AND attribute_id=? AND store_id=?")
									   ->execute(serialize($arrExistingValues), $page['pid'], $attributeSetID, $attributeID, $storeID);
						
					//}
				}
				
			}else{
				return;
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
				$this->Database->prepare("UPDATE tl_pfc_aggregate SET value_collection=? WHERE pid=? AND attribute_set_id=? AND attribute_id=? AND store_id=?")
							   ->execute(serialize($arrExistingValues), $page['pid'], $attributeSetID, $attributeID, $storeID);
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
					'attribute_id'			=> $attributeID,
					'attribute_set_id' 		=> $attributeSetID,
					'store_id'				=> $storeID
				);
				
				$this->Database->prepare("INSERT INTO tl_pfc_aggregate %s")->set($arrSet)->execute();
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
				'ptable'                      => 'tl_product_attribute_sets',
				'switchToEdit'                => true, 
				'enableVersioning'            => false/*,
				'onload_callback'							=> array 
					(
						array('ProductCatalog', 'checkPermission')
					)*/,
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
	
	public function regenerateDca($typeId)
	{
			$objFields = $this->Database->prepare("SELECT * FROM tl_product_attributes WHERE pid=? ORDER BY sorting")
						->execute($typeId);

			$this->fieldDef['date']['eval'] = array('datepicker' => $this->getDatePickerString());
			
			$dca = $this->getDefaultDca();        
			$fields = array();
			$titleFields = array();
			$sortingFields = array();
			$groupingFields = array();
			$selectors = array();
			
			// load DCA, as we're calling it now in ProductCatalog, not tl_product_attributes
			$this->loadDataContainer('tl_product_attributes');

			while ($objFields->next())
			{
					$field_name = $objFields->field_name;
					$colType = $objFields->type;
					$visibleOptions = trimsplit('[,;]', $GLOBALS['TL_DCA']['tl_product_attributes']['palettes'][$colType]);
					
					$field = $this->fieldDef[$colType];
					$fields[] = $field_name;
          			
          			//$separators[] = (($objFields->insertBreak) ? ';' : ',');
					
					$dca['fields'][$field_name] = @array_merge_recursive
					(
							array
							(
									'label'     => array ($objFields->name, ''), //$objFields->description),
									'eval'      => array 
									(
											'mandatory'         => $objFields->is_required && in_array('is_required', $visibleOptions) ? true : false,
											//'unique'            => $objFields->uniqueItem && in_array('uniqueItem', $visibleOptions) ? true : false,
											'catalog'           => array
											(
													'type'          => $colType,
											)
									),
									//'default'   => $objFields->defValue,
									'filter'    => $objFields->is_filterable && in_array('is_filterable', $visibleOptions) ? true : false,
									'search'    => $objFields->is_searchable && in_array('is_searchable', $visibleOptions) ? true : false,
									//'sorting'   => $objFields->groupingMode && in_array('groupingMode', $visibleOptions) ? true : false,
							),
							$field
					);
					
					
					$configFunction = $colType . "Config";
					if (method_exists($this, $configFunction))
					{
							$this->$configFunction($dca['fields'][$field_name], $objFields);
					}
					
					if ($objFields->name && in_array('name', $visibleOptions))
					{
							$titleFields[] = $field_name;
					}
					
					if ($objFields->is_order_by_enabled && in_array('is_order_by_enabled', $visibleOptions))
					{
							$sortingFields[] = $field_name;
					}
					
					/*
					if ($objFields->groupingMode && in_array('groupingMode', $visibleOptions))
					{
							$groupingFields[] = $field_name;
							$dca['fields'][$field_name]['flag'] = $objFields->groupingMode;
					}*/
					
					/*
					if ($objFields->parentCheckbox)
					{
							if (isset($selectors[$objFields->parentCheckbox]))
							{
									$selectors[$objFields->parentCheckbox][] = $field_name;
							}
							else
							{
									$selectors[$objFields->parentCheckbox] = array($field_name);
							}
					}*/
			}
			
			// build palettes and subpalettes
			$selectors = array_intersect_key($selectors, array_flip($fields));
			$fieldsInSubpalette = array();
			foreach ($selectors as $selector => $subpaletteFields)
			{
					$dca['fields'][$selector]['eval']['submitOnChange'] = true;
					$dca['subpalettes'][$selector] = implode(',', $subpaletteFields);
					$fieldsInSubpalette = array_merge($fieldsInSubpalette, $subpaletteFields);
			}
			$dca['palettes']['__selector__'] = array_keys($selectors);

			// added insertbreak behaviour by thyon
			$strPalette = '';
			$palettes = array_diff($fields, $fieldsInSubpalette);
			foreach ($palettes as $id=>$field) 
			{
				$strPalette .= (($id > 0) ? $separators[$id] : '').$field;	
			}
			$dca['palettes']['default'] = $strPalette;
						
			// set title fields
			$titleFields = count($titleFields) ? $titleFields : array('id');
			$titleFormat = implode(', ', array_fill(0, count($titleFields), '%s'));
			$dca['list']['label'] = array
			(
					'fields' => $titleFields, 
					'format' => $titleFormat,
					'label_callback' => array('ProductCatalog', 'renderField'),
			);
			
			// set sorting fields
			if (count($sortingFields))
			{
					$dca['list']['sorting']['fields'] = $sortingFields;
			}
			
			if (count($groupingFields))
			{
					$dca['list']['sorting']['mode'] = 2;
					unset($dca['list']['operations']['cut']);
			}
			
			$this->Database->prepare("UPDATE tl_product_attribute_sets SET dca=? WHERE id=?")
					->execute(serialize($dca), $typeId);
	}
	
	
	/**
 	* Row Label
 	*/	    
	public function renderField($row)
	{
	
		if (!$row['pid'])
		{
			return 'ID:'.$row['id'];
		}

		if (isset($this->storeTables[$row['pid']]) && isset($this->strFormat[$row['pid']]))
		{
			$storeTable = $this->storeTables[$row['pid']];
			$strFormat = $this->strFormat[$row['pid']];
		}
		else
		{
			$objType = $this->Database->prepare("SELECT storeTable, format FROM tl_product_attribute_sets WHERE id=?")
					->limit(1)
					->execute($row['pid']);
			
			$storeTable = $objType->storeTable;
			$strFormat = $objType->format;
			$this->storeTables[$row['pid']] = $storeTable;
			$this->strFormat[$row['pid']] = $strFormat;
		}
		
		$fields = $GLOBALS['TL_DCA'][$storeTable]['list']['label']['fields'];

		$values = array();
		foreach($fields as $field)
		{
			$values[$field] = $this->formatTitle($row[$field], $GLOBALS['TL_DCA'][$storeTable]['fields'][$field]);
		}

		if (!strlen($strFormat))
		{
			return implode(', ', $values);
		}
		else
		{
			return $this->generateTitle($strFormat, $values, $storeTable);
		}
	}
	
	private function generateTitle($strFormat, $values, $storeTable)
	{
		$fields = $GLOBALS['TL_DCA'][$storeTable]['list']['label']['fields'];
		preg_match_all('/{{([^}]+)}}/', $strFormat, $matches);
		//$strFormat = '';
		foreach ($matches[1] as $match)
		{
			$params = split('::', $match);
			$fieldConf = $GLOBALS['TL_DCA'][$storeTable]['fields'][$params[0]];
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
	 * Re-generate tl_cap_aggregate from pages field.
	 * 
	 * @access public
	 * @return void
	 */
	public function repairCAP($dc)
	{
		$objAttributeSet = $this->Database->prepare("SELECT * FROM tl_product_attribute_sets WHERE id=?")->limit(1)->execute($dc->id);
		
		if ($objAttributeSet->numRows)
		{
			// Delete all
			$this->Database->prepare("DELETE FROM tl_cap_aggregate WHERE storeTable=?")->execute($objAttributeSet->storeTable);
			
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
						$arrValues[] = $objAttributeSet->storeTable;
						$arrValues[] = $objProducts->id;
						$arrValues[] = $objAttributeSet->id;
						$arrValues[] = $objAttributeSet->store_id;
					}
				}
			}
			
			if (count($arrQuery))
			{
				$this->Database->prepare("INSERT INTO tl_cap_aggregate (pid, sorting, tstamp, storeTable, product_id, attribute_set_id, store_id) VALUES ".implode(', ', $arrQuery))->execute($arrValues);
			}
		}
		
		$this->redirect(str_replace('key=repairCAP', '', $this->Environment->request));
	}
	
	public function pasteButton()
	{
		echo 'test';
	}

	
}


?>