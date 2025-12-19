<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Action;

use Contao\Input;
use Isotope\CoreBundle\HttpFoundation\ActionResult;
use Isotope\CoreBundle\Model\ProductCollectionItem;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\Render\ViewInterface;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\Store\CartManager;
use Isotope\CoreBundle\Store\StoreManager;
use Isotope\Haste\Util\Url;
use Isotope\Isotope;
use Twig\Environment;

class AddToCartAction implements ProductActionInterface
{
    public function __construct(
        private readonly StoreManager $storeManager,
        private readonly CartManager $cartManager,
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

    //public function getLabel(IsotopeProduct $product = null): string
    //{
    //    if ($this->getCurrentCartItem($product)) {
    //        return $GLOBALS['TL_LANG']['MSC']['buttonLabel']['update_cart'];
    //    }
    //
    //    return $GLOBALS['TL_LANG']['MSC']['buttonLabel']['add_to_cart'];
    //}

    public function handle(CoreProduct $product, array $configuration, ViewInterface $view): ActionResult
    {
        $item = $this->getCurrentCartItem($product);

        if ($item) {
            $response = $this->handleUpdateCart($item, $product);
        } else {
            $response = $this->handleAddToCart($product, $configuration, $view);
        }

        if ($response->success && $view->get('iso_addProductJumpTo')) {
            return new ActionResult(
                null,
                Url::addQueryString(
                    'continue='.base64_encode(\Contao\Environment::get('request')),
                    $view->get('iso_addProductJumpTo'),
                ),
            );
        }

        return $response;
    }

    private function getCurrentCartItem(CoreProduct $product): ProductCollectionItem|null
    {
        if (!Input::get('collection_item')) {
            return null;
        }

        /** @var ProductCollectionItem $item */
        $item = Isotope::getCart()->getItemById(Input::get('collection_item'));

        return $item?->isProduct($product) ? $item : null;
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

    private function handleUpdateCart(ProductCollectionItem $item, CoreProduct $product): ActionResult
    {
        $success = Isotope::getCart()->updateProduct($product, $item);

        if ($success) {
            return new ActionResult($GLOBALS['TL_LANG']['MSC']['updatedInCart']);
        }

        return new ActionResult('Das Produkt konnte nicht aktualisiert werden.', success: false);
    }
}
