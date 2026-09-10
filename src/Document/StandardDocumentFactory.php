<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Document;

use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\Model;
use Isotope\CoreBundle\Model\DocumentModel;
use Isotope\CoreBundle\ModelType\ModelInstanceTrait;
use Isotope\CoreBundle\ModelType\ModelTypeFactoryInterface;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Document\NumberFormat;

/**
 * @implements ModelTypeFactoryInterface<DocumentInterface, DocumentModel>
 */
class StandardDocumentFactory implements ModelTypeFactoryInterface
{
    use ModelInstanceTrait;

    public function __construct(private readonly InsertTagParser $insertTagParser)
    {
    }

    /**
     * @param DocumentModel $model
     */
    public function create(Model $model, StoreContext $context): StandardDocument
    {
        $this->validateInstanceOf($model, DocumentModel::class);

        $document = new StandardDocument(
            $model->id,
            $model->fileTitle,
            new NumberFormat($this->insertTagParser->replace($model->numberPrefix), (int) $model->numberDigits),
            '@Contao/'.($model->customTpl ?: 'isotope/document/standard').'.html.twig',
        );

        $document
            ->setDocumentTitle($this->insertTagParser->replace($model->documentTitle))
            ->setGalleryId($model->gallery ?: null)
        ;

        return $document;
    }
}
