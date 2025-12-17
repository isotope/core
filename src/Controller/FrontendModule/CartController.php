<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\FrontendModule;

use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\ModuleModel;
use Isotope\CoreBundle\Store\CartManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule('iso_cart')]
class CartController extends AbstractFrontendModuleController
{
    public function __construct(
        private readonly CartManager $cartManager,
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        $context = $this->getStoreContext();
        $collection = $context->getCart();

        $template->collection = $collection;

        if (
            $request->isMethod(Request::METHOD_POST)
            && $request->request->get('FORM_SUBMIT') === $this->getFragmentId($model->row())
            && !empty($quantity = $request->request->all('quantity'))
        ) {
            foreach ($collection->getLineItems() as $item) {
                if (isset($quantity[$item->getId()])) {
                    $this->cartManager->changeQuantity($collection, $item->getId(), (int) $quantity[$item->getId()], $context);
                }
            }
        }

        return $template->getResponse();
    }
}
