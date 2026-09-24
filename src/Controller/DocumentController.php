<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller;

use Contao\CoreBundle\Controller\AbstractController;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Isotope\CoreBundle\Store\DocumentManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('_isotope/document/{uuid}')]
class DocumentController extends AbstractController
{
    public function __construct(
        private readonly DocumentManager $documentManager,
    ) {
    }

    public function __invoke(Request $request, string $uuid): Response
    {
        $this->initializeContaoFramework();

        $file = $this->documentManager->find($uuid);

        if (!$file) {
            throw new PageNotFoundException();
        }

        return $file->getResponse();
    }
}
