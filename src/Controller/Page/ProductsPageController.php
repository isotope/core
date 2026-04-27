<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\Page;

use Contao\CoreBundle\Controller\Page\AbstractPageController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsPage;
use Contao\CoreBundle\Exception\BadRequestException;
use Contao\CoreBundle\Exception\ForwardPageNotFoundException;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Routing\ResponseContext\CoreResponseContextFactory;
use Contao\CoreBundle\Routing\ResponseContext\HtmlHeadBag\HtmlHeadBag;
use Contao\CoreBundle\String\HtmlDecoder;
use Contao\Input;
use Contao\PageModel;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Model\ProductModel;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\DataCollector\ProductDto;
use Isotope\CoreBundle\Product\Extension\MetadataExtension;
use Isotope\CoreBundle\Product\ProductBuilder;
use Isotope\CoreBundle\Product\Query\ProductQueryFactory;
use Isotope\CoreBundle\Routing\ProductReference;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Util\ProductCategoryUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsPage('iso_products')]
class ProductsPageController extends AbstractPageController
{
    public function __construct(
        private readonly CoreResponseContextFactory $responseContextFactory,
        private readonly HtmlDecoder $htmlDecoder,
        private readonly ProductCategoryUtil $productCategoryUtil,
        private readonly AttributeRegistry $attributeRegistry,
        private readonly ProductQueryFactory $queryFactory,
        private readonly ProductBuilder $productBuilder,
    ) {
    }

    public function __invoke(Request $request, PageModel $pageModel, StoreContext $context): Response
    {
        Input::setUnusedRouteParameters([]); // Disable check on custom page controller

        $alias = ltrim($request->attributes->getString('parameters'), '/');

        if (!$alias) {
            return $this->buildPage($pageModel, $request);
        }

        $product = ProductModel::findByMultilingualAliasOrId($alias);

        if (!$product) {
            throw new PageNotFoundException();
        }

        $product = $this->productBuilder->buildOne(new ProductDto('tl_iso_product.'.$product->getLanguageId()), $context);

        if (!$product instanceof CoreProduct || !$product->isAvailable()) {
            throw new PageNotFoundException();
        }

        if ($pageModel->iso_readerJumpTo) {
            $readerPage = PageModel::findById($pageModel->iso_readerJumpTo);

            if (null === $readerPage) {
                throw new ForwardPageNotFoundException();
            }

            return $this->buildPage($readerPage, $request, $product);
        }

        return $this->buildPage($pageModel, $request, $product);
    }

    private function buildPage(PageModel $pageModel, Request $request, CoreProduct|null $product = null): Response
    {
        $pageModel->loadDetails();
        $originalPageModel = $request->attributes->get('pageModel');

        $request->attributes->set('_content', $product);
        $request->attributes->set('_product_query', $this->queryFactory->createFromRequest($request));
        $request->attributes->set('_isotope_fragment', $fragmentId = $request->headers->get('Isotope-Fragment'));

        // Override page model in the request
        $request->attributes->set('originalPageModel', $originalPageModel);
        $request->attributes->set('pageModel', $pageModel);

        if ($product) {
            $pageModel->trail = $originalPageModel->trail;
            $pageModel->title = $product->name;
        }

        $context = $this->responseContextFactory->createContaoWebpageResponseContext($pageModel);

        if ($product && $context->has(HtmlHeadBag::class)) {
            $htmlHeadBag = $context->get(HtmlHeadBag::class);

            if ($metadata = $product->getExtension(MetadataExtension::class)) {
                $htmlHeadBag
                    ->setTitle($this->htmlDecoder->inputEncodedToPlainText($metadata->getTitle()))
                    ->setMetaDescription($this->htmlDecoder->inputEncodedToPlainText($metadata->getDescription()))
                ;
            }

            if ($pageModel->enableCanonical) {
                $this->addCanonicalUrls($htmlHeadBag, $pageModel, $product);
            }
        }

        $response = $this->renderPage($pageModel, $context);
        $response->setVary('Isotope-Fragment', false);

        // A response exception should have been thrown by the fragment on this page
        if (null !== $fragmentId) {
            throw new BadRequestException();
        }

        return $response;
    }

    private function addCanonicalUrls(HtmlHeadBag $htmlHeadBag, PageModel $pageModel, CoreProduct $product): void
    {
        $htmlHeadBag->setKeepParamsForCanonical(
            array_intersect($product->getProductType()->getVariantAttributes(), $this->attributeRegistry->getVariantOptionFields()),
        );

        if ($pageModel->canonicalLink) {
            return;
        }

        $categories = $this->productCategoryUtil->getPublishedForProductInRoot((int) $pageModel->rootId, $product);

        // If there is only one page, we don't need to check for the page alias.
        if (1 === \count($categories)) {
            $htmlHeadBag->setCanonicalUri(
                $this->generateContentUrl(
                    new ProductReference($product, array_first($categories)),
                    [],
                    UrlGeneratorInterface::ABSOLUTE_URL,
                ),
            );
        }

        foreach ($categories as $pageId) {
            $objJumpTo = PageModel::findPublishedById($pageId);

            if (!$objJumpTo) {
                continue;
            }

            // Do not use the index page as canonical link
            if ('index' === $objJumpTo->alias) {
                continue;
            }

            $htmlHeadBag->setCanonicalUri(
                $this->generateContentUrl(
                    new ProductReference($product, $objJumpTo),
                    [],
                    UrlGeneratorInterface::ABSOLUTE_URL,
                ),
            );

            // Break on the first page that is found and the alias is not "index"
            break;
        }
    }
}
