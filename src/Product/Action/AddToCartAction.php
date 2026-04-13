<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Action;

use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\Input;
use Contao\PageModel;
use Isotope\CoreBundle\HttpFoundation\ActionResult;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\Render\ViewInterface;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\Store\CartManager;
use Isotope\CoreBundle\Store\StoreManager;
use Isotope\Haste\Util\Url;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Exception\ExceptionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class AddToCartAction implements ProductActionInterface
{
    public function __construct(
        private readonly StoreManager $storeManager,
        private readonly CartManager $cartManager,
        private readonly RequestStack $requestStack,
        private readonly ContentUrlGenerator $urlGenerator,
        private readonly UriSigner $uriSigner,
        private readonly Environment $twig,
    ) {
    }

    public function supports(CoreProduct $product, ViewInterface $view): bool
    {
        return true;
    }

    public function render(CoreProduct $product, ViewInterface $view): string
    {
        return $this->twig->render('@Contao/isotope/product_action/add_to_cart.html.twig', [
            'disabled' => ($product->hasVariants() && !$product->isVariant()),
        ]);
    }

    public function handle(CoreProduct $product, array $configuration, ViewInterface $view): ActionResult
    {
        $request = $this->requestStack->getCurrentRequest();
        $response = $this->handleAddToCart($product, $configuration, $view);

        if ($request && $response->success && $view->get('iso_addProductJumpTo')) {
            try {
                return new ActionResult(
                    null,
                    $this->uriSigner->sign($this->urlGenerator->generate(
                        PageModel::findById($view->get('iso_addProductJumpTo')),
                        ['continue' => base64_encode($request->getRequestUri())],
                        UrlGeneratorInterface::ABSOLUTE_URL,
                    )),
                );
            } catch (ExceptionInterface) {
                // ignore routing exception
            }
        }

        return $response;
    }

    private function handleAddToCart(CoreProduct $product, array $configuration, ViewInterface $view): ActionResult
    {
        $quantity = 1;

        if ($view->useQuantity($product) && Input::post('quantity_requested') > 0) {
            $quantity = (int) Input::post('quantity_requested');
        }

        // Do not add parent of variant product to the cart
        if (($product->hasVariants() && !$product->isVariant())
            //|| !Isotope::getCart()->addProduct($product, $quantity, $configuration, $view->toArray($product))
        ) {
            return new ActionResult('Das Produkt konnte nicht in den Warenkorb gelegt werden', success: false);
        }

        $context = $this->storeManager->getCurrentContext();

        $item = new LineItem(
            LineItem::TYPE_PRODUCT,
            $product->getIdentifier(),
            $quantity,
        );

        $item->setConfiguration($configuration);
        $item->setProduct($product);
        $item->setSku($product->getSku());
        $item->setLabel($product->getName());

        $this->cartManager->add($context->getCart(), $item, $context);

        return new ActionResult($GLOBALS['TL_LANG']['MSC']['addedToCart']);
    }
}
