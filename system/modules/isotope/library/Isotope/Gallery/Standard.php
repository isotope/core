<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2008-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Gallery;

use Isotope\Interfaces\IsotopeGallery;


/**
 * Class Standard
 *
 * Provide methods to handle Isotope galleries.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 * @author     Yanick Witschi <yanick.witschi@terminal42.ch>
 */
class Standard extends \Frontend implements IsotopeGallery
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'iso_gallery_standard';

    /**
     * Data storage
     * @var array
     */
    protected $arrData = array();

    /**
     * Files
     * @var array
     */
    protected $arrFiles = array();

    /**
     * Isotope object
     * @var object
     */
    protected $Isotope;


    /**
     * Construct the object
     * @param string
     * @param array
     */
    public function __construct($strName, $arrFiles)
    {
        parent::__construct();

        $this->import('Isotope\Isotope', 'Isotope');
        $this->name = $strName;
        $this->files = $arrFiles;
    }


    /**
     * Set a value
     * @param string
     * @param mixed
     */
    public function __set($strKey, $varValue)
    {
        switch ($strKey)
        {
            case 'files':
                $this->arrFiles = array();
                $varValue = deserialize($varValue);

                if (is_array($varValue) && !empty($varValue))
                {
                    foreach ($varValue as $file)
                    {
                        $this->addImage($file);
                    }
                }

                // No image available, add placeholder from store configuration
                if (empty($this->arrFiles))
                {
                    $strPlaceholder = $this->Isotope->Config->missing_image_placeholder;

                    if ($strPlaceholder != '' && is_file(TL_ROOT . '/' . $strPlaceholder))
                    {
                        $this->addImage(array('src'=>$this->Isotope->Config->missing_image_placeholder), false);
                    }
                }
                break;

            case 'main_image':
                $file = is_array($varValue) ? $varValue : array('src'=>$file);
                return $this->addImage($file, true, true);
                break;

            default:
                $this->arrData[$strKey] = $varValue;
                break;
        }
    }


    /**
     * Get a value
     * @param string
     * @return mixed
     */
    public function __get($strKey)
    {
        switch ($strKey)
        {
            case 'main_image':
                return reset($this->arrFiles);
                break;

            case 'images':
                return $this->arrFiles;
                break;

            default:
                return $this->arrData[$strKey];
        }
    }


    /**
     * Get the number of images
     * @return int
     */
    public function size()
    {
        return count($this->arrFiles);
    }


    /**
     * Returns whether the gallery object has an image do display or not
     * @return boolean
     */
    public function hasImages()
    {
        return !empty($this->arrFiles);
    }


    /**
     * Check whether a property is set
     * @param string
     * @return boolean
     */
    public function __isset($strKey)
    {
        return isset($this->arrData[$strKey]);
    }


    /**
     * If the class is echoed, return the main image
     */
    public function __toString()
    {
        return $this->generateMainImage();
    }


    /**
     * Generate main image and return it as HTML string
     * @param string
     * @return string
     */
    public function generateMainImage($strType='medium')
    {
        if (!count($this->arrFiles))
        {
            return $this->generateAttribute($this->name . '_' . $strType . 'size', ' ', 'images ' . $strType);
        }

        $arrFile = reset($this->arrFiles);

        $this->injectAjax();

        $objTemplate = new \Isotope\Template($this->strTemplate);

        $objTemplate->setData($arrFile);
        $objTemplate->id = 0;
        $objTemplate->mode = 'main';
        $objTemplate->type = $strType;
        $objTemplate->name = $this->name;
        $objTemplate->product_id = $this->product_id;
        $objTemplate->href_reader = $this->href_reader;

        list($objTemplate->link, $objTemplate->rel) = explode('|', $arrFile['link']);

        return $this->generateAttribute($this->name . '_' . $strType . 'size', $objTemplate->parse(), 'images ' . $strType);
    }


    /**
     * Generate gallery and return it as HTML string
     * @param string
     * @param integer
     * @return string
     */
    public function generateGallery($strType='gallery', $intSkip=1)
    {
        $strGallery = '';

        foreach ($this->arrFiles as $i => $arrFile)
        {
            if ($i < $intSkip)
            {
                continue;
            }

            $objTemplate = new \Isotope\Template($this->strTemplate);

            $objTemplate->setData($arrFile);
            $objTemplate->id = $i;
            $objTemplate->mode = 'gallery';
            $objTemplate->type = $strType;
            $objTemplate->name = $this->name;
            $objTemplate->product_id = $this->product_id;
            $objTemplate->href_reader = $this->href_reader;

            list($objTemplate->link, $objTemplate->rel) = explode('|', $arrFile['link']);

            $strGallery .= $objTemplate->parse();
        }

        $this->injectAjax();
        return $this->generateAttribute($this->name . '_gallery', $strGallery, $strType);
    }


    /**
     * Inject Ajax scripts
     */
    protected function injectAjax()
    {
        list(,$startScript, $endScript) = \Isotope\Frontend::getElementAndScriptTags();

        $GLOBALS['TL_MOOTOOLS'][get_class($this).'_ajax'] = "
$startScript
window.addEvent('ajaxready', function() {
  Mediabox ? Mediabox.scanPage() : Lightbox.scanPage();
});
$endScript
";
    }


    /**
     * Generate the HTML attribute container
     * @param string
     * @param string
     * @param string
     * @return string
     */
    protected function generateAttribute($strId, $strBuffer, $strClass='')
    {
        return '<div class="iso_attribute' . ($strClass != '' ? ' '.strtolower($strClass) : '') .'" id="' . $strId . '">' . $strBuffer . '</div>';
    }


    /**
     * Add an image to the gallery
     * @param array
     * @param bool
     * @param bool
     * @return bool
     */
    private function addImage(array $file, $blnWatermark=true, $blnMain=false)
    {
        $strFile = $file['src'];

        // File without path must be located in the isotope root folder
        if (strpos($strFile, '/') === false)
        {
            $strFile = 'isotope/' . strtolower(substr($strFile, 0, 1)) . '/' . $strFile;
        }

        if (is_file(TL_ROOT . '/' . $strFile))
        {
            $objFile = new \File($strFile);

            if ($objFile->isGdImage)
            {
                foreach ((array) $this->Isotope->Config->imageSizes as $size)
                {
                    $strImage = $this->getImage($strFile, $size['width'], $size['height'], $size['mode']);

                    if ($size['watermark'] != '' && $blnWatermark)
                    {
                        $strImage = \Isotope\Frontend::watermarkImage($strImage, $size['watermark'], $size['position']);
                    }

                    $arrSize = @getimagesize(TL_ROOT . '/' . $strImage);

                    if (is_array($arrSize) && strlen($arrSize[3]))
                    {
                        $file[$size['name'] . '_size'] = $arrSize[3];
                        $file[$size['name'] . '_imageSize'] = $arrSize;
                    }

                    $file['alt'] = specialchars($file['alt'], true);
                    $file['desc'] = specialchars($file['desc'], true);

                    $file[$size['name']] = $strImage;
                }

                // Main image is first in the array
                if ($blnMain)
                {
                    array_unshift($this->arrFiles, $file);
                }
                else
                {
                    $this->arrFiles[] = $file;
                }

                return true;
            }
        }

        return false;
    }
}
