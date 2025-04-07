<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Document;

use Codefog\HasteBundle\StringParser;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Isotope\Frontend;
use Isotope\Interfaces\IsotopeProductCollection;

abstract class AbstractDocument implements DocumentInterface
{
    private string $documentTitle = '';

    private string $documentTemplate = 'iso_document_default';

    private string $collectionTemplate = 'iso_collection_default';

    private string $orderCollectionBy = 'asc_id';

    private int|null $galleryId = null;

    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly StringParser $stringParser,
        private readonly int $id,
        private readonly string $fileTitle,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFileTitle(): string
    {
        return $this->fileTitle;
    }

    public function getDocumentTitle(): string
    {
        return $this->documentTitle;
    }

    public function setDocumentTitle(string $documentTitle): self
    {
        $this->documentTitle = $documentTitle;

        return $this;
    }

    public function getDocumentTemplate(): string
    {
        return $this->documentTemplate;
    }

    public function setDocumentTemplate(string $documentTemplate): self
    {
        $this->documentTemplate = $documentTemplate;

        return $this;
    }

    public function getCollectionTemplate(): string
    {
        return $this->collectionTemplate;
    }

    public function setCollectionTemplate(string $collectionTemplate): self
    {
        $this->collectionTemplate = $collectionTemplate;

        return $this;
    }

    public function getOrderCollectionBy(): string
    {
        return $this->orderCollectionBy;
    }

    public function setOrderCollectionBy(string $orderCollectionBy): self
    {
        $this->orderCollectionBy = $orderCollectionBy;

        return $this;
    }

    public function getGalleryId(): int|null
    {
        return $this->galleryId;
    }

    public function setGalleryId(int|null $galleryId): self
    {
        $this->galleryId = $galleryId;

        return $this;
    }

    /**
     * Loads the page configuration and language before generating a PDF.
     */
    protected function prepareEnvironment(IsotopeProductCollection $objCollection): void
    {
        global $objPage;

        if (
            !\is_object($objPage)
            && $objCollection->pageId > 0
            && ($objPage = $this->framework->getAdapter(PageModel::class)->findWithDetails($objCollection->pageId))
        ) {
            $objPage = Frontend::loadPageConfig($objPage);

            System::loadLanguageFile('default', $GLOBALS['TL_LANGUAGE'], true);
        }
    }

    protected function prepareCollectionTokens(IsotopeProductCollection $objCollection): array
    {
        $arrTokens = [];

        foreach ($objCollection->row() as $k => $v) {
            $arrTokens['collection_'.$k] = $v;
        }

        return $arrTokens;
    }

    protected function prepareFileName(string $strName, array $arrTokens = [], string $strPath = ''): string
    {
        // Replace simple tokens
        $strName = $this->sanitizeFileName(
            $this->stringParser->recursiveReplaceTokensAndTags(
                $strName,
                $arrTokens,
                StringParser::NO_TAGS | StringParser::NO_BREAKS | StringParser::NO_ENTITIES,
            ),
        );

        if ($strPath) {
            // Make sure the path contains a trailing slash
            $strPath = preg_replace('/([^\/]+)$/', '$1/', $strPath);

            $strName = $strPath.$strName;
        }

        return $strName;
    }

    protected function sanitizeFileName(string $strName, bool $blnPreserveUppercase = true): string
    {
        return StringUtil::standardize(StringUtil::ampersand($strName, false), $blnPreserveUppercase);
    }
}
