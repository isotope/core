<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\Page;

use Contao\CoreBundle\DependencyInjection\Attribute\AsPage;
use Contao\CoreBundle\Exception\ForwardPageNotFoundException;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\CoreBundle\Routing\ResponseContext\CoreResponseContextFactory;
use Contao\CoreBundle\Routing\ResponseContext\HtmlHeadBag\HtmlHeadBag;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use Contao\CoreBundle\String\HtmlDecoder;
use Contao\FrontendIndex;
use Contao\Input;
use Contao\PageModel;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Routing\ProductReference;
use Isotope\CoreBundle\Util\ProductCategoryUtil;
use Isotope\Model\Product;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsPage('iso_products')]
class ProductsPageController
{
    public function __construct(
        private readonly CoreResponseContextFactory $responseContextFactory,
        private readonly ResponseContextAccessor $responseContextAccessor,
        private readonly HtmlDecoder $htmlDecoder,
        private readonly ProductCategoryUtil $productCategoryUtil,
        private readonly ContentUrlGenerator $urlGenerator,
        private readonly AttributeRegistry $attributeRegistry,
    ) {
    }

    public function __invoke(Request $request, PageModel $pageModel): Response
    {
        Input::setUnusedRouteParameters([]); // Disable check on custom page controller

        $alias = ltrim($request->attributes->get('parameters'), '/');

        if (!$alias) {
            return (new FrontendIndex())->renderPage($pageModel);
        }

        $product = Product::findAvailableByIdOrAlias($alias);

        if (null === $product) {
            throw new PageNotFoundException();
        }

        $request->attributes->set('_content', $product);

        if ($pageModel->iso_readerJumpTo) {
            $readerPage = PageModel::findById($pageModel->iso_readerJumpTo);

            if (null === $readerPage) {
                throw new ForwardPageNotFoundException();
            }

            $this->setResponseContext($readerPage, $product);

            return (new FrontendIndex())->renderPage($readerPage);
        }

        $this->setResponseContext($pageModel, $product);

        return (new FrontendIndex())->renderPage($pageModel);
    }

    private function setResponseContext(PageModel $pageModel, Product $product): void
    {
        $context = $this->responseContextFactory->createContaoWebpageResponseContext($pageModel);

        $htmlHeadBag = $context->get(HtmlHeadBag::class);
        $htmlHeadBag
            ->setTitle($this->htmlDecoder->inputEncodedToPlainText($product->meta_title ?: $product->getName()))
            ->setMetaDescription($this->htmlDecoder->inputEncodedToPlainText($product->meta_description ?: $product->teaser ?: $product->description))
        ;

        if ($pageModel->enableCanonical) {
            $this->addCanonicalUrls($htmlHeadBag, $pageModel, $product);
        }

        $this->responseContextAccessor->setResponseContext($context);
    }

    private function addCanonicalUrls(HtmlHeadBag $htmlHeadBag, PageModel $pageModel, Product $product): void
    {
        $htmlHeadBag->setKeepParamsForCanonical(
            array_intersect($product->getType()->getVariantAttributes(), $this->attributeRegistry->getVariantOptionFields())
        );

        if ($pageModel->canonicalLink) {
            return;
        }

        $categories = $this->productCategoryUtil->getPublishedForProductInRoot((int) $pageModel->rootId, $product);

        // If there is only one page, we don't need to check for the page alias.
        if (1 === \count($categories)) {
            $htmlHeadBag->setCanonicalUri(
                $this->urlGenerator->generate(
                    new ProductReference($product, $categories[0]),
                    [],
                    UrlGeneratorInterface::ABSOLUTE_URL
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
                $this->urlGenerator->generate(
                    new ProductReference($product, $objJumpTo),
                    [],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
            );

            // Break on the first page that is found and the alias is not "index"
            break;
        }
    }
}
