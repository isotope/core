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
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 * @author     Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 */

namespace Isotope;

use Isotope\Model\Attribute;
use Isotope\Model\Product;
use Isotope\Model\ProductCollectionItem;


/**
 * Class tl_iso_products
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_iso_products extends \Backend
{


    /**
     * Import a back end user and Isotope objects
     */
    public function __construct()
    {
        parent::__construct();

        $this->import('BackendUser', 'User');
    }


    /**
     * Generate all combination of product attributes
     * @param object
     * @return string
     */
    public function generateVariants($dc)
    {
        $table = Product::getTable();
        $objProduct = Product::findByPk($dc->id);

        $doNotSubmit = false;
        $strBuffer = '';
        $arrOptions = array();

        foreach ($objProduct->getRelated('type')->getVariantAttributes() as $attribute) {
            if ($GLOBALS['TL_DCA'][$table]['fields'][$attribute]['attributes']['variant_option']) {

                $GLOBALS['TL_DCA'][$table]['fields'][$attribute]['eval']['mandatory'] = true;
                $GLOBALS['TL_DCA'][$table]['fields'][$attribute]['eval']['multiple'] = true;

                $arrField = \CheckBox::getAttributesFromDca($GLOBALS['TL_DCA'][$table]['fields'][$attribute], $attribute);

                foreach ($arrField['options'] as $k => $option) {
                    if ($option['value'] == '') {
                        unset($arrField['options'][$k]);
                    }
                }

                $objWidget = new \CheckBox($arrField);

                if (\Input::post('FORM_SUBMIT') == ($table.'_generate')) {
                    $objWidget->validate();

                    if ($objWidget->hasErrors()) {
                        $doNotSubmit = true;
                    } else {
                        $arrOptions[$attribute] = $objWidget->value;
                    }
                }

                $strBuffer .= $objWidget->parse();
            }
        }

        if (\Input::post('FORM_SUBMIT') == $table.'_generate' && !$doNotSubmit) {
            $time = time();
            $arrCombinations = array();

            foreach ($arrOptions as $name => $options) {
                $arrTemp = $arrCombinations;
                $arrCombinations = array();

                foreach ($options as $option) {
                    if (empty($arrTemp)) {
                        $arrCombinations[][$name] = $option;
                        continue;
                    }

                    foreach ($arrTemp as $temp) {
                        $temp[$name] = $option;
                        $arrCombinations[] = $temp;
                    }
                }
            }

            foreach ($arrCombinations as $combination) {

                $objVariant = \Database::getInstance()->prepare("
                    SELECT * FROM $table WHERE pid=? AND " . implode('=? AND ', array_keys($combination)) . "=?"
                )->execute(array_merge(array($objProduct->id), $combination));

                if (!$objVariant->numRows) {

                    $arrSet = array_merge($combination, array(
                        'tstamp'    => $time,
                        'pid'       => $objProduct->id,
                        'inherit'   => array_diff($objProduct->getRelated('type')->getVariantAttributes(), Attribute::getSystemColumnsFields()),
                    ));

                    \Database::getInstance()->prepare("INSERT INTO $table %s")->set($arrSet)->execute();
                }
            }

            \Controller::redirect(str_replace('&key=generate', '', \Environment::get('request')));
        }

        // Return form
        return '
<div id="tl_buttons">
<a href="'.ampersand(str_replace('&key=generate', '', \Environment::get('request'))).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>

<h2 class="sub_headline">'.sprintf($GLOBALS['TL_LANG'][$table]['generate'][1], $dc->id).'</h2>'.$this->getMessages().'

<form action="'.ampersand(\Environment::get('request'), true).'" id="'.$table.'_generate" class="tl_form" method="post">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="'.$table.'_generate">
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">

<div class="tl_tbox block">
' . $strBuffer . '
</div>

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
  <input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['tl_iso_products']['generate'][0]).'">
</div>

