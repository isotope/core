<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Download;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\File;
use Contao\FilesModel;
use Contao\Folder;
use Contao\Image;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCallback('tl_iso_download', 'list.label.label')]
class LabelListener
{
    public function __construct(
        #[Autowire(param: 'kernel.project_dir')] private readonly string $projectDir,
    )
    {
    }

    public function __invoke(array $row): string
    {
        /** @var FilesModel|null $filesModel */
        $filesModel = FilesModel::findByUuid($row['singleSRC']);

        if (!$filesModel) {
            return '<p class="error">'.$GLOBALS['TL_LANG']['ERR']['invalidName'].'</p>';
        }

        $path = $filesModel->path;

        if ('folder' === $filesModel->type) {
            $arrDownloads = array();

            foreach (Folder::scan($this->projectDir.'/'.$path) as $file) {
                if (is_file($this->projectDir.'/'.$path.'/'.$file)) {
                    $objFile = new File($path.'/'.$file);
                    $icon = Image::getHtml($objFile->icon);
                    $arrDownloads[] = sprintf('<div style="margin-bottom:5px;height:16px;">%s %s/%s</div>', $icon, $path, $file);
                }
            }

            if (0 === \count($arrDownloads)) {
                return $GLOBALS['TL_LANG']['ERR']['emptyDownloadsFolder'];
            }

            return '<div style="margin-bottom:5px;height:16px;font-weight:bold">'.$path.'</div>'.implode("<br>\n", $arrDownloads);
        }

        $objFile = new File($path);
        $icon = Image::getHtml($objFile->icon);

        return sprintf('<div style="height: 16px">%s %s</div>', $icon, $path);
    }
}
