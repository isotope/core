<?php

namespace Isotope\CoreBundle\Product\Action;

use Contao\Input;
use Isotope\CoreBundle\HttpFoundation\ActionResult;
use Isotope\CoreBundle\Product\Render\RenderConfig;
use Isotope\Haste\Util\Url;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Isotope;
use Isotope\Model\ProductCollectionItem;
use Twig\Environment;

class AddToCartAction implements ProductActionInterface
{
    public function __construct(private readonly Environment $twig)
    {
    }

    public function supports(IsotopeProduct $product, RenderConfig $renderConfig): bool
    {
        return true;
    }

    public function render(IsotopeProduct $product, RenderConfig $renderConfig): string
    {
        return $this->twig->render('@Contao/isotope/product_action/add_to_cart.html.twig', [
            'disabled' => $product->hasVariants() && !$product->isVariant(),
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

    public function handle(IsotopeProduct $product, RenderConfig $renderConfig): ActionResult
    {
        $item = $this->getCurrentCartItem($product);

        if ($item) {
            $response = $this->handleUpdateCart($item, $product);
        } else {
            $response = $this->handleAddToCart($product, $renderConfig);
        }

        if ($response->success && ($renderConfig->options['iso_addProductJumpTo'] ?? null)) {
            return new ActionResult(
                null,
                Url::addQueryString(
                    'continue=' . base64_encode(\Contao\Environment::get('request')),
                    $renderConfig->options['iso_addProductJumpTo'],
                )
            );
        }

        return $response;
    }

    /**
     * @param IsotopeProduct|null $product
     *
     * @return ProductCollectionItem|null
     */
    private function getCurrentCartItem(IsotopeProduct $product = null)
    {
        if (null === $product || !Input::get('collection_item')) {
            return null;
        }

        /** @var ProductCollectionItem $item */
        $item = Isotope::getCart()->getItemById(Input::get('collection_item'));

        if (null !== $item
            && $item->hasProduct()
            && $item->getProduct()->getProductId() == $product->getProductId()
        ) {
            return $item;
        }

        return null;
    }

    private function handleAddToCart(IsotopeProduct $product, RenderConfig $config): ActionResult
    {
        $quantity = 1;

        if ($config->withQuantity && Input::post('quantity_requested') > 0) {
            $quantity = (int) Input::post('quantity_requested');
        }

        // Do not add parent of variant product to the cart
        if (($product->hasVariants() && !$product->isVariant())
            || !Isotope::getCart()->addProduct($product, $quantity, $config->toArray())
        ) {
            return new ActionResult('Das Produkt konnte nicht in den Warenkorb gelegt werden', success: false);
        }

        return new ActionResult($GLOBALS['TL_LANG']['MSC']['addedToCart']);
    }

    private function handleUpdateCart(ProductCollectionItem $item, IsotopeProduct $product): ActionResult
    {
        $success = Isotope::getCart()->updateProduct($product, $item);

        if ($success) {
            return new ActionResult($GLOBALS['TL_LANG']['MSC']['updatedInCart']);
        }

        return new ActionResult('Das Produkt konnte nicht aktualisiert werden.', success: false);
    }
}
