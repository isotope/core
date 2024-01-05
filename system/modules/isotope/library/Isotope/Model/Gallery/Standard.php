<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Gallery;

use Contao\Environment;
use Contao\File;
use Contao\FilesModel;
use Contao\Picture;
use Contao\StringUtil;
use Contao\System;
use Haste\Image\Image;
use Isotope\CompatibilityHelper;
use Isotope\Interfaces\IsotopeGallery;
use Isotope\Model\Gallery;
use Isotope\Template;

/**
 * Standard implements a lightbox gallery
 *
 * @property string $lightbox_size
 * @property string $lightbox_watermark_image
 * @property string $lightbox_watermark_position
 * @property string $lightbox_template
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
    private $arrImages = array();

    /**
     * Override template if available in record
     *
     * @param array $arrData The data record
     *
     * @return $this The model object
     */
    public function setRow(array $arrData)
    {
        if ($arrData['customTpl'] != '' && CompatibilityHelper::isFrontend()) {
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
     * Set gallery template
     *
     * @param string $strTemplate
     */
    public function setTemplate($strTemplate)
    {
        $this->strTemplate = $strTemplate;
    }

    /**
     * Get gallery template
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->strTemplate;
    }

    /**
     * Set image files
     *
     * @param array $arrFiles
     */
    public function setFiles(array $arrFiles)
    {
        $this->arrFiles = array_values($arrFiles);
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
        return \count($this->arrFiles);
    }


    /**
     * Returns whether the gallery object has an image do display or not
     *
     * @return bool
     */
    public function hasImages()
    {
        // Check files array here because we don't need to generate an image just to know if there are images
        return \count($this->arrFiles) > 0;
    }

    /**
     * Checks if a placeholder image is defined
     *
     * @return bool
     */
    protected function hasPlaceholderImage()
    {
        return ($this->placeholder && null !== FilesModel::findByPk($this->placeholder));
    }

    /**
     * Generate main image and return it as HTML string
     *
     * @return string
     */
    public function generateMainImage()
    {
        $hasImages = $this->hasImages();

        if (!$hasImages && !$this->hasPlaceholderImage()) {
            return '';
        }

        /** @var Template|object $objTemplate */
        $objTemplate = new Template($this->strTemplate);

        $this->addImageToTemplate(
            $objTemplate,
            'main',
            ($hasImages ? $this->arrFiles[0] : $this->getPlaceholderImage()),
            $hasImages
        );

        $objTemplate->javascript = '';

        if (Environment::get('isAjaxRequest')) {
            $strScripts = '';
            $arrTemplates = StringUtil::deserialize($this->lightbox_template, true);

            if (!empty($arrTemplates)) {
                foreach ($arrTemplates as $strTemplate) {
                    $objScript = new Template($strTemplate);
                    $strScripts .= $objScript->parse();
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
        // If we skip one or more images or no placeholder image is available there's no gallery
        if (!$this->hasImages() && ($intSkip >= 1 || !$this->hasPlaceholderImage())) {
            return '';
        }

        $strGallery = '';
        $watermark  = true;
        $arrFiles   = \array_slice($this->arrFiles, $intSkip);

        // Add placeholder for the gallery
        if (0 === \count($arrFiles) && $intSkip < 1) {
            $arrFiles[] = $this->getPlaceholderImage();
            $watermark  = false;
        }

        foreach ($arrFiles as $arrFile) {
            $objTemplate = new Template($this->strTemplate);

            $this->addImageToTemplate($objTemplate, 'gallery', $arrFile, $watermark);

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
     * @param Template|object $objTemplate
     * @param string          $strType
     * @param array           $arrFile
     * @param bool            $blnWatermark
     */
    protected function addImageToTemplate(Template $objTemplate, $strType, array $arrFile, $blnWatermark = true)
    {
        $arrFile = $this->getImageForType($strType, $arrFile, $blnWatermark);

        $objTemplate->setData($this->arrData);
        $objTemplate->type       = $strType;
        $objTemplate->name       = $this->getName();
        $objTemplate->product_id = $this->product_id;
        $objTemplate->file       = $arrFile;
        $objTemplate->src        = $arrFile[$strType] ?? '';
        $objTemplate->size       = $arrFile[$strType . '_size'] ?? '';
        $objTemplate->alt        = $arrFile['alt'] ?? '';
        $objTemplate->title      = $arrFile['desc'] ?? '';
        $objTemplate->class      = trim(($this->arrData['class'] ?? '') . ' ' . ($arrFile['class'] ?? ''));

        // Add the missing data to the picture
        $arrFile['picture']['alt']   = $objTemplate->alt;
        $arrFile['picture']['title'] = $objTemplate->title;
        $arrFile['picture']['class'] = $objTemplate->class;

        $objTemplate->picture = $arrFile['picture'];

        switch ($this->anchor) {
            case 'reader':
                $objTemplate->hasLink = ($this->href != '');
                $objTemplate->link    = $this->href;
                break;

            case 'lightbox':
                $arrFile = $this->getImageForType('lightbox', $arrFile, $blnWatermark);
                [$link, $rel] = explode('|', $arrFile['link'], 2) + [null, null];
                $attributes = ($rel ? ' data-lightbox="' . $rel . '"' : ' target="_blank"');

                $objTemplate->hasLink    = true;
                $objTemplate->link       = $link ?: $arrFile['lightbox'];
                $objTemplate->attributes = ($link ? $attributes : ' data-lightbox="product' . $this->product_id . '"');
                break;

            default:
                $objTemplate->hasLink = false;
                break;
        }
    }

    /**
     * Gets the placeholder image
     *
     * @return array
     *
     * @throws \UnderflowException If no placeholder image is found
     */
    protected function getPlaceholderImage()
    {
        $objPlaceholder = FilesModel::findByPk($this->placeholder);

        if (null === $objPlaceholder) {
            throw new \UnderflowException('Placeholder image requested but not defined!');
        }

        return array('src' => $objPlaceholder->path);
    }

    /**
     * Gets the image for a given file and given type and optionally adds a watermark to it
     *
     * @param   string $strType
     * @param   array $arrFile
     * @param   bool  $blnWatermark
     *
     * @return  array
     * @throws  \InvalidArgumentException
     */
    protected function getImageForType($strType, array $arrFile, $blnWatermark = true)
    {
        // Check cache
        $strCacheKey = md5($strType . '-' . json_encode($arrFile) . '-' . (int) $blnWatermark);

        if (isset($this->arrImages[$strCacheKey])) {
            return $this->arrImages[$strCacheKey];
        }

        $strFile = $arrFile['src'];

        // File without path must be located in the isotope root folder
        if (strpos($strFile, '/') === false) {
            $strFile = 'isotope/' . strtolower(substr($strFile, 0, 1)) . '/' . $strFile;
        }

        $objFile = new File($strFile);

        if (!$objFile->exists()) {
            return [];
        }

        $size = StringUtil::deserialize($this->{$strType . '_size'}, true);

        try {
            $strImage = \Contao\Image::create($strFile, $size)->executeResize()->getResizedPath();
            $picture = Picture::create($strFile, $size)->getTemplateData();
        } catch (\Exception $e) {
            System::log('Image "' . $strFile . '" could not be processed: ' . $e->getMessage(), __METHOD__, TL_ERROR);

            $strImage = '';
            $picture = array('img'=>array('src'=>'', 'srcset'=>''), 'sources'=>array());
        }

        // Watermark
        if ($blnWatermark
            && $this->{$strType . '_watermark_image'} != ''
            && ($objWatermark = FilesModel::findByUuid($this->{$strType . '_watermark_image'})) !== null
        ) {
            if (method_exists(File::class, 'createIfDeferred')) {
                (new File(rawurldecode($strImage)))->createIfDeferred();
            }

            $strImage = Image::addWatermark($strImage, $objWatermark->path, $this->{$strType . '_watermark_position'});

            // Apply watermark to the picture image source
            if ($picture['img']['src']) {
                $picture['img']['src'] = Image::addWatermark($picture['img']['src'], $objWatermark->path, $this->{$strType . '_watermark_position'});
            }

            // Apply watermark to the picture sources
            foreach ($picture['sources'] as $k => $v) {
                $picture['sources'][$k]['src'] = Image::addWatermark($v['src'], $objWatermark->path, $this->{$strType . '_watermark_position'});
            }
        }

        $arrSize = (new File(rawurldecode($strImage)))->imageSize;

        if (\is_array($arrSize) && $arrSize[3] !== '') {
            $arrFile[$strType . '_size']      = $arrSize[3];
            $arrFile[$strType . '_imageSize'] = $arrSize;
        }

        $arrFile['alt']     = StringUtil::specialchars($arrFile['alt'] ?? '', true);
        $arrFile['desc']    = StringUtil::specialchars($arrFile['desc'] ?? '', true);
        $arrFile['picture'] = $picture;

        $arrFile[$strType] = TL_ASSETS_URL . $strImage;

        $this->arrImages[$strCacheKey] = $arrFile;

        return $arrFile;
    }
}
