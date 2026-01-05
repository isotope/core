<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\Render\ReaderView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement('iso_productreader', category: 'isotope')]
class ProductReaderController extends AbstractContentElementController
{
    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $product = $request->attributes->get('_content');

        if (!$product instanceof CoreProduct) {
            if ($model->iso_display404Page) {
                throw new PageNotFoundException();
            }

            return new Response();
        }

        $template->product = $product;
        $template->view = ReaderView::createFromContent($model);

        return $template->getResponse();
    }
}
