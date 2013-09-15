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
 */

namespace Isotope;

use Isotope\Model\Download;
use Isotope\Model\ProductCollectionDownload;


/**
 * Class tl_iso_downloads
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_iso_downloads extends \Backend
{

    /**
     * List download files
     * @param   array
     * @return  string
     * @see     https://contao.org/de/manual/3.1/data-container-arrays.html#label_callback
     */
    public function listRows($row)
    {
        // Check for version 3 format
        if (!is_numeric($row['singleSRC'])) {
            return '<p class="error">'.$GLOBALS['TL_LANG']['ERR']['version2format'].'</p>';
        }

        $objDownload = Download::findByPk($row['id']);

        if (null === $objDownload) {
            return '<p class="error">'.$GLOBALS['TL_LANG']['ERR']['invalidName'].'</p>';
        }

        $path = $objDownload->getRelated('singleSRC')->path;

        if ($objDownload->getRelated('singleSRC')->type == 'folder') {
            $arrDownloads = array();

            foreach (scan(TL_ROOT . '/' . $path) as $file) {
                if (is_file(TL_ROOT . '/' . $path . '/' . $file)) {
                    $objFile = new \File($path . '/' . $file);
                    $icon = 'background:url(assets/contao/images/' . $objFile->icon . ') left center no-repeat; padding-left: 22px';
                    $arrDownloads[] = sprintf('<div style="margin-bottom:5px;height:16px;%s">%s</div>', $icon, $path . '/' . $file);
                }
            }

            if (empty($arrDownloads)) {
                return $GLOBALS['TL_LANG']['ERR']['emptyDownloadsFolder'];
            }

            return '<div style="margin-bottom:5px;height:16px;font-weight:bold">' . $path . '</div>' . implode("\n", $arrDownloads);
        }

        if (is_file(TL_ROOT . '/' . $path))
        {
            $objFile = new \File($path);
            $icon = 'background: url(assets/contao/images/' . $objFile->icon . ') left center no-repeat; padding-left: 22px';
        }

        return sprintf('<div style="height: 16px;%s">%s</div>', $icon, $path);
    }

    /**
     * Prevent delete on a download which has been sold
     * @param   array
     * @param   string
     * @param   string
     * @param   string
     * @param   string
     * @param   array
     * @return  string
     * @see     https://contao.org/de/manual/3.1/data-container-arrays.html#button_callback
     */
    public function deleteButton($row, $href, $label, $title, $icon, $attributes)
    {
        if (ProductCollectionDownload::countBy('download_id', $row['id']) > 0) {
            return $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
        }

        return '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
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
        if (!\BackendUser::getInstance()->isAdmin && !\BackendUser::getInstance()->hasAccess('tl_iso_downloads::published', 'alexf'))
        {
            return '';
        }

        if ($row['published'] != '1')
        {
            $icon = 'invisible.gif';
        }

        $href .= '&amp;tid='.$row['id'].'&amp;state='.($row['published'] ? '' : 1);

        return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
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

        // Check permissions to publish
        if (!\BackendUser::getInstance()->isAdmin && !\BackendUser::getInstance()->hasAccess('tl_iso_downloads::published', 'alexf'))
        {
            \System::log('Not enough permissions to publish/unpublish download ID "'.$intId.'"', __METHOD__, TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');
        }

        $this->createInitialVersion('tl_iso_downloads', $intId);

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_iso_downloads']['fields']['published']['save_callback']))
        {
            foreach ($GLOBALS['TL_DCA']['tl_iso_downloads']['fields']['published']['save_callback'] as $callback)
            {
                $objCallback = \System::importStatic($callback[0]);
                $blnVisible = $objCallback->$callback[1]($blnVisible, $this);
            }
        }

        // Update the database
        \Database::getInstance()->prepare("UPDATE tl_iso_downloads SET published='" . ($blnVisible ? 1 : '') . "' WHERE id=?")->execute($intId);

        $this->createNewVersion('tl_iso_downloads', $intId);
    }
}
