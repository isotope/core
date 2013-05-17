<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope;

use \Contao\Backend as Contao_Backend;
use Isotope\Model\Config;
use Isotope\Model\OrderStatus;


/**
 * Class Isotope\Backend
 *
 * Provide methods to handle Isotope back end components.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 */
class Backend extends Contao_Backend
{

    /**
     * Truncate the tl_iso_productcache table if a product is changed
     * @param mixed
     * @return mixed
     */
    public static function truncateProductCache($varValue=null)
    {
        \Database::getInstance()->query("TRUNCATE tl_iso_productcache");

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
            \System::loadLanguageFile('subdivisions');
        }

        return $GLOBALS['TL_LANG']['DIV'];
    }


    /**
     * DCA for setup module tables is "closed" to hide the "new" button. Re-enable it when clicking on a button
     * @param object
     */
    public function initializeSetupModule($dc)
    {
        if (\Input::get('act') != '')
        {
            $GLOBALS['TL_DCA'][$dc->table]['config']['closed'] = false;
        }
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
        if (\Input::post('FORM_SUBMIT') == 'tl_mail_import')
        {
            $source = \Input::post('source', true);

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

                $objFile = new \File($strFile);

                // Skip anything but .imt files
                if ($objFile->extension != 'imt')
                {
                    $_SESSION['TL_ERROR'][] = sprintf($GLOBALS['TL_LANG']['ERR']['filetype'], $objFile->extension);
                    continue;
                }

                $arrFiles[] = $strFile;
            }

            // Check wether there are any files left
            if (empty($arrFiles))
            {
                $_SESSION['TL_ERROR'][] = $GLOBALS['TL_LANG']['ERR']['all_fields'];
                $this->reload();
            }

            return $this->importMailFiles($arrFiles);
        }

        $objTree = new \FileTree($this->prepareForWidget($GLOBALS['TL_DCA']['tl_iso_mail']['fields']['source'], 'source', null, 'source', 'tl_iso_mail'));

        // Return the form
        return '
<div id="tl_buttons">
<a href="'.ampersand(str_replace('&key=importMail', '', \Environment::get('request'))).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'" accesskey="b">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>

<h2 class="sub_headline">'.$GLOBALS['TL_LANG']['tl_iso_mail']['importMail'][1].'</h2>'.$this->getMessages().'

