<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\FrontendModule;

use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\ModuleModel;
use Contao\StringUtil;
use Isotope\Model\Product;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule('iso_relatedproducts', category: 'isotope')]
class RelatedProductsController extends AbstractFrontendModuleController
{
    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        $categories = StringUtil::deserialize($model->iso_related_categories, true);
        $product = $request->attributes->get('_content');

        if (!$product instanceof Product || [] === $categories) {
            return new Response();
        }



        return $template->getResponse();
    }
}
