<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Hashids\Hashids;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/_isotope/product-hit/{hashid}', name: 'isotope_product_hits', requirements: ['id' => '.+'], defaults: ['_scope' => 'frontend'])]
class ProductHitController
{
    public function __construct(
        private readonly Connection $connection,
        #[Autowire(service: 'isotope.hashids')] private readonly Hashids $hashids,
    ) {
    }

    public function __invoke(string $hashid): Response
    {
        try {
            $this->connection->executeStatement(
                'UPDATE tl_iso_product SET hitCount=hitCount+1 WHERE id IN (?)',
                [$this->hashids->decode($hashid)],
                [ArrayParameterType::INTEGER],
            );
        } catch (\Throwable) {
            // ignore
        }

        $response = new Response(base64_decode('R0lGODlhAQABAJAAAP8AAAAAACH5BAUQAAAALAAAAAABAAEAAAICBAEAOw==', true));
        $response->headers->set('Content-Type', 'image/gif');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache');
        $response->headers->addCacheControlDirective('must-revalidate');

        return $response;
    }
}
