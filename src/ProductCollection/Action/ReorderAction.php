<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Action;

use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\PageModel;
use Isotope\CoreBundle\HttpFoundation\ActionResult;
use Isotope\CoreBundle\ProductCollection\AbstractProductCollection;
use Isotope\CoreBundle\Store\CartManager;
use Isotope\CoreBundle\Store\StoreManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Exception\ExceptionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class ReorderAction implements ProductCollectionActionInterface
{
    public function __construct(
        private readonly StoreManager $storeManager,
        private readonly CartManager $cartManager,
        private readonly Environment $twig,
        private readonly RequestStack $requestStack,
        private readonly ContentUrlGenerator $urlGenerator,
        private readonly UriSigner $uriSigner,
    ) {
    }

    public function supports(AbstractProductCollection $collection, array $context): bool
    {
        return true;
    }

    public function render(AbstractProductCollection $collection, array $context): string
    {
        return $this->twig->render('@Contao/isotope/product_collection_action/reorder.html.twig');
    }

    public function handle(AbstractProductCollection $collection, array $context): ActionResult
    {
        $storeContext = $this->storeManager->getCurrentContext();
        $cart = $storeContext->getCart();

        $this->cartManager->add(
            $cart,
            $collection->getLineItems()->products()->clone()->toArray(),
            $storeContext,
        );

        $redirect = null;

        if (($context['data']['iso_cart_jumpTo'] ?? null) > 0 && ($request = $this->requestStack->getCurrentRequest())) {
            try {
                $redirect = $this->uriSigner->sign($this->urlGenerator->generate(
                    PageModel::findById($context['data']['iso_cart_jumpTo']),
                    ['continue' => base64_encode($request->getRequestUri())],
                    UrlGeneratorInterface::ABSOLUTE_URL,
                ));
            } catch (ExceptionInterface) {
                // ignore redirect URL
            }
        }

        return new ActionResult(
            $GLOBALS['TL_LANG']['MSC']['reorderConfirmation'],
            $redirect,
        );
    }
}
