<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller;

use Contao\CoreBundle\Framework\ContaoFramework;
use Isotope\PostSale;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\UriSigner;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/_isotope/postsale/{mod}/{id}', name: 'isotope_postsale', requirements: ['mod' => '(pay|ship)', 'id' => '\d+'], defaults: ['_scope' => 'frontend', '_token_check' => false, '_bypass_maintenance' => true])]
class PostsaleController
{
    public function __construct(private readonly ContaoFramework $framework, private readonly \Symfony\Component\HttpFoundation\UriSigner $uriSigner)
    {
    }

    public function __invoke(Request $request): Response
    {
        // Allow redirects to bypass POST data issues in payment return URLs
        if ($request->query->has('redirect')) {
            if ($this->uriSigner->check($request->getSchemeAndHttpHost() . $request->getBaseUrl() . $request->getPathInfo() . (null !== ($qs = $request->server->get('QUERY_STRING')) ? '?' . $qs : ''))) {
                return new RedirectResponse($request->query->get('redirect'));
            }

            return new Response('Bad Request', Response::HTTP_BAD_REQUEST);
        }

        $this->framework->initialize();

        return (new PostSale($request))->run();
    }
}
