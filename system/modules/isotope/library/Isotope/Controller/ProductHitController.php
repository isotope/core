<?php

declare(strict_types=1);

namespace Isotope\Controller;

use Doctrine\DBAL\Connection;
use Hashids\Hashids;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(name="isotope_product_hits", path="/_isotope/product-hit/{hashid}", requirements={"id" = ".+"}, defaults={"_scope" = "frontend"})
 */
class ProductHitController
{
    private Connection $connection;
    private string $secret;

    public function __construct(Connection $connection, string $secret)
    {
        $this->connection = $connection;
        $this->secret = $secret;
    }

    public function __invoke(string $hashid): Response
    {
        $hashids = new Hashids($this->secret, 8);

        try {
            $this->connection->executeStatement(
                'UPDATE tl_iso_product SET hitCount=hitCount+1 WHERE id IN (?)',
                [$hashids->decode($hashid)],
                [Connection::PARAM_INT_ARRAY]
            );
        } catch (\Throwable $e) {
            // ignore
        }

        $response = new Response(base64_decode('R0lGODlhAQABAJAAAP8AAAAAACH5BAUQAAAALAAAAAABAAEAAAICBAEAOw=='));
        $response->headers->set('Content-Type', 'image/gif');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache');
        $response->headers->addCacheControlDirective('must-revalidate');

        return $response;
    }
}
