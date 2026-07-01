<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener;

use Contao\CoreBundle\Event\SitemapEvent;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\Database;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\Product\DataCollector\ProductDto;
use Isotope\CoreBundle\Product\DataCollector\ProductDtoCollection;
use Isotope\CoreBundle\Product\ProductBuilder;
use Isotope\CoreBundle\Store\StoreManager;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Routing\Exception\ExceptionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsEventListener]
class SitemapListener
{
    public function __construct(
        private readonly Connection $connection,
        private readonly ProductBuilder $productBuilder,
        private readonly StoreManager $storeManager,
        private readonly ContentUrlGenerator $urlGenerator,
    ) {
    }

    public function __invoke(SitemapEvent $event): void
    {
        $pageIds = Database::getInstance()->getChildRecords($event->getRootPageIds(), 'tl_page');
        $productIds = $this->connection->fetchFirstColumn(
            'SELECT p.id FROM tl_iso_product p JOIN tl_iso_product_category c ON p.id=c.product_id WHERE c.pid IN (?)',
            [$pageIds],
            [ArrayParameterType::INTEGER],
        );

        $dtoCollection = new ProductDtoCollection(array_map(static fn (int|string $id) => new ProductDto('tl_iso_product.'.$id), $productIds));
        $context = $this->storeManager->getContextForRequest($event->getRequest());

        $products = $this->productBuilder->build($dtoCollection, $context);
        $sitemap = $event->getDocument();
        $urlSet = $sitemap->firstElementChild;

        foreach ($products as $product) {
            try {
                $url = $this->urlGenerator->generate($product, [], UrlGeneratorInterface::ABSOLUTE_URL);

                $loc = $sitemap->createElementNS($urlSet->namespaceURI, 'loc');
                $loc->appendChild($sitemap->createTextNode($url));

                $urlEl = $sitemap->createElementNS($urlSet->namespaceURI, 'url');
                $urlEl->appendChild($loc);
                $urlSet->appendChild($urlEl);
            } catch (ExceptionInterface) {
                // ignore product if URL cannot be generated
            }
        }

    }
}
