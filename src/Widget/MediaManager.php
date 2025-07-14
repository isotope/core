<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Widget;

use Contao\Database;
use Contao\File;
use Contao\Image;
use Contao\StringUtil;
use Contao\System;
use Contao\UploadableWidgetInterface;
use Contao\Widget;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class MediaManager extends Widget implements UploadableWidgetInterface
{
    public const TEMP_FOLDER = 'assets/images';

    /**
     * @var bool
     */
    protected $blnSubmitInput = true;

    /**
     * @var string
     */
    protected $strTemplate = 'be_widget';

    private array $validExtensions = [];

    public function __set($strKey, $varValue): void
    {
        switch ($strKey) {
            case 'value':
                $this->varValue = StringUtil::deserialize($varValue);
                break;

            case 'extensions':
                $this->validExtensions = \is_array($varValue) ? $varValue : explode(',', (string) $varValue);
                break;

            default:
                parent::__set($strKey, $varValue);
                break;
        }
    }

    public function generate(): string
    {
        $blnLanguage = false;
        $arrFallback = $this->getFallbackData();

        if (!\is_array($this->varValue)) {
            $this->varValue = [];
        }

        // Merge parent record data
        if (null !== $arrFallback) {
            $blnLanguage = true;
            $this->varValue = static::mergeMediaData($this->varValue, $arrFallback);
        }

        $files = [];

        foreach ($this->varValue as $media) {
            $path = $this->getFilePath($media['src']);
            $file = new File($this->getFilePath($media['src']));

            if (!$file->exists()) {
                continue;
            }

            $files[] = [
                ...$media,
                'path' => $path,
                'width' => $file->width,
                'icon' => 'assets/contao/images/'.$file->icon,
                'isImage' => $file->isImage,
            ];
        }

        return System::getContainer()->get('twig')->render('@Contao/backend/iso_mediamanager.html.twig', [
            'name' => $this->strName,
            'accept' => array_map(static fn ($v) => '.'.$v, $this->validExtensions),
            'isLanguage' => $blnLanguage,
            'dragIcon' => Image::getHtml('drag.svg', $GLOBALS['TL_LANG']['MSC']['move'] ?? ''),
            'deleteIcon' => Image::getHtml('delete.svg', $GLOBALS['TL_LANG']['MSC']['lw_delete'] ?? ''),
            'files' => $files,
        ]);
    }

    public static function mergeMediaData(array $arrCurrent, array $arrParent): array
    {
        $arrTranslate = [];

        // Create an array of images where key = image name
        foreach ($arrParent as $image) {
            if ('all' !== ($image['translate'] ?? null)) {
                $arrTranslate[$image['src']] = $image;
            }
        }

        if ([] === $arrCurrent) {
            return array_values($arrTranslate);
        }

        foreach ($arrCurrent as $i => $image) {
            if (isset($arrTranslate[$image['src']])) {
                if ('none' === ($arrTranslate[$image['src']]['translate'] ?? null)) {
                    $arrCurrent[$i] = $arrTranslate[$image['src']];
                } else {
                    $arrCurrent[$i]['link'] = $arrTranslate[$image['src']]['link'] ?? '';
                    $arrCurrent[$i]['translate'] = $arrTranslate[$image['src']]['translate'] ?? '';
                }

                unset($arrTranslate[$image['src']]);
            } elseif ('all' !== $arrCurrent[$i]['translate']) {
                unset($arrCurrent[$i]);
            }
        }

        // Add remaining parent image to the list
        if (!empty($arrTranslate)) {
            $arrCurrent = array_merge($arrCurrent, array_values($arrTranslate));
        }

        return array_values($arrCurrent);
    }

    protected function validator($varInput): array|null
    {
        if (!\is_array($varInput)) {
            $varInput = [];
        }

        // Fetch fallback language record
        $arrFallback = $this->getFallbackData();
        $projectDir = System::getContainer()->getParameter('kernel.project_dir');

        if (\is_array($arrFallback)) {
            foreach ($arrFallback as $k => $arrImage) {
                if ('all' === ($arrImage['translate'] ?? null)) {
                    unset($arrFallback[$k]);
                }
            }
        }

        // Check that image is not assigned in fallback language
        foreach ($varInput as $k => $v) {
            if (\is_array($arrFallback) && \in_array($v, $arrFallback, false)) {
                $this->addError($GLOBALS['TL_LANG']['ERR']['imageInFallback']);
            } elseif (null !== $arrFallback) {
                $varInput[$k]['translate'] = 'all';
            }
        }

        // Move all temporary files
        foreach ($varInput as $k => $v) {
            $pathinfo = pathinfo((string) $v['src']);

            if ([] !== $this->validExtensions && !\in_array($pathinfo['extension'], $this->validExtensions, true)) {
                $this->addError(\sprintf($GLOBALS['TL_LANG']['ERR']['filetype'], $pathinfo['extension']));
                continue;
            }

            if (false !== stripos((string) $v['src'], self::TEMP_FOLDER)) {
                $strFile = $this->getFilePath(basename((string) $v['src']));

                if (
                    is_file($projectDir . '/' . $strFile)
                    && md5_file($projectDir . '/' .  $v['src']) !== md5_file($projectDir . '/' . $strFile)
                ) {
                    $strFile = $this->getFilePath(
                        StringUtil::standardize($pathinfo['filename']) . '-' . substr(md5_file($projectDir . '/' .  $strFile), 0, 8) . '.' . $pathinfo['extension']
                    );
                }

                $fs = new Filesystem();

                // Make sure the parent folder exists
                $fs->mkdir(\dirname($strFile));

                try {
                    $fs->copy($v['src'], $strFile);
                    $varInput[$k]['src'] = basename($strFile);
                } catch (IOException $e) {
                    $this->addError($e->getMessage());
                }
            }
        }

        // Check if there are values
        if ($this->mandatory) {
            $hasFile = false;
            foreach ($varInput as $file) {
                if (is_file($projectDir . '/' . $this->getFilePath($file['src']))) {
                    $hasFile = true;
                    break;
                }
            }

            if (!$hasFile && (!\is_array($arrFallback) || empty($arrFallback))) {
                $this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['mandatory'], $this->strLabel));
            }
        }

        if (empty($varInput)) {
            $varInput = null;
        }

        return $varInput;
    }

    /**
     * Get the file path or folder only.
     */
    protected function getFilePath(string $file, bool $folder = false): string
    {
        if (false !== stripos($file, self::TEMP_FOLDER)) {
            return $folder ? \dirname($folder) : $file;
        }

        return 'isotope/'.$file[0].($folder ? '' : '/'.$file);
    }

    /**
     * Retrieve image data from fallback language.
     */
    protected function getFallbackData(): array|null
    {
        // Fetch fallback language record
        if ($this->objDca->editLanguage) {
            $data = StringUtil::deserialize(Database::getInstance()->execute("SELECT {$this->strField} FROM {$this->strTable} WHERE id={$this->currentRecord}")->{$this->strField});

            if (\is_array($data)) {
                return $data;
            }
        }

        return null;
    }
}
