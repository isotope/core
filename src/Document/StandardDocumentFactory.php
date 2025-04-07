<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Document;

use Codefog\HasteBundle\StringParser;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\Model;
use Isotope\CoreBundle\Model\Document;
use Isotope\CoreBundle\ModelType\ModelTypeFactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class StandardDocumentFactory implements ModelTypeFactoryInterface
{
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly StringParser $stringParser,
        private readonly InsertTagParser $insertTagParser,
        #[Autowire(param: 'kernel.project_dir')] private readonly string $projectDir,
    ) {
    }

    /**
     * @param Document $model
     */
    public function create(Model $model): StandardDocument
    {
        if (!$model instanceof Document) {
            throw new \RuntimeException(\sprintf('%s should be an instance of %s', $model::class, Document::class));
        }

        $document = new StandardDocument(
            $this->framework,
            $this->stringParser,
            $this->insertTagParser,
            $model->id,
            $model->fileTitle,
            $this->projectDir,
        );

        $document
            ->setDocumentTitle($model->documentTitle)
            ->setDocumentTemplate($model->documentTpl)
            ->setCollectionTemplate($model->collectionTpl)
            ->setOrderCollectionBy($model->orderCollectionBy)
            ->setGalleryId($model->gallery ?: null)
        ;

        return $document;
    }
}
