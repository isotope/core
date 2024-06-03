<?php

/**
 * Haste utilities for Contao Open Source CMS
 *
 * Copyright (C) 2012-2013 Codefog & terminal42 gmbh
 *
 * @package    Haste
 * @link       http://github.com/codefog/contao-haste/
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Haste\Image;

use Contao\Controller;
use Contao\File;
use Contao\Files;
use Contao\FilesModel;
use Contao\Frontend;
use Contao\StringUtil;
use Contao\System;

class Image
{
    const SORT_NAME_ASC = 'name_asc';
    const SORT_NAME_DESC = 'name_desc';
    const SORT_DATE_ASC = 'date_asc';
    const SORT_DATE_DESC = 'date_desc';
    const SORT_CUSTOM = 'custom';
    const SORT_RANDOM = 'random';

    /**
     * Apply a watermark to an image
     * @param string
     * @param string
     * @param string
     * @param string
     */
    public static function addWatermark($image, $watermark, $position = 'br', $target = null)
    {
        $image = urldecode($image);
        $projectDir = System::getContainer()->getParameter('kernel.project_dir');

        if (!is_file($projectDir . '/' . $image) || !is_file($projectDir . '/' . $watermark)) {
            return $image;
        }

        $objFile = new File($image);
        $strCacheName = 'assets/images/' . substr($objFile->filename, -1) . '/' . $objFile->filename . '-' . substr(md5($watermark . '-' . $position . '-' . $objFile->mtime), 0, 8) . '.' . $objFile->extension;

        // Return the path of the new image if it exists already
        if (is_file($projectDir . '/' . $strCacheName)) {
            return $strCacheName;
        }

        // !HOOK: override image watermark routine
        if (isset($GLOBALS['TL_HOOKS']['watermarkImage']) && is_array($GLOBALS['TL_HOOKS']['watermarkImage'])) {
            foreach ($GLOBALS['TL_HOOKS']['watermarkImage'] as $callback) {
                $objCallback = System::importStatic($callback[0]);
                $return = $objCallback->{$callback[1]}($image, $watermark, $position, $target);

                if (is_string($return)) {
                    return $return;
                }
            }
        }

        $arrGdinfo = gd_info();
        $strImage = null;

        // Load image
        switch ($objFile->extension) {
            case 'gif':
                if ($arrGdinfo['GIF Read Support']) {
                    $strImage = imagecreatefromgif($projectDir . '/' . $image);
                }
                break;

            case 'jpg':
            case 'jpeg':
                if ($arrGdinfo['JPG Support'] || $arrGdinfo['JPEG Support']) {
                    $strImage = imagecreatefromjpeg($projectDir . '/' . $image);
                }
                break;

            case 'png':
                if ($arrGdinfo['PNG Support']) {
                    $strImage = imagecreatefrompng($projectDir . '/' . $image);
                }
                break;
        }

        // Image could not be read
        if (!$strImage) {
            return $image;
        }

        $objWatermark = new File($watermark);
        $resWatermark = null;

        // Load watermark
        switch ($objWatermark->extension) {
            case 'gif':
                if ($arrGdinfo['GIF Read Support']) {
                    $resWatermark = imagecreatefromgif($projectDir . '/' . $watermark);
                }
                break;

            case 'jpg':
            case 'jpeg':
                if ($arrGdinfo['JPG Support'] || $arrGdinfo['JPEG Support']) {
                    $resWatermark = imagecreatefromjpeg($projectDir . '/' . $watermark);
                }
                break;

            case 'png':
                if ($arrGdinfo['PNG Support']) {
                    $resWatermark = imagecreatefrompng($projectDir . '/' . $watermark);
                }
                break;
        }

        // Image could not be read
        if (!is_resource($resWatermark) && !$resWatermark instanceof \Contao\GdImage) {
            return $image;
        }

        switch ($position) {
            case 'left_top':
                $x = 0;
                $y = 0;
                break;

            case 'center_top':
                $x = ($objFile->width / 2) - ($objWatermark->width / 2);
                $y = 0;
                break;

            case 'right_top':
                $x = $objFile->width - $objWatermark->width;
                $y = 0;
                break;

            case 'left_center':
                $x = 0;
                $y = ($objFile->height / 2) - ($objWatermark->height / 2);
                break;

            case 'center_center':
                $x = ($objFile->width / 2) - ($objWatermark->width / 2);
                $y = ($objFile->height / 2) - ($objWatermark->height / 2);
                break;

            case 'right_center':
                $x = $objFile->width - $objWatermark->width;
                $y = ($objFile->height / 2) - ($objWatermark->height / 2);
                break;

            case 'left_bottom':
                $x = 0;
                $y = $objFile->height - $objWatermark->height;
                break;

            case 'center_bottom':
                $x = ($objFile->width / 2) - ($objWatermark->width / 2);
                $y = $objFile->height - $objWatermark->height;
                break;

            case 'right_bottom':
            default:
                $x = $objFile->width - $objWatermark->width;
                $y = $objFile->height - $objWatermark->height;
                break;
        }

        imagecopy($strImage, $resWatermark, $x, $y, 0, 0, $objWatermark->width, $objWatermark->height);

        // Fallback to PNG if GIF ist not supported
        if ($objFile->extension == 'gif' && !$arrGdinfo['GIF Create Support']) {
            $objFile->extension = 'png';
        }

        // Create an image parent folder if it does not exist yet
        if (!is_dir($projectDir . '/' . dirname($strCacheName))) {
            Files::getInstance()->mkdir(dirname($strCacheName));
        }

        // Create the new image
        switch ($objFile->extension) {
            case 'gif':
                imagegif($strImage, $projectDir . '/' . $strCacheName);
                break;

            case 'jpg':
            case 'jpeg':
                imagejpeg($strImage, $projectDir . '/' . $strCacheName, (!$GLOBALS['TL_CONFIG']['jpgQuality'] ? 80 : $GLOBALS['TL_CONFIG']['jpgQuality']));
                break;

            case 'png':
                imagepng($strImage, $projectDir . '/' . $strCacheName);
                break;
        }

        // Destroy the temporary images
        imagedestroy($strImage);
        imagedestroy($resWatermark);

        // Resize the original image
        if ($target) {
            $objFiles = Files::getInstance();
            $objFiles->copy($strCacheName, $target);

            return $target;
        }

        // Set the file permissions when the Safe Mode Hack is used
        if ($GLOBALS['TL_CONFIG']['useFTP'] ?? false) {
            $objFiles = Files::getInstance();
            $objFiles->chmod($strCacheName, 0644);
        }

        // Return the path to new image
        return $strCacheName;
    }

    /**
     * Gets an array for a Contao gallery in the "gallery_default" style.
     *
     * @param array|string $uuids Either an array of UUIDs or a serialized array of UUIDs
     * @param array $options      Options for the prepareImage() and sortImages() methods
     *
     * @return array
     */
    public static function getForGalleryTemplate($uuids, array $options)
    {
        $images = static::findImages($uuids, $options);

        if (isset($options['sortBy'])) {
            static::sortImages($images, $options['sortBy'], $options);
        }

        $body = [];

        foreach ($images as $k => $image) {
            $stdClass = $image['templateData'];
            $stdClass->class = $k % 2 === 0 ? 'odd' : 'even';
            $body['image'][] = $stdClass;
        }

        return [
            'body' => $body,
        ];
    }

    /**
     * Prepares the data for a typical Contao gallery fetching image data, meta data,
     * responsive images data etc. based on options.
     *
     * @param array|string $uuids Either an array of UUIDs or a serialized array of UUIDs
     * @param array $options      Options for the prepareImage() method
     *
     * return array
     */
    public static function findImages($uuids, array $options)
    {
        $images = [];

        $files = StringUtil::deserialize($uuids, true);

        $fileModels = FilesModel::findMultipleByUuids($files);

        if (null === $fileModels) {
            return [];
        }

        foreach ($fileModels as $fileModel) {
            // Single files
            if ('file' === $fileModel->type) {
                $file = new File($fileModel->path, true);

                if (!$file->exists() || !$file->isImage) {
                    continue;
                }

                $images[$fileModel->path] = static::prepareImage($fileModel, $options);
            }
            // Folders
            else {
                $subFileModels = FilesModel::findByPid($fileModel->uuid);

                if (null === $subFileModels) {
                    continue;
                }

                foreach ($subFileModels as $subFileModel) {
                    if ('file' === $subFileModel->type) {
                        $file = new File($subFileModel->path, true);

                        if (!$file->exists() || !$file->isImage) {
                            continue;
                        }

                        $images[$subFileModel->path] = static::prepareImage($subFileModel, $options);
                    }
                }
            }
        }

        return $images;
    }

    /**
     * Prepares one image for a typical Contao template.
     *
     * Possible option keys:
     *
     *  - language (language for meta data. If not specified, the language of the current page object is taken)
     *  - size (either integer with responsive image configuration ID or an array of three keys whereas 0 = width, 1 = height, 2 = crop mode)
     *  - fullsize (if provided, adds full size handling)
     *
     * @param FilesModel $fileModel
     * @param array $options
     *
     * return array
     */
    public static function prepareImage(FilesModel $fileModel, array $options)
    {
        $file = new File($fileModel->path, true);

        if (!isset($options['language']) && isset($GLOBALS['objPage'])) {
            $options['language'] = $GLOBALS['objPage']->language;
        }

        $meta = Frontend::getMetaData($fileModel->meta, $options['language'] ?? '');

        // Use the file name as title if none is given
        if ('' === ($meta['title'] ?? null)) {
            $meta['title'] = StringUtil::specialchars($file->basename);
        }

        $image = [
            'id'        => $fileModel->id,
            'uuid'      => $fileModel->uuid,
            'name'      => $file->basename,
            'singleSRC' => $fileModel->path,
            'alt'       => ($meta['title'] ?? null),
            'imageUrl'  => ($meta['link'] ?? null),
            'caption'   => ($meta['caption'] ?? null),
            'mtime'     => $file->mtime
        ];

        foreach (['size', 'fullsize'] as $k) {
            if (isset($options[$k])) {
                $image[$k] = $options[$k];
            }
        }

        // If fullsize was activated but no lightbox id provided, we have
        // to set one because Controller::addImageToTemplate() would generate
        // one by calling $template->getName() which does not exist on our
        // stdClass object
        if ($image['fullsize'] ?? false) {
            if (!isset($options['lightboxId'])) {
                $options['lightboxId'] = 'lightbox';
            } else {
                $options['lightboxId'] = 'lightbox[' . $options['lightboxId'] . ']';
            }
        }

        $stdClass = new \stdClass();

        Controller::addImageToTemplate(
            $stdClass,
            $image,
            isset($options['maxWidth']) ? $options['maxWidth'] : null,
            isset($options['lightboxId']) ? $options['lightboxId'] : null
        );

        $image['templateData'] = $stdClass;

        return $image;
    }

    /**
     * Sort an array of images based on the "prepareImage" format.
     *
     * Available sorting options (use the class constants!):
     *
     *  - \Haste\Image\Image::SORT_NAME_ASC (sort by name, ascending)
     *  - \Haste\Image\Image::SORT_NAME_DESC (sort by name, descending)
     *  - \Haste\Image\Image::SORT_DATE_ASC (sort by date, ascending)
     *  - \Haste\Image\Image::SORT_DATE_DESC (sort by date, descending)
     *  - \Haste\Image\Image::SORT_CUSTOM | needs the orderSRC options key)
     *  - \Haste\Image\Image::SORT_RANDOM (sort randomly)
     *
     * Possible options keys:
     *
     *  - orderSRC (multiple UUIDS, either as a serialized string or already as array containing the UUIDs in correct order)
     *
     * @param array  $images Array of images to sort
     * @param string $sortBy Sort by key
     * @param array  $options
     */
    public static function sortImages(array &$images, $sortBy = self::SORT_NAME_ASC, array $options = [])
    {
        switch ($sortBy) {
            case static::SORT_NAME_ASC:
            case static::SORT_NAME_DESC:
                uksort($images, function($a, $b) use ($sortBy) {
                    $cmp = strnatcasecmp(basename($a), basename($b));

                    if (static::SORT_NAME_DESC === $sortBy) {
                        $cmp = $cmp * -1;
                    }

                    return $cmp;
                });
                break;

            case static::SORT_DATE_ASC:
            case static::SORT_DATE_DESC:
                usort($images, function($a, $b) use ($sortBy) {
                    $cmp = $a['mtime'] > $b['mtime'];

                    if (static::SORT_DATE_DESC === $sortBy) {
                        $cmp = $cmp * -1;
                    }

                    return $cmp;
                });
                break;

            case static::SORT_CUSTOM:
                if (!isset($options['orderSRC'])) {
                    throw new \InvalidArgumentException('When sorting by custom order, you need to provide the "orderSRC" array option containing the UUIDs in correct order.');
                }

                $order = StringUtil::deserialize($options['orderSRC'], true);

                // Remove all values
                $order = array_map(function () {}, array_flip($order));

                // Move the matching elements to their position in $arrOrder
                foreach ($images as $k=>$v) {
                    if (array_key_exists($v['uuid'], $order)) {
                        $order[$v['uuid']] = $v;
                        unset($images[$k]);
                    }
                }

                // Append the left-over images at the end
                if (0 !== count($images)) {
                    $order = array_merge($order, array_values($images));
                }

                // Remove empty (unreplaced) entries
                $images = array_values(array_filter($order));
                unset($order);
                break;

            case static::SORT_RANDOM:
                shuffle($images);
                break;
        }
    }
}
