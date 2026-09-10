<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller;

use Contao\CoreBundle\Controller\AbstractController;
use Contao\CoreBundle\Exception\BadRequestException;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Isotope\CoreBundle\Store\DocumentManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('_isotope/document/{id}', requirements: ['id' => '\d+'])]
class DocumentController extends AbstractController
{
    public function __construct(
        private readonly DocumentManager $documentManager,
        private readonly UriSigner $uriSigner,
    ) {
    }

    public function __invoke(Request $request, int $id): Response
    {
        if (!$this->uriSigner->checkRequest($request)) {
            throw new BadRequestException();
        }

        $this->initializeContaoFramework();

        $file = $this->documentManager->find($id);

        if (!$file) {
            throw new PageNotFoundException();
        }

        return $file->getResponse();
    }
}
