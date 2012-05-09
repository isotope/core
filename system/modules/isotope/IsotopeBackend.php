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
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Class IsotopeBackend
 * 
 * Provide methods to handle Isotope back end components.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 */
class IsotopeBackend extends Backend
{

	/**
	 * Truncate the tl_iso_productcache table if a product is changed
	 * @param mixed
	 * @return mixed
	 */
	public function truncateProductCache($varValue=null)
	{
		$this->Database->query("TRUNCATE tl_iso_productcache");

		return $varValue;
	}


	/**
	 * Get array of subdivisions, delay loading of file if not necessary
	 * @param object
	 * @return array
	 */
	public function getSubdivisions($dc)
	{
		if (!is_array($GLOBALS['TL_LANG']['DIV']))
		{
			$this->loadLanguageFile('subdivisions');
		}

		return $GLOBALS['TL_LANG']['DIV'];
	}


	/**
	 * DCA for setup module tables is "closed" to hide the "new" button. Re-enable it when clicking on a button
	 * @param object
	 */
	public function initializeSetupModule($dc)
	{
		if ($this->Input->get('act') != '')
		{
			$GLOBALS['TL_DCA'][$dc->table]['config']['closed'] = false;
		}
	}


	/**
	 * Add published/unpublished image to each record
	 * @param array
	 * @param string
	 * @return string
	 */
	public function addPublishIcon($row, $label)
	{
		$image = 'published';

		if (!$row['enabled'])
		{
			$image = 'un'.$image;
		}

		return sprintf('<div class="list_icon" style="background-image:url(\'system/themes/%s/images/%s.gif\');">%s</div>', $this->getTheme(), $image, $label);
	}


	/**
	 * Export email template into XML file
	 * @param object
	 */
	public function exportMail($dc)
	{
		// Get the mail meta data
		$objMail = $this->Database->execute("SELECT * FROM tl_iso_mail WHERE id=".$dc->id);

		if ($objMail->numRows < 1)
		{
			return;
		}

		// Romanize the name
		$strName = utf8_romanize($objMail->name);
		$strName = strtolower(str_replace(' ', '_', $strName));
		$strName = preg_replace('/[^A-Za-z0-9_-]/', '', $strName);
		$strName = basename($strName);

		// Create a new XML document
		$xml = new DOMDocument('1.0', 'UTF-8');
		$xml->formatOutput = true;

		// Root element
		$template = $xml->createElement('mail');
		$template = $xml->appendChild($template);

		foreach ($objMail->row() as $k=>$v)
		{
			$field = $xml->createElement('field');
			$field->setAttribute('name', $k);
			$field = $template->appendChild($field);

			if ($v === null)
			{
				$v = 'NULL';
			}

			$value = $xml->createTextNode($v);
			$value = $field->appendChild($value);
		}

		$objContent = $this->Database->execute("SELECT * FROM tl_iso_mail_content WHERE pid=".$objMail->id);

		while ($objContent->next())
		{
			$content = $xml->createElement('content');
			$content = $template->appendChild($content);

			foreach ($objContent->row() as $k => $v)
			{
				$field = $xml->createElement('field');
				$field->setAttribute('name', $k);
				$field = $content->appendChild($field);

				if ($v === null)
				{
					$v = 'NULL';
				}

				$value = $xml->createTextNode($v);
				$value = $field->appendChild($value);
			}
		}

		$strXML = $xml->saveXML();

		header('Content-Type: application/imt');
		header('Content-Transfer-Encoding: binary');
		header('Content-Disposition: attachment; filename="' . $strName . '.imt"');
		header('Content-Length: ' . strlen($strXML));
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Expires: 0');

		echo $strXML;

		exit;
	}