</div>
</form>';
    }


    /**
     * Import images and other media file for products
     * @param object
     * @param array
     * @return string
     */
    public function importAssets($dc, $arrNewImages=array())
    {
        $objTree = new \FileTree(\FileTree::getAttributesFromDca($GLOBALS['TL_DCA']['tl_iso_products']['fields']['source'], 'source', null, 'source', 'tl_iso_products'));

        // Import assets
        if (\Input::post('FORM_SUBMIT') == 'tl_iso_products_import' && \Input::post('source') != '')
        {
            $this->import('Files');

            $strPath = \Input::post('source');
            $arrFiles = scan(TL_ROOT . '/' . $strPath);

            if (empty($arrFiles))
            {
                $_SESSION['TL_ERROR'][] = $GLOBALS['TL_LANG']['MSC']['noFilesInFolder'];
                \Controller::reload();
            }

            $arrDelete = array();
            $objProducts = \Database::getInstance()->prepare("SELECT * FROM tl_iso_products WHERE pid=0")->execute();

            while ($objProducts->next())
            {
                $arrImageNames  = array();
                $arrImages = deserialize($objProducts->images);

                if (!is_array($arrImages))
                {
                    $arrImages = array();
                }
                else
                {
                    foreach ($arrImages as $row)
                    {
                        if ($row['src'])
                        {
                            $arrImageNames[] = $row['src'];
                        }
                    }
                }

                $arrPattern = array();
                $arrPattern[] = $objProducts->alias ?  standardize($objProducts->alias) : null;
                $arrPattern[] = $objProducts->sku ? $objProducts->sku : null;
                $arrPattern[] = $objProducts->sku ? standardize($objProducts->sku) : null;
                $arrPattern[] = !empty($arrImageNames) ? implode('|', $arrImageNames) : null;

                // !HOOK: add custom import regex patterns
                if (isset($GLOBALS['ISO_HOOKS']['addAssetImportRegexp']) && is_array($GLOBALS['ISO_HOOKS']['addAssetImportRegexp']))
                {
                    foreach ($GLOBALS['ISO_HOOKS']['addAssetImportRegexp'] as $callback)
                    {
                        $objCallback = \System::importStatic($callback[0]);
                        $arrPattern = $objCallback->$callback[1]($arrPattern,$objProducts);
                    }
                }

                $strPattern = '@^(' . implode('|', array_filter($arrPattern)) . ')@i';

                $arrMatches = preg_grep($strPattern, $arrFiles);

                if (!empty($arrMatches))
                {
                    $arrNewImages = array();

                    foreach ($arrMatches as $file)
                    {
                        if (is_dir(TL_ROOT . '/' . $strPath . '/' . $file))
                        {
                            $arrSubfiles = scan(TL_ROOT . '/' . $strPath . '/' . $file);
                            if (!empty($arrSubfiles))
                            {
                                foreach ($arrSubfiles as $subfile)
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

                    if (!empty($arrNewImages))
                    {
                        foreach ($arrNewImages as $strFile)
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

                        \Database::getInstance()->prepare("UPDATE tl_iso_products SET images=? WHERE id=?")->execute(serialize($arrImages), $objProducts->id);
                    }
                }
            }

            if (!empty($arrDelete))
            {
                $arrDelete = array_unique($arrDelete);

                foreach ($arrDelete as $file)
                {
                    $this->Files->delete($file);
                }
            }

            \Controller::reload();
        }

        // Return form
        return '
<div id="tl_buttons">
<a href="'.ampersand(str_replace('&key=import', '', \Environment::get('request'))).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>

<h2 class="sub_headline">'.$GLOBALS['TL_LANG']['tl_iso_products']['import'][1].'</h2>'.$this->getMessages().'

<form action="'.ampersand(\Environment::get('request'), true).'" id="tl_iso_products_import" class="tl_form" method="post">
<div class="tl_formbody_edit iso_importassets">
<input type="hidden" name="FORM_SUBMIT" value="tl_iso_products_import">
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">

<div class="tl_info">' . $GLOBALS['TL_LANG']['tl_iso_products']['importAssetsDescr'] . '</div>

<div class="tl_tbox block">
  <h3><label for="source">'.$GLOBALS['TL_LANG']['tl_iso_products']['source'][0].'</label> <a href="typolight/files.php" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['fileManager']) . '" onclick="Backend.getScrollOffset(); this.blur(); Backend.openWindow(this, 750, 500); return false;">' . \Image::getHtml('filemanager.gif', $GLOBALS['TL_LANG']['MSC']['fileManager'], 'style="vertical-align:text-bottom;"') . '</a></h3>
  '.$objTree->generate().(strlen($GLOBALS['TL_LANG']['tl_iso_products']['source'][1]) ? '
  <p class="tl_help">'.$GLOBALS['TL_LANG']['tl_iso_products']['source'][1].'</p>' : '').'
</div>

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
<input type="submit" name="save" id="save" class="tl_submit" alt="import product assets" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['tl_iso_products']['import'][0]).'">
</div>

</div>
</form>';
    }



    /**
     * Return the "copy" button
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function copyIcon($row, $href, $label, $title, $icon, $attributes)
    {
        if ($row['pid'] > 0)
        {
            return '<a href="'.preg_replace('/&(amp;)?id=[^& ]*/i', '', ampersand(\Environment::get('request'))).'&amp;act=paste&amp;mode=copy&amp;table=tl_iso_products&amp;id='.$row['id'].'&amp;pid='.\Input::get('id').'" title="'.specialchars($title).'"'.$attributes.' onclick="Backend.getScrollOffset();">'.\Image::getHtml($icon, $label).'</a> ';
        }

        return '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.\Image::getHtml($icon, $label).'</a> ';
    }



    /**
     * Return the "cut" button
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function cutIcon($row, $href, $label, $title, $icon, $attributes)
    {
        // Check permission
        if (!$this->User->isAdmin) {
            $groups = deserialize($this->User->iso_groups);

            if (!is_array($groups) || empty($groups)) {
                return \Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
            }
        }

        if ($row['pid'] > 0) {
            return '<a href="'.preg_replace('/&(amp;)?id=[^& ]*/i', '', ampersand(\Environment::get('request'))).'&amp;act=paste&amp;mode=cut&amp;table=tl_iso_products&amp;id='.$row['id'].'&amp;pid='.\Input::get('id').'" title="'.specialchars($title).'"'.$attributes.' onclick="Backend.getScrollOffset();">'.\Image::getHtml($icon, $label).'</a> ';
        } else {
            return '<a href="system/modules/isotope/group.php?do='.\Input::get('do').'&amp;table=tl_iso_groups&amp;field=gid&amp;value='.$row['gid'].'" title="'.specialchars($title).'"'.$attributes.' onclick="Backend.getScrollOffset();Isotope.openModalGroupSelector({\'width\':765,\'title\':\''.specialchars($GLOBALS['TL_LANG']['tl_iso_products']['product_groups'][0]).'\',\'url\':this.href,\'action\':\'moveProduct\',\'redirect\':\''.$this->addToUrl($href . '&pid=' . intval(\Input::get('pid')) . '&id=' . $row['id']).'\'});return false">'.\Image::getHtml($icon, $label).'</a> ';
        }
    }

    /**
     * Disable "delete" button if product has been sold
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function deleteButton($row, $href, $label, $title, $icon, $attributes)
    {
        if (ProductCollectionItem::countBy(array("product_id IN (SELECT id FROM tl_iso_products WHERE id=? OR (pid=? AND language=''))"), array($row['id'], $row['id'])) > 0) {
            return \Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
        }

        return '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.\Image::getHtml($icon, $label).'</a> ';
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
        if (strlen(\Input::get('tid')))
        {
            $this->toggleVisibility(\Input::get('tid'), (\Input::get('state') == 1));
            \Controller::redirect($this->getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$this->User->isAdmin && !$this->User->hasAccess('tl_iso_products::published', 'alexf'))
        {
            return '';
        }

        $objProductType = \Database::getInstance()->execute("SELECT * FROM tl_iso_producttypes WHERE id=" . (int) $row['type']);
        $arrAttributes = $row['pid'] ? deserialize($objProductType->variant_attributes, true) : deserialize($objProductType->attributes, true);
        $time = time();

        if (($arrAttributes['start']['enabled'] && $row['start'] != '' && $row['start'] > $time) || ($arrAttributes['stop']['enabled'] && $row['stop'] != '' && $row['stop'] < $time))
        {
            return \Image::getHtml('system/modules/isotope/assets/invisible-startstop.png', $label).' ';
        }
        elseif ($row['published'] != '1')
        {
            $icon = 'invisible.gif';
        }

        $href .= '&amp;tid='.$row['id'].'&amp;state='.($row['published'] ? '' : 1);

        return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.\Image::getHtml($icon, $label).'</a> ';
    }


    /**
     * Publish/unpublish a product
     * @param integer
     * @param boolean
     * @return void
     */
    public function toggleVisibility($intId, $blnVisible)
    {
        // Check permissions to edit
        \Input::setGet('id', $intId);
        \Input::setGet('act', 'toggle');

        $this->import('Isotope\ProductCallbacks', 'ProductCallbacks');
        $this->ProductCallbacks->checkPermission();

        // Check permissions to publish
        if (!$this->User->isAdmin && !$this->User->hasAccess('tl_iso_products::published', 'alexf'))
        {
            \System::log('Not enough permissions to publish/unpublish product ID "'.$intId.'"', 'tl_iso_products toggleVisibility', TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');
        }

        $this->createInitialVersion('tl_iso_products', $intId);

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_iso_products']['fields']['published']['save_callback']))
        {
            foreach ($GLOBALS['TL_DCA']['tl_iso_products']['fields']['published']['save_callback'] as $callback)
            {
                $objCallback = \System::importStatic($callback[0]);
                $blnVisible = $objCallback->$callback[1]($blnVisible, $this);
            }
        }

        // Update the database
        \Database::getInstance()->prepare("UPDATE tl_iso_products SET published='" . ($blnVisible ? 1 : '') . "' WHERE id=?")->execute($intId);

        $this->createNewVersion('tl_iso_products', $intId);
    }


    /**
     * Initialize the tl_iso_products DCA
     * @return void
     */
    public function loadProductsDCA($strTable)
    {
        if ($strTable != 'tl_iso_products' || !\Database::getInstance()->tableExists('tl_iso_attributes')) {
            return;
        }

        $arrData = &$GLOBALS['TL_DCA'][$strTable];
        $arrData['attributes'] = array();

        // Write attributes from database to DCA
        if (($objAttributes = \Isotope\Model\Attribute::findAll(array('uncached'=>true))) !== null) {
            while ($objAttributes->next()) {
                $objAttribute = $objAttributes->current();

                if (null !== $objAttribute) {
                    $objAttribute->saveToDCA($arrData);
                    $arrData['attributes'][$objAttribute->field_name] = $objAttribute;
                }
            }
        }

        // Create temporary models for non-database attributes
        foreach (array_diff_key($arrData['fields'], $arrData['attributes']) as $strName => $arrConfig) {

            if (is_array($arrConfig['attributes'])) {
                if ($arrConfig['attributes']['type'] != '') {
                    $strClass = $arrConfig['attributes']['type'];
                } else {
                    $strClass = \Isotope\Model\Attribute::getClassForModelType($arrConfig['inputType']);
                }

                if ($strClass != '') {
                    $objAttribute = new $strClass();
                    $objAttribute->loadFromDCA($arrData, $strName);
                    $arrData['attributes'][$strName] = $objAttribute;
                }
            }
        }
    }
}
