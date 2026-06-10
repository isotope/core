<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Isotope\CoreBundle\Store\CartManager;
use Isotope\CoreBundle\Store\Price\PriceDisplay;
use Isotope\CoreBundle\Store\Price\TaxCalculation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement('iso_cart', category: 'isotope')]
class CartController extends AbstractContentElementController
{
    public function __construct(
        private readonly CartManager $cartManager,
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $context = $this->getStoreContext();
        $collection = $context->getCart();

        $this->addCollectionToTemplate($template, $collection, $context);

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
