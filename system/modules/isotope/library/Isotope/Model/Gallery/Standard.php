<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Gallery;

use Haste\Image\Image;
use Isotope\Interfaces\IsotopeGallery;
use Isotope\Model\Gallery;
use Isotope\Template;

/**
 * Standard implements a lightbox gallery
 *
 * @property string lightbox_size
 * @property string lightbox_watermark_image
 * @property string lightbox_watermark_position
 * @property string lightbox_template
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
     * Images cache
     * @var array
     */
    protected $arrImages = array();

    /**
     * Override template if available in record
     *
     * @param array $arrData The data record
     *
     * @return $this The model object
     */
    public function setRow(array $arrData)
    {
        if ($arrData['customTpl'] != '' && TL_MODE == 'FE') {
            $this->strTemplate = $arrData['customTpl'];
        }

        return parent::setRow($arrData);
    }


    /**
     * Set gallery attribute name
     *
     * @param string $strName
     */
    public function setName($strName)
    {
        $this->strName = $strName;
    }

    /**
     * Get gallery attribute name
     *
     * @return string
     */
    public function getName()
    {
        return $this->strName;
    }

    /**
     * Set image files
     *
     * @param array $arrFiles
     */
    public function setFiles(array $arrFiles)
    {
        $this->arrFiles = $arrFiles;
    }

    /**
     * Get image files
     *
     * @return array
     */
    public function getFiles()
    {
        return $this->arrFiles;
    }


    /**
     * Get the number of images
     *
     * @return int
     */
    public function size()
    {
        return count($this->arrFiles);
    }


    /**
     * Returns whether the gallery object has an image do display or not
     *
     * @return bool
     */
    public function hasImages()
    {
        // Check files array here because we don't need to generate an image
        // just to know if there are images
        return !empty($this->arrFiles);
    }

    /**
     * Generate main image and return it as HTML string
     *
     * @return string
     */
    public function generateMainImage()
    {
        if (!$this->hasImages()) {
            // Check for the placeholder image
            if (!$this->hasPlaceholderImage()) {
                return '';
            }

            $arrFile = $this->getPlaceholderImageForGallery('main');
        } else {
            $arrFile = $this->getImageForGallery(
                'main',
                reset($this->arrFiles)
            );
        }

        $objTemplate = new Template($this->strTemplate);

        $this->addImageToTemplate($objTemplate, 'main', $arrFile);
        $objTemplate->javascript = '';

        if (\Environment::get('isAjaxRequest')) {
            $strScripts = '';
            $arrTemplates = deserialize($this->lightbox_template, true);

            if (!empty($arrTemplates)) {
                foreach ($arrTemplates as $strTemplate) {
                    $objScript = new Template($strTemplate);
                    $strScripts = $objScript->parse();
                }
            }

            $objTemplate->javascript = $strScripts;
        }

        return $objTemplate->parse();
    }

    /**
     * Generate gallery and return it as HTML string
     *
     * @param int $intSkip
     *
     * @return string
     */
    public function generateGallery($intSkip = 1)
    {
        $strGallery = '';

        $arrImages = array();

        if (!$this->hasImages()) {
            // If we skip one or more images or no placeholder image is available
            // there's no gallery
            if ($intSkip >= 1 || !$this->hasPlaceholderImage()) {
                return '';
            }

            // Otherwise we get the placeholder for the gallery
            $arrImages[] = $this->getPlaceholderImageForGallery('gallery');

        } else {
            foreach ($this->arrFiles as $i => $arrFile) {
                if ($i < $intSkip) {
                    continue;
                }

                $arrImages[] = $this->getImageForGallery(
                    'gallery',
                    $arrFile
                );
            }
        }

        foreach ($arrImages as $arrFile) {
            $objTemplate = new Template($this->strTemplate);

            $this->addImageToTemplate($objTemplate, 'gallery', $arrFile);

            $strGallery .= $objTemplate->parse();
        }

        return $strGallery;
    }

    /**
     * If the class is echoed, return the main image
     *
     * @return string
     */
    public function __toString()
    {
        return $this->generateMainImage();
    }

    /**
     * Generate template with given file
     *
     * @param Template $objTemplate
     * @param string   $strType
     * @param array    $arrFile
     *
     * @return string
     */
    protected function addImageToTemplate(Template $objTemplate, $strType, array $arrFile)
    {
        $objTemplate->setData($this->arrData);
        $objTemplate->type       = $strType;
        $objTemplate->product_id = $this->product_id;
        $objTemplate->file       = $arrFile;
        $objTemplate->src        = $arrFile[$strType];
        $objTemplate->size       = $arrFile[$strType . '_size'];
        $objTemplate->alt        = $arrFile['alt'];
        $objTemplate->title      = $arrFile['desc'];
        $objTemplate->class      = trim($this->arrData['class'] . ' ' . $arrFile['class']);

        switch ($this->anchor) {
            case 'reader':
                $objTemplate->hasLink = ($this->href != '');
                $objTemplate->link    = $this->href;
                break;

            case 'lightbox':
                list($link, $rel) = explode('|', $arrFile['link'], 2);

                $objTemplate->hasLink    = true;
                $objTemplate->link       = $link ? : $arrFile['lightbox'];
                $objTemplate->attributes = ($link ? ($rel ? ' data-lightbox="' . $rel . '"' : ' target="_blank"') : ' data-lightbox="product' . $this->product_id . '"');
                break;

            default:
                $objTemplate->hasLink = false;
                break;
        }
    }

    /**
     * Gets the image for a given file and given gallery and optionally adds a
     * watermark to it
     *
     * @param   string $strGallery
     * @param   array $arrFile
     * @param   bool  $blnWatermark
     *
     * @return  array
     * @throws  \InvalidArgumentException
     */
    protected function getImageForGallery($strGallery, array $arrFile, $blnWatermark = true)
    {
        // Check cache
        $strCacheKey = md5($strGallery . '-' . json_encode($arrFile) . '-' . (int) $blnWatermark);

        if (isset($this->arrImages[$strCacheKey])) {
            return $this->arrImages[$strCacheKey];
        }

        $strFile = $arrFile['src'];

        // File without path must be located in the isotope root folder
        if (strpos($strFile, '/') === false) {
            $strFile = 'isotope/' . strtolower(substr($strFile, 0, 1)) . '/' . $strFile;
        }

        if (!is_file(TL_ROOT . '/' . $strFile)) {
            throw new \InvalidArgumentException('Apparently the file "' . $strFile . '" does not exist!');
        }

        $size     = deserialize($this->{$strGallery . '_size'}, true);
        $strImage = \Image::get($strFile, $size[0], $size[1], $size[2]);

        // Watermark
        if ($blnWatermark
            && $this->{$strGallery . '_watermark_image'} != ''
            && ($objWatermark = \FilesModel::findByUuid($this->{$strGallery . '_watermark_image'})) !== null
        ) {
            $strImage = Image::addWatermark($strImage, $objWatermark->path, $this->{$strGallery . '_watermark_position'});
        }

        $arrSize = @getimagesize(TL_ROOT . '/' . $strImage);

        if (is_array($arrSize) && $arrSize[3] !== '') {
            $arrFile[$strGallery . '_size']      = $arrSize[3];
            $arrFile[$strGallery . '_imageSize'] = $arrSize;
        }

        $arrFile['alt']  = specialchars($arrFile['alt'], true);
        $arrFile['desc'] = specialchars($arrFile['desc'], true);

        $arrFile[$strGallery] = $strImage;

        $this->arrImages[$strCacheKey] = $arrFile;

        return $arrFile;
    }

    /**
     * Checks if a placeholder image is defined
     *
     * @return bool
     */
    protected function hasPlaceholderImage()
    {
        return \FilesModel::findByPk($this->placeholder) !== null;
    }

    /**
     * Gets the placeholder image
     *
     * @param $strGallery
     * @return array|null
     */
    protected function getPlaceholderImageForGallery($strGallery)
    {
        $objPlaceholder = \FilesModel::findByPk($this->placeholder);

        if (null === $objPlaceholder) {
            throw new \RuntimeException('Placeholder image requested but not defined!');
        }

        return $this->getImageForGallery(
            $strGallery,
            array('src' => $objPlaceholder->path),
            false
        );
    }
}
