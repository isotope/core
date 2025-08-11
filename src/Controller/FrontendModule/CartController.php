<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\FrontendModule;

use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\ModuleModel;
use Isotope\Isotope;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule('iso_cart')]
class CartController extends AbstractFrontendModuleController
{
    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        $collection = Isotope::getCart();

        $template->collection = $collection;

        if (
            $request->isMethod(Request::METHOD_POST)
            && $request->request->get('FORM_SUBMIT') === $this->getFragmentId($model->row())
            && !empty($quantity = $request->request->all('quantity'))
        ) {
            foreach ($collection->getItems() as $item) {
                if (isset($quantity[$item->id])) {
                    $collection->updateItem($item, array('quantity' => $quantity[$item->id]));
                }
            }
        }

        return $template->getResponse();
    }
}
