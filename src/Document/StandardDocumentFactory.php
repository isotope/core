<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Document;

use Codefog\HasteBundle\StringParser;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\Model;
use Isotope\CoreBundle\Model\DocumentModel;
use Isotope\CoreBundle\ModelType\ModelInstanceTrait;
use Isotope\CoreBundle\ModelType\ModelTypeFactoryInterface;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @implements ModelTypeFactoryInterface<DocumentInterface, DocumentModel>
 */
class StandardDocumentFactory implements ModelTypeFactoryInterface
{
    use ModelInstanceTrait;

    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly StringParser $stringParser,
        private readonly InsertTagParser $insertTagParser,
        #[Autowire(param: 'kernel.project_dir')] private readonly string $projectDir,
    ) {
    }

    /**
     * @param DocumentModel $model
     */
    public function create(Model $model, StoreContext $context): StandardDocument
    {
        $this->validateInstanceOf($model, DocumentModel::class);

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
