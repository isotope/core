<?php

namespace Isotope\CoreBundle\Product\Action;

use Contao\Input;
use Isotope\CoreBundle\HttpFoundation\ActionResult;
use Isotope\CoreBundle\Product\ProductInterface;
use Isotope\CoreBundle\Product\Render\RenderConfig;
use Isotope\CoreBundle\Product\VariantProductInterface;
use Isotope\Haste\Util\Url;
use Isotope\Isotope;
use Isotope\Model\ProductCollectionItem;
use Twig\Environment;

class AddToCartAction implements ProductActionInterface
{
    public function __construct(private readonly Environment $twig)
    {
    }

    public function supports(ProductInterface $product, RenderConfig $renderConfig): bool
    {
        return true;
    }

    public function render(ProductInterface $product, RenderConfig $renderConfig): string
    {
        return $this->twig->render('@Contao/isotope/product_action/add_to_cart.html.twig', [
            'disabled' => !$product instanceof VariantProductInterface || ($product->hasVariants() && !$product->isVariant()),
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

    public function handle(ProductInterface $product, array $configuration, RenderConfig $renderConfig): ActionResult
    {
        $item = $this->getCurrentCartItem($product);

        if ($item) {
            $response = $this->handleUpdateCart($item, $product);
        } else {
            $response = $this->handleAddToCart($product, $configuration, $renderConfig);
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

    private function getCurrentCartItem(ProductInterface $product): ProductCollectionItem|null
    {
        if (!Input::get('collection_item')) {
            return null;
        }

        /** @var ProductCollectionItem $item */
        $item = Isotope::getCart()->getItemById(Input::get('collection_item'));

        return $item?->isProduct($product) ? $item : null;
    }

    private function handleAddToCart(ProductInterface $product, array $configuration, RenderConfig $config): ActionResult
    {
        $quantity = 1;

        if ($config->withQuantity && Input::post('quantity_requested') > 0) {
            $quantity = (int) Input::post('quantity_requested');
        }

        // Do not add parent of variant product to the cart
        if (($product->hasVariants() && !$product->isVariant())
            || !Isotope::getCart()->addProduct($product, $quantity, $configuration, $config->toArray())
        ) {
            return new ActionResult('Das Produkt konnte nicht in den Warenkorb gelegt werden', success: false);
        }

        return new ActionResult($GLOBALS['TL_LANG']['MSC']['addedToCart']);
    }

    private function handleUpdateCart(ProductCollectionItem $item, ProductInterface $product): ActionResult
    {
        $success = Isotope::getCart()->updateProduct($product, $item);

        if ($success) {
            return new ActionResult($GLOBALS['TL_LANG']['MSC']['updatedInCart']);
        }

        return new ActionResult('Das Produkt konnte nicht aktualisiert werden.', success: false);
    }
}
