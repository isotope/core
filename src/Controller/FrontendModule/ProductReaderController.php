<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\CoreBundle\String\HtmlAttributes;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\ModuleModel;
use Contao\StringUtil;
use Isotope\CoreBundle\Product\RenderConfig;
use Isotope\Model\Product;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule('iso_productreader', priority: 10)]
class ProductReaderController extends AbstractFrontendModuleController
{
    public function __construct(private readonly RequestStack $requestStack)
    {
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

        $config = RenderConfig::createFromModule($model, $product);
        $content = $product->generate($config->toArray());

        if (
            !$model->iso_disable_options
            && $request->isXmlHttpRequest()
            && $request->request->getInt('AJAX_MODULE') === (int) $model->id
            && $request->request->getInt('AJAX_PRODUCT') === $product->getProductId()
        ) {
            throw new ResponseException(new Response($content));
        }

        $template->product = $content;
        $template->attributes = (new HtmlAttributes($product->getCssId()))->addClass($product->getCssClass());
        $template->referer = $this->generateContentUrl($this->requestStack->getMainRequest()->attributes->get('pageModel'));

        return $template->getResponse();
    }
}
