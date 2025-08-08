<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\FrontendModule;

use Contao\Controller;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\Input;
use Contao\ModuleModel;
use Isotope\CoreBundle\ProductCollection\Action\ContinueShoppingAction;
use Isotope\CoreBundle\ProductCollection\Action\GoToCartAction;
use Isotope\CoreBundle\ProductCollection\Action\GoToCheckoutAction;
use Isotope\CoreBundle\ProductCollection\Action\ProductCollectionActionInterface;
use Isotope\CoreBundle\ProductCollection\Action\UpdateCartAction;
use Isotope\Haste\Util\Url;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Isotope\Model\ProductCollection\Cart;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule('iso_cart')]
class CartController extends AbstractFrontendModuleController
{
    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        $collection = Isotope::getCart();

        $template->collection = $collection;
        $template->remove = $this->getRemoveActions($collection);

        return $template->getResponse();
    }

    private function getRemoveActions(Cart $cart): array
    {
        $actions = [];

        foreach ($cart->getItems() as $item) {
            if ((int) Input::get('remove') === (int) $item->id) {
                $cart->deleteItemById($item->id);
                Controller::redirect(Url::removeQueryString(['remove']));
            }

            $actions[$item->id]  = Url::addQueryString('remove=' . $item->id);
        }

        return $actions;
    }
}
