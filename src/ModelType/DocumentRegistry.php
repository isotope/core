<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ModelType;

use Isotope\CoreBundle\Document\DocumentInterface;
use Isotope\CoreBundle\Model\DocumentModel;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Contracts\Service\ServiceProviderInterface;

/**
 * @extends AbstractModelTypeRegistry<DocumentInterface, DocumentModel>
 */
#[Autoconfigure(public: true)]
class DocumentRegistry extends AbstractModelTypeRegistry
{
    public function __construct(ServiceProviderInterface $factories)
    {
        parent::__construct($factories, DocumentInterface::class);
    }
}
