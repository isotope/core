<?php

declare(strict_types=1);

namespace Isotope\Controller;

use Contao\CoreBundle\Framework\ContaoFramework;
use Isotope\PostSale;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\UriSigner;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(name="isotope_postsale", path="/_isotope/postsale/{mod}/{id}", requirements={"mod" = "(pay|ship)", "id" = "\d+"}, defaults={"_scope" = "frontend", "_token_check" = false, "_bypass_maintenance" = true})
 * @Route(path="/system/modules/isotope/postsale.php", defaults={"_scope" = "frontend", "_token_check" = false, "_bypass_maintenance" = true})
 */
class PostsaleController
{
    /**
     * @var ContaoFramework
     */
    private $framework;

    /**
     * @var UriSigner
     */
    private $uriSigner;

    public function __construct(ContaoFramework $framework, UriSigner $uriSigner)
    {
        $this->framework = $framework;
        $this->uriSigner = $uriSigner;
    }

    public function __invoke(Request $request)
    {
        // Allow redirects to bypass POST data issues in payment return URLs
        if ($request->query->has('redirect')) {
            if ($this->uriSigner->check($request->getSchemeAndHttpHost() . $request->getBaseUrl() . $request->getPathInfo() . (null !== ($qs = $request->server->get('QUERY_STRING')) ? '?' . $qs : ''))) {
                return new RedirectResponse($request->query->get('redirect'));
            }

            return new Response('Bad Request', Response::HTTP_BAD_REQUEST);
        }

        $this->framework->initialize();

        $postsale = new PostSale($request);

        return $postsale->run();
    }
}