<form action="'.ampersand(\Environment::get('request'), true).'" id="tl_mail_import" class="tl_form" method="post">
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
        $this->redirect(str_replace('&key=importMail', '', \Environment::get('request')));
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
    public static function getTemplates($strPrefix)
    {
        $arrTemplates = array();

		// Get the default templates
		foreach (\TemplateLoader::getPrefixedFiles($strPrefix) as $strTemplate) {
			$arrTemplates[$strTemplate] = $strTemplate;
		}

		$arrCustomized = glob(TL_ROOT . '/templates/' . $strPrefix . '*');

		// Add the customized templates
		if (is_array($arrCustomized)) {
			foreach ($arrCustomized as $strFile) {

				$strTemplate = basename($strFile, strrchr($strFile, '.'));

				if (!isset($arrTemplates[$strTemplate])) {
					$arrTemplates[''][$strTemplate] = $strTemplate;
				}
			}
		}

		// Do not look for back end templates in theme folders (see #5379)
		if ($strPrefix == 'be_') {
			return $arrTemplates;
		}

		// Try to select the shop configs
		try {
			$objConfig = Config::findAll(array('order'=>'name'));
		} catch (\Exception $e) {
			$objConfig = null;
		}

		// Add the shop config templates
		if (null !== $objConfig) {
			while ($objConfig->next()) {
				if ($objConfig->templateGroup != '') {

    				$strFolder = sprintf($GLOBALS['TL_LANG']['MSC']['templatesConfig'], $objConfig->name);
					$arrConfigTemplates = glob(TL_ROOT . '/' . $objConfig->templateGroup . '/' . $strPrefix . '*');

					if (is_array($arrConfigTemplates)) {
						foreach ($arrConfigTemplates as $strFile) {

							$strTemplate = basename($strFile, strrchr($strFile, '.'));

							if (!isset($arrTemplates[''][$strTemplate])) {
								$arrTemplates[$strFolder][$strTemplate] = $strTemplate;
							}
						}
					}
				}
			}
		}

		// Try to select the themes (see #5210)
		try {
			$objTheme = \ThemeModel::findAll(array('order'=>'name'));
		} catch (\Exception $e) {
			$objTheme = null;
		}

		// Add the theme templates
		if (null !== $objTheme) {
			while ($objTheme->next()) {
				if ($objTheme->templates != '') {

    				$strFolder = sprintf($GLOBALS['TL_LANG']['MSC']['templatesTheme'], $objTheme->name);
					$arrThemeTemplates = glob(TL_ROOT . '/' . $objTheme->templates . '/' . $strPrefix . '*');

					if (is_array($arrThemeTemplates)) {
						foreach ($arrThemeTemplates as $strFile) {

							$strTemplate = basename($strFile, strrchr($strFile, '.'));

							if (!isset($arrTemplates[''][$strTemplate])) {
								$arrTemplates[$strFolder][$strTemplate] = $strTemplate;
							}
						}
					}
				}
			}
		}

        return $arrTemplates;
    }


    /**
     * Get all tax classes, including a "split amonst products" option
     * @param DataContainer
     * @return array
     */
    public static function getTaxClassesWithSplit()
    {
        $arrTaxes = array();
        $objTaxes = \Database::getInstance()->execute("SELECT * FROM tl_iso_tax_class ORDER BY name");

        while ($objTaxes->next())
        {
            $arrTaxes[$objTaxes->id] = $objTaxes->name;
        }

        $arrTaxes[-1] = $GLOBALS['TL_LANG']['MSC']['splittedTaxRate'];

        return $arrTaxes;
    }


    /**
     * Get order status and return it as array
     * @param object
     * @return array
     */
    public static function getOrderStatus()
    {
        $arrStatus = array();
        $objStatus = OrderStatus::findAll(array('order'=>'sorting'));

        while ($objStatus->next())
        {
            $arrStatus[$objStatus->id] = $objStatus->current()->getName();
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
        if ($this->Database->tableExists('tl_iso_attributes'))
        {
            $objAttributes = $this->Database->execute("SELECT * FROM tl_iso_attributes");

            while ($objAttributes->next())
            {
                if ($objAttributes->field_name == '' || $objAttributes->type == '' || $GLOBALS['ISO_ATTR'][$objAttributes->type]['sql'] == '')
                {
                    continue;
                }

                $arrData['tl_iso_products']['TABLE_FIELDS'][$objAttributes->field_name] = sprintf('`%s` %s', $objAttributes->field_name, $GLOBALS['ISO_ATTR'][$objAttributes->type]['sql']);

                // Also check indexes
                if ($objAttributes->fe_filter && $GLOBALS['ISO_ATTR'][$objAttributes->type]['useIndex'])
                {
                    $arrData['tl_iso_products']['TABLE_CREATE_DEFINITIONS'][$objAttributes->field_name] = sprintf('KEY `%s` (`%s`)', $objAttributes->field_name, $objAttributes->field_name);
                }
            }
        }

        return $arrData;
    }


    /**
     * Show messages for new order status
     * @return string
     */
    public function getOrderMessages()
    {
        if (!$this->Database->tableExists('tl_iso_orderstatus')) {
            return '';
        }

        $arrMessages = array();
        $objOrders = $this->Database->query("SELECT COUNT(*) AS total, s.name FROM tl_iso_product_collection c LEFT JOIN tl_iso_orderstatus s ON c.order_status=s.id WHERE c.type='Order' AND s.welcomescreen='1' GROUP BY s.id");

        while ($objOrders->next())
        {
            $arrMessages[] = '<p class="tl_new">' . sprintf($GLOBALS['TL_LANG']['MSC']['newOrders'], $objOrders->total, $objOrders->name) . '</p>';
        }

        return implode("\n", $arrMessages);
    }


    /**
     * Get product type for a given group
     * @param product group id
     * @return int|false
     */
    public static function getProductTypeForGroup($intGroup)
    {
        $objDatabase = \Database::getInstance();

        do
        {
            $objGroup = $objDatabase->query('SELECT pid, product_type FROM tl_iso_groups WHERE id=' . (int) $intGroup);

            if ($objGroup->product_type > 0)
            {
                return $objGroup->product_type;
            }

            $intGroup = $objGroup->pid;
        }
        while ($objGroup->numRows && $intGroup > 0);

        // if there is no default type set we return false
        return false;
    }

    /**
     * Returns an array of all allowed product IDs and variant IDs for the current backend user
     * @return array|bool
     */
    public static function getAllowedProductIds()
    {
        $objUser = \BackendUser::getInstance();

        if ($objUser->isAdmin)
        {
            $arrProducts = true;
        }
        else
        {
            $arrNewRecords = $_SESSION['BE_DATA']['new_records']['tl_iso_products'];
            $arrProductTypes = $objUser->iso_product_types;
            $arrGroups = $objUser->iso_groups;

            if (!is_array($arrProductTypes) || empty($arrProductTypes) || !is_array($arrGroups) || empty($arrGroups))
            {
                return false;
            }

            $arrGroups = array_merge($arrGroups, Isotope::getInstance()->call('getChildRecords', array($arrGroups, 'tl_iso_groups')));

            $objProducts = Database::getInstance()->execute("SELECT id FROM tl_iso_products
                                                             WHERE pid=0 AND language='' AND
                                                             gid IN (" . implode(',', $arrGroups) . ") AND
                                                             (type IN (" . implode(',', $arrProductTypes) . ")" .
                                                             ((is_array($arrNewRecords) && !empty($arrNewRecords)) ? " OR id IN (".implode(',', $arrNewRecords)."))" : ')'));

            if ($objProducts->numRows == 0)
            {
                return array();
            }

            $arrProducts = $objProducts->fetchEach('id');
            $arrProducts = array_merge($arrProducts, Isotope::getInstance()->call('getChildRecords', array($arrProducts, 'tl_iso_products')));
        }

        // HOOK: allow extensions to define allowed products
        if (isset($GLOBALS['ISO_HOOKS']['getAllowedProductIds']) && is_array($GLOBALS['ISO_HOOKS']['getAllowedProductIds']))
        {
            foreach ($GLOBALS['ISO_HOOKS']['getAllowedProductIds'] as $callback)
            {
                $objCallback = \System::importStatic($callback[0]);
                $arrAllowed = $objCallback->$callback[1]();

                if ($arrAllowed === false)
                {
                    return false;
                }
                elseif (is_array($arrAllowed))
                {
                    if ($arrProducts === true)
                    {
                        $arrProducts = $arrAllowed;
                    }
                    else
                    {
                        $arrProducts = array_intersect($arrProducts, $arrAllowed);
                    }
                }
            }
        }

        // If all product are allowed, we don't need to filter
        if ($arrProducts === true || count($arrProducts) == \Database::getInstance()->execute("SELECT COUNT(id) as total FROM tl_iso_products")->total)
        {
            return true;
        }

        return $arrProducts;
    }


    /**
     * Generate groups breadcrumb and return it as HTML string
     * @param integer
     * @param integer
     * @return string
     */
    public static function generateGroupsBreadcrumb($intId, $intProductId=null)
    {
		$arrGroups = array();
		$objSession = \Session::getInstance();

		// Set a new gid
		if (isset($_GET['gid']))
		{
			$objSession->set('iso_products_gid', \Input::get('gid'));
			\Controller::redirect(preg_replace('/&gid=[^&]*/', '', \Environment::get('request')));
		}

		// Return if there is no trail
		if (!$objSession->get('iso_products_gid') && !$intProductId)
		{
			return '';
		}

		// Include the product in variants view
		if ($intProductId)
		{
			$objProduct = \Database::getInstance()->prepare("SELECT gid, name FROM tl_iso_products WHERE id=?")
												  ->limit(1)
												  ->execute($intProductId);

			if ($objProduct->numRows)
			{
				$arrGroups[] = array('id'=>$intProductId, 'name'=>$objProduct->name);

				// Override the group ID
				$intId = $objProduct->gid;
			}
		}

		$intPid = $intId;

		// Generate groups
		do
		{
			$objGroup = \Database::getInstance()->prepare("SELECT id, pid, name FROM tl_iso_groups WHERE id=?")
												->limit(1)
												->execute($intPid);

			if ($objGroup->numRows)
			{
				$arrGroups[] = array('id'=>$objGroup->id, 'name'=>$objGroup->name);

				if ($objGroup->pid)
				{
					$intPid = $objGroup->pid;
				}
			}
		}
		while ($objGroup->pid);

		$arrLinks = array();
		$strUrl = \Environment::get('request');

		// Remove the product ID from URL
		if ($intProductId)
		{
			$strUrl = preg_replace('/&id=[^&]*/', '', $strUrl);
		}

		// Generate breadcrumb trail
		foreach ($arrGroups as $i=>$arrGroup)
		{
			if (!$arrGroup['id'])
			{
				continue;
			}

			$buffer = '';

			// No link for the active group
			if ((!$intProductId && $intId == $arrGroup['id']) || ($intProductId && $intProductId == $arrGroup['id']))
			{
				$buffer .= $arrGroup['name'];
			}
			else
			{
				$buffer .= '<a href="' . ampersand($strUrl) . '&amp;gid='.$arrGroup['id'] . '" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['selectGroup']).'">' . $arrGroup['name'] . '</a>';
			}

			$arrLinks[] = $buffer;
		}

		$arrLinks[] = sprintf('<a href="%s" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['allGroups']).'"><img src="system/modules/isotope/assets/folders.png" width="16" height="16" alt="" style="margin-right:6px;"> %s</a>', ampersand($strUrl) . '&amp;gid=0', $GLOBALS['TL_LANG']['MSC']['filterAll']);

		return '
<ul id="tl_breadcrumb">
  <li>' . implode(' &gt; </li><li>', array_reverse($arrLinks)) . '</li>
</ul>';
    }
}
