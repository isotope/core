<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\Backend;

use Contao\CoreBundle\Controller\AbstractController;
use Contao\FileUpload;
use Contao\Message;
use Contao\StringUtil;
use Contao\System;
use Isotope\CoreBundle\Exception\RuntimeException;
use Isotope\CoreBundle\Security\IsotopeCorePermissions;
use Isotope\CoreBundle\Widget\MediaManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('%contao.backend.route_prefix%/isotope-mediamanager-upload', 'iso_mediamanager_upload', defaults: ['_scope' => 'backend', '_token_check' => false], methods: ['POST'])]
class MediaManagerUploadController extends AbstractController
{
    private const NAME = 'file';

    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly TranslatorInterface $translator,
        #[Autowire(param: 'kernel.project_dir')] private readonly string $projectDir,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(IsotopeCorePermissions::USER_CAN_ACCESS_PRODUCTS);

        $this->initializeContaoFramework();
        System::loadLanguageFile('default');

        try {
            return new JsonResponse(['success' => true, 'file' => $this->validateUpload()]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function validateUpload(): string
    {
        Message::reset();

        try {
            $objUploader = new FileUpload();
            $objUploader->setName(self::NAME);
            $uploadFolder = MediaManager::TEMP_FOLDER;

            // Convert the $_FILES array to Contao format
            if (!empty($_FILES[self::NAME])) {
                $pathinfo = pathinfo(strtolower((string) $_FILES[self::NAME]['name']));
                $strCacheName = StringUtil::standardize($pathinfo['filename']).'.'.$pathinfo['extension'];
                $uploadFolder = MediaManager::TEMP_FOLDER.'/'.$strCacheName[0];

                if (
                    is_file($this->projectDir.'/'.$uploadFolder.'/'.$strCacheName)
                    && hash_file('xxh3', $this->projectDir.'/'.$uploadFolder.'/'.$strCacheName) !== ($hash = hash_file('xxh3', $_FILES[self::NAME]['tmp_name']))
                ) {
                    $strCacheName = StringUtil::standardize($pathinfo['filename']).'-'.$hash.'.'.$pathinfo['extension'];
                }

                $this->filesystem->mkdir($uploadFolder);

                $_FILES[self::NAME] = [
                    'name' => [$strCacheName],
                    'type' => [$_FILES[self::NAME]['type']],
                    'tmp_name' => [$_FILES[self::NAME]['tmp_name']],
                    'error' => [$_FILES[self::NAME]['error']],
                    'size' => [$_FILES[self::NAME]['size']],
                ];
            }

            $varInput = $objUploader->uploadTo($uploadFolder);

            if ($objUploader->hasError()) {
                $messages = System::getContainer()
                    ->get('request_stack')
                    ->getSession()
                    ->getFlashBag()
                    ->peek('contao.BE.error')
                ;

                if (\is_array($messages)) {
                    throw new RuntimeException($messages[0]);
                }
            }
        } finally {
            Message::reset();
        }

        if (!\is_array($varInput) || empty($varInput)) {
            throw new RuntimeException($this->translator->trans('MSC.mmUnknownError', [], 'contao_default'));
        }

        return $varInput[0] ?? '';
    }
}
