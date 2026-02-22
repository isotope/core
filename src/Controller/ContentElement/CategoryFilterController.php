<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Isotope\CoreBundle\Product\CoreProduct;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement('iso_categoryfilter', category: 'isotope')]
class CategoryFilterController extends AbstractContentElementController
{
    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        if ($request->attributes->get('_content') instanceof CoreProduct && $model->iso_hide_list) {
            return new Response('');
        }

        // Set the trail and level
        if ($model->defineRoot && $model->rootPage > 0) {
            $trail = [$model->rootPage];
            $level = 0;
        } else {
            $trail = $this->getPageModel()->trail;
            $level = $model->levelOffset > 0 ? $model->levelOffset : 0;
        }

        $template->filter_key = $model->iso_filterKey ?: '';
        $template->menuOptions = [
            'filterKey' => $template->filter_key,
            'rootPage' => $trail[$level] ?? 0,
            'showLevel' => $model->showLevel,
            'showHidden' => $model->showHidden,
            'showProtected' => $model->showProtected,
        ];

        return $template->getResponse();
    }
}
