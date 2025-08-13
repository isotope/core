<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\FrontendModule;

use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\ModuleModel;
use Isotope\Model\Product;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule('iso_categoryfilter', category: 'isotope')]
class CategoryFilterController extends AbstractFrontendModuleController
{
    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        if ($request->attributes->get('_content') instanceof Product && $model->iso_hide_list) {
            return new Response('');
        }

        // Set the trail and level
        if ($model->defineRoot && $model->rootPage > 0) {
            $trail = [$model->rootPage];
            $level = 0;
        } else {
            $trail = $this->getPageModel()->trail;
            $level = ($model->levelOffset > 0) ? $model->levelOffset : 0;
        }

        $template->menuOptions = [
            'filterId' => 'fmd'.$model->id,
            'rootPage' => $trail[$level] ?? 0,
            'showLevel' => $model->showLevel,
            'showHidden' => $model->showHidden,
            'showProtected' => $model->showProtected,
        ];

        return $template->getResponse();
    }
}
