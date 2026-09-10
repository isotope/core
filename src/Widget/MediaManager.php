<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Widget;

use Contao\File;
use Contao\StringUtil;
use Contao\System;
use Contao\UploadableWidgetInterface;
use Contao\Widget;
use Doctrine\DBAL\Connection;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Terminal42\DcMultilingualBundle\Driver;

/**
 * @phpstan-type MediaFile array{src: string, link?: string, alt?: string, desc?: string, translate?: "all"|"text"|"none"}
 */
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

    #[\Override]
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
                'width' => max($file->width ?: 500, 500),
                'icon' => 'assets/contao/images/'.$file->icon,
                'isImage' => $file->isImage,
            ];
        }

        return System::getContainer()->get('twig')->render('@Contao/backend/iso_mediamanager.html.twig', [
            'id' => 'ctrl_'.$this->strId,
            'name' => $this->strName,
            'accept' => array_map(static fn ($v) => '.'.$v, $this->validExtensions),
            'is_language' => $blnLanguage,
            'files' => $files,
            'attributes' => $this->getAttributes(),
        ]);
    }

    /**
     * @return array<MediaFile>
     */
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

    #[\Override]
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

        // Move all temporary files
        foreach ($varInput as $k => $v) {
            if (\is_array($arrFallback)) {
                foreach ($arrFallback as $image) {
                    if ($v['src'] === $image['src'] && $v['translate'] !== $image['translate']) {
                        $this->addError($GLOBALS['TL_LANG']['ERR']['imageInFallback']);
                        continue 2;
                    }
                }
            }

            if (null !== $arrFallback) {
                $varInput[$k]['translate'] = 'all';
            }

            $pathinfo = pathinfo((string) $v['src']);

            if ([] !== $this->validExtensions && !\in_array($pathinfo['extension'], $this->validExtensions, true)) {
                $this->addError(\sprintf($GLOBALS['TL_LANG']['ERR']['filetype'], $pathinfo['extension']));
                continue;
            }

            if (false !== stripos((string) $v['src'], self::TEMP_FOLDER)) {
                $strFile = $this->getFilePath(basename((string) $v['src']));

                if (
                    is_file($projectDir.'/'.$strFile)
                    && hash_file('xxh3', $projectDir.'/'.$v['src']) !== ($hash = hash_file('xxh3', $projectDir.'/'.$strFile))
                ) {
                    $strFile = $this->getFilePath(
                        StringUtil::standardize($pathinfo['filename']).'-'.$hash.'.'.$pathinfo['extension'],
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
            $hasFile = array_any($varInput, fn ($file) => is_file($projectDir.'/'.$this->getFilePath($file['src'])));
            if (!$hasFile && (!\is_array($arrFallback) || empty($arrFallback))) {
                $this->addError(\sprintf($GLOBALS['TL_LANG']['ERR']['mandatory'], $this->strLabel));
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
    private function getFilePath(string $file): string
    {
        if (false !== stripos($file, self::TEMP_FOLDER)) {
            return $file;
        }

        return 'isotope/'.$file[0].'/'.$file;
    }

    /**
     * Retrieve image data from fallback language.
     *
     * @return array<MediaFile>|null
     */
    private function getFallbackData(): array|null
    {
        $dc = $this->objDca;

        if (!$dc instanceof Driver || !$dc->getCurrentLanguage()) {
            return null;
        }

        /** @var Connection $connection */
        $connection = System::getContainer()->get('database_connection');
        $data = StringUtil::deserialize($connection->fetchOne("SELECT {$this->strField} FROM {$this->strTable} WHERE id={$this->currentRecord}"));

        if (\is_array($data)) {
            return $data;
        }

        return null;
    }
}
