<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\FrontendModule;

use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\ModuleModel;
use Isotope\CoreBundle\Product\Render\DefaultProductRenderer;
use Isotope\CoreBundle\Product\Render\RenderConfig;
use Isotope\Model\Product;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule('iso_productreader', category: 'isotope')]
class ProductReaderController extends AbstractFrontendModuleController
{
    public function __construct(
        private readonly DefaultProductRenderer $productRenderer,
        private readonly RequestStack $requestStack,
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        $product = $request->attributes->get('_content');

        if (!$product instanceof Product) {
            if ($model->iso_display404Page) {
                throw new PageNotFoundException();
            }

            return new Response();
        }

        $template->product = $this->productRenderer->render($product, RenderConfig::createReader($model, $product), $request);
        $template->referer = $this->generateContentUrl($this->requestStack->getMainRequest()->attributes->get('pageModel'));

        return $template->getResponse();
    }
}
