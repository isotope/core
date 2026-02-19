<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ModelType;

use Isotope\CoreBundle\Document\DocumentInterface;
use Isotope\CoreBundle\Model\Document;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Contracts\Service\ServiceProviderInterface;

/**
 * @extends AbstractModelTypeRegistry<DocumentInterface, Document>
 */
#[Autoconfigure(public: true)]
class DocumentRegistry extends AbstractModelTypeRegistry
{
    public function __construct(ServiceProviderInterface $factories)
    {
        parent::__construct($factories, DocumentInterface::class);
    }
}
