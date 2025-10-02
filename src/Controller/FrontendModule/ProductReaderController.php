<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\FrontendModule;

use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\ModuleModel;
use Isotope\CoreBundle\Product\ProductInterface;
use Isotope\CoreBundle\Product\Render\ReaderView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule('iso_productreader', category: 'isotope')]
class ProductReaderController extends AbstractFrontendModuleController
{
    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        $product = $request->attributes->get('_content');

        if (!$product instanceof ProductInterface) {
            if ($model->iso_display404Page) {
                throw new PageNotFoundException();
            }

            return new Response();
        }

        $template->product = $product;
        $template->view = ReaderView::createFromModule($model);

        return $template->getResponse();
    }
}
