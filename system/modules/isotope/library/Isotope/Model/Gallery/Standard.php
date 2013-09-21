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

namespace Isotope\Model\Gallery;

use Isotope\Isotope;
use Isotope\Interfaces\IsotopeGallery;
use Isotope\Model\Gallery;


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
class Standard extends Gallery implements IsotopeGallery
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'iso_gallery_standard';

    /**
     * Attribute name
     * @var string
     */
    protected $strName;

    /**
     * Files
     * @var array
     */
    protected $arrFiles = array();


    /**
     * Set gallery attribute name
     * @param   string
     */
    public function setName($strName)
    {
        $this->strName = $strName;
    }

    /**
     * Get gallery attribute name
     * @return  string
     */
    public function getName()
    {
        return $this->strName;
    }

    /**
     * Set image files
     * @param   array
     */
    public function setFiles($varValue)
    {
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
            $objPlaceholder = \FilesModel::findByPk($this->placeholder);

            if (null !== $objPlaceholder && is_file(TL_ROOT . '/' . $objPlaceholder->path))
            {
                $this->addImage(array('src'=>$objPlaceholder->path), false);
            }
        }
    }

    /**
     * Get image files
     * @return  array
     */
    public function getFiles()
    {
        return $this->arrFiles;
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
     * If the class is echoed, return the main image
     */
    public function __toString()
    {
        return $this->generateMainImage();
    }


    /**
     * Generate main image and return it as HTML string
     * @return  string
     */
    public function generateMainImage()
    {
        if (!count($this->arrFiles)) {
            return '';
        }

        $arrFile = reset($this->arrFiles);

        $objTemplate = new \Isotope\Template($this->strTemplate);

        $this->addImageToTemplate($objTemplate, 'main', $arrFile);

        return $objTemplate->parse();
    }

    /**
     * Generate gallery and return it as HTML string
     * @param   integer
     * @return  string
     */
    public function generateGallery($intSkip=1)
    {
        $strGallery = '';

        foreach ($this->arrFiles as $i => $arrFile) {
            if ($i < $intSkip) {
                continue;
            }

            $objTemplate = new \Isotope\Template($this->strTemplate);

            $this->addImageToTemplate($objTemplate, 'gallery', $arrFile);

            $strGallery .= $objTemplate->parse();
        }

        return $strGallery;
    }

    /**
     * Generate template with given file
     * @param   object
     * @param   string
     * @param   array
     * @return  string
     */
    protected function addImageToTemplate(\Isotope\Template $objTemplate, $strType, array $arrFile)
    {
        $objTemplate->setData($this->arrData);
        $objTemplate->type = $strType;
        $objTemplate->product_id = $this->product_id;
        $objTemplate->file = $arrFile;
        $objTemplate->src = $arrFile[$strType];
        $objTemplate->size = $arrFile[$strType.'_size'];
        $objTemplate->alt = $arrFile['alt'];
        $objTemplate->title = $arrFile['desc'];

        switch ($this->anchor) {
            case 'reader':
                $objTemplate->hasLink = ($this->href != '');
                $objTemplate->link = $this->href;
                break;

            case 'lightbox':
                list($link, $rel) = explode('|', $arrFile['link'], 2);

                $objTemplate->hasLink = true;
                $objTemplate->link = $link ?: $arrFile['lightbox'];
                $objTemplate->attributes = ($link ? ($rel ? ' data-lightbox="'.$rel.'"' : ' target="_blank"') : ' data-lightbox="product'.$this->product_id.'"');
                break;

            default:
                $objTemplate->hasLink = false;
                break;
        }
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
                foreach (array('main','gallery','lightbox') as $name)
                {
                    $size = deserialize($this->{$name.'_size'});
                    $strImage = \Image::get($strFile, $size[0], $size[1], $size[2]);

                    if ($this->{$name.'_watermark_image'} != '' && $blnWatermark)
                    {
                        $strImage = \Isotope\Frontend::watermarkImage($strImage, $this->{$name.'_watermark_image'}, $this->{$name.'_watermark_position'});
                    }

                    $arrSize = @getimagesize(TL_ROOT . '/' . $strImage);

                    if (is_array($arrSize) && strlen($arrSize[3]))
                    {
                        $file[$name . '_size'] = $arrSize[3];
                        $file[$name . '_imageSize'] = $arrSize;
                    }

                    $file['alt'] = specialchars($file['alt'], true);
                    $file['desc'] = specialchars($file['desc'], true);

                    $file[$name] = $strImage;
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