	/**
	 * Import email template
	 * @param object
	 * @return string
	 */
	public function importMail($dc)
	{
		if ($this->Input->post('FORM_SUBMIT') == 'tl_mail_import')
		{
			$source = $this->Input->post('source', true);

			// Check the file names
			if (!$source || !is_array($source))
			{
				$_SESSION['TL_ERROR'][] = $GLOBALS['TL_LANG']['ERR']['all_fields'];
				$this->reload();
			}

			$arrFiles = array();

			// Skip invalid entries
			foreach ($source as $strFile)
			{
				// Skip folders
				if (is_dir(TL_ROOT . '/' . $strFile))
				{
					$_SESSION['TL_ERROR'][] = sprintf($GLOBALS['TL_LANG']['ERR']['importFolder'], basename($strFile));
					continue;
				}

				$objFile = new File($strFile);

				// Skip anything but .imt files
				if ($objFile->extension != 'imt')
				{
					$_SESSION['TL_ERROR'][] = sprintf($GLOBALS['TL_LANG']['ERR']['filetype'], $objFile->extension);
					continue;
				}

				$arrFiles[] = $strFile;
			}

			// Check wether there are any files left
			if (count($arrFiles) < 1)
			{
				$_SESSION['TL_ERROR'][] = $GLOBALS['TL_LANG']['ERR']['all_fields'];
				$this->reload();
			}

			return $this->importMailFiles($arrFiles);
		}

		$objTree = new FileTree($this->prepareForWidget($GLOBALS['TL_DCA']['tl_iso_mail']['fields']['source'], 'source', null, 'source', 'tl_iso_mail'));

		// Return the form
		return '
<div id="tl_buttons">
<a href="'.ampersand(str_replace('&key=importMail', '', $this->Environment->request)).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'" accesskey="b">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>

<h2 class="sub_headline">'.$GLOBALS['TL_LANG']['tl_iso_mail']['importMail'][1].'</h2>'.$this->getMessages().'

<form action="'.ampersand($this->Environment->request, true).'" id="tl_mail_import" class="tl_form" method="post">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="tl_mail_import">
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">

<div class="tl_tbox block">
  <h3><label for="source">'.$GLOBALS['TL_LANG']['tl_iso_mail']['source'][0].'</label> <a href="contao/files.php" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['fileManager']) . '" onclick="Backend.getScrollOffset(); Backend.openWindow(this, 750, 500); return false;">' . $this->generateImage('filemanager.gif', $GLOBALS['TL_LANG']['MSC']['fileManager'], 'style="vertical-align:text-bottom;"') . '</a></h3>'.$objTree->generate().(strlen($GLOBALS['TL_LANG']['tl_iso_mail']['source'][1]) ? '
  <p class="tl_help tl_tip">'.$GLOBALS['TL_LANG']['tl_iso_mail']['source'][1].'</p>' : '').'
</div>

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
  <input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['tl_iso_mail']['importMail'][0]).'">
</div>

</div>
</form>';
	}


	/**
	 * Import mail template from XML file
	 * @param array
	 */
	protected function importMailFiles($arrFiles)
	{
		// Store the field names of the theme tables
		$arrDbFields = array
		(
			'tl_iso_mail' => array_diff($this->Database->getFieldNames('tl_iso_mail'), array('id', 'pid')),
			'tl_iso_mail_content' => array_diff($this->Database->getFieldNames('tl_iso_mail_content'), array('id', 'pid')),
		);

		foreach ($arrFiles as $strFile)
		{
			$xml = new DOMDocument();
			$xml->preserveWhiteSpace = false;

			if (!$xml->loadXML(file_get_contents(TL_ROOT . '/' . $strFile)))
			{
				$_SESSION['TL_ERROR'][] = sprintf($GLOBALS['TL_LANG']['tl_iso_mail']['xml_error'], basename($strFile));
				continue;
			}

			$arrMapper = array();
			$template = $xml->getElementsByTagName('field');
			$content = $xml->getElementsByTagName('content');

			$arrSet = array();

			// Loop through the mail fields
			for( $i=0; $i<$template->length; $i++ )
			{
				if (!in_array($template->item($i)->getAttribute('name'), $arrDbFields['tl_iso_mail']))
				{
					continue;
				}

				$arrSet[$template->item($i)->getAttribute('name')] = $template->item($i)->nodeValue;
			}

			$intPid = $this->Database->prepare("INSERT INTO tl_iso_mail %s")->set($arrSet)->execute()->insertId;

			// Loop through the content fields
			for ($i=0; $i<$content->length; $i++)
			{
				$arrSet = array('pid'=>$intPid);
				$row = $content->item($i)->childNodes;

				// Loop through the content fields
				for ($j=0; $j<$row->length; $j++)
				{
					if (!in_array($row->item($j)->getAttribute('name'), $arrDbFields['tl_iso_mail_content']))
					{
						continue;
					}

					$arrSet[$row->item($j)->getAttribute('name')] = $row->item($j)->nodeValue;
				}

				$this->Database->prepare("INSERT INTO tl_iso_mail_content %s")->set($arrSet)->execute();
			}

			// Notify the user
			$_SESSION['TL_CONFIRM'][] = sprintf($GLOBALS['TL_LANG']['tl_iso_mail']['mail_imported'], basename($strFile));
		}

		// Redirect
		setcookie('BE_PAGE_OFFSET', 0, 0, '/');
		$this->redirect(str_replace('&key=importMail', '', $this->Environment->request));
	}
	
	
	/**
	 * Return all Isotope modules 
	 * @return array
	 */
	public function getIsotopeModules()
	{
		$arrModules = array();

		foreach ($GLOBALS['ISO_MOD'] as $k=>$v)
		{
			$arrModules[$k] = array_keys($v);
		}

		return $arrModules;
	}
	
	
	/**
	 * List template from all themes, show theme name
	 * @param string
	 * @param int
	 * @return array
	 */
	public static function getTemplates($strPrefix, $intTheme=0)
	{
		$objDatabase = Database::getInstance();
		$objConfig = Config::getInstance();

		$arrThemes = array();
		$arrStores = array();
		$arrTemplates = array();
		$arrFolders = array();

		// Add the templates root directory
		$arrFolders[] = TL_ROOT . '/templates';

		// Add theme templates folder
		$objTheme = $objDatabase->execute("SELECT name, templates FROM tl_theme" . ($intPid>0 ? " WHERE id=$intPid" : ''));
		while( $objTheme->next() )
		{
			if ($objTheme->templates != '' && is_dir(TL_ROOT .'/'. $objTheme->templates))
			{
				$arrFolders[] = TL_ROOT .'/'. $objTheme->templates;
				$arrThemes[TL_ROOT .'/'. $objTheme->templates] = $objTheme->name;
			}
		}
		
		// Add Isotope config templates folder
		$objStore = $objDatabase->execute("SELECT name, templateGroup FROM tl_iso_config");
		while( $objStore->next() )
		{
			if ($objStore->templateGroup != '' && is_dir(TL_ROOT .'/'. $objStore->templateGroup))
			{
				$arrFolders[] = TL_ROOT .'/'. $objStore->templateGroup;
				$arrStores[TL_ROOT .'/'. $objStore->templateGroup] = $objStore->name;
			}
		}

		// Add the module templates folders if they exist
		foreach ($objConfig->getActiveModules() as $strModule)
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
				
				if (isset($arrThemes[$strFolder]))
				{
					$arrTemplates[$strName] = sprintf($GLOBALS['ISO_LANG']['MSC']['templateTheme'], $strName, $arrThemes[$strFolder]);
				}
				elseif (isset($arrStores[$strFolder]))
				{
					$arrTemplates[$strName] = sprintf($GLOBALS['ISO_LANG']['MSC']['templateConfig'], $strName, $arrStores[$strFolder]);
				}
				else
				{
					$arrTemplates[$strName] = $strName;
				}
			}
		}

		natcasesort($arrTemplates);
		return $arrTemplates;
	}
	
	
	/**
	 * Get all tax classes, including a "split amonst products" option
	 * @param DataContainer
	 * @return array
	 */
	public static function getTaxClassesWithSplit()
	{
		$objDatabase = Database::getInstance();
		
		$arrTaxes = array();
		$objTaxes = $objDatabase->execute("SELECT * FROM tl_iso_tax_class ORDER BY name");
		
		while( $objTaxes->next() )
		{
			$arrTaxes[$objTaxes->id] = $objTaxes->name;
		}
		
		$arrTaxes[-1] = $GLOBALS['ISO_LANG']['MSC']['splittedTaxRate'];
		
		return $arrTaxes;
	}
	
	
	/**
	 * Get order status and return it as array
	 * @param object
	 * @return array
	 */
	public static function getOrderStatus()
	{
		$objDatabase = Database::getInstance();
		
		$arrStatus = array();
		$objStatus = $objDatabase->execute("SELECT id, name FROM tl_iso_orderstatus ORDER BY sorting");
		
		while( $objStatus->next() )
		{
			$arrStatus[$objStatus->id] = $objStatus->name;
		}
		
		return $arrStatus;
	}


	/**
	 * Add the product attributes to the db updater array so the users don't delete them while updating
	 * @param array
	 * @return array
	 */
	public function addAttributesToDBUpdate($arrData)
	{
		$objAttributes = $this->Database->execute("SELECT * FROM tl_iso_attributes");

		while ($objAttributes->next())
		{
			if ($objAttributes->type == '' || $GLOBALS['ISO_ATTR'][$objAttributes->type]['sql'] == '')
			{
				continue;
			}

			$arrData['tl_iso_products']['TABLE_FIELDS'][$objAttributes->field_name] = sprintf('`%s` %s', $objAttributes->field_name, $GLOBALS['ISO_ATTR'][$objAttributes->type]['sql']);

			// also check indexes
			if ($objAttributes->fe_filter && $GLOBALS['ISO_ATTR'][$objAttributes->type]['useIndex'])
			{
				$arrData['tl_iso_products']['TABLE_CREATE_DEFINITIONS'][$objAttributes->field_name] = sprintf('KEY `%s` (`%s`)', $objAttributes->field_name, $objAttributes->field_name);
			}
		}

		return $arrData;
	}
}

