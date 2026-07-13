<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Action;

use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\PageModel;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsProductAction;
use Isotope\CoreBundle\HttpFoundation\ActionResult;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\Render\ViewInterface;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\Store\CartManager;
use Isotope\CoreBundle\Store\StoreManager;
use Isotope\CoreBundle\Twig\TemplateMarkupFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Exception\ExceptionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

#[AsProductAction]
class AddToCartAction extends AbstractProductAction
{
    public function __construct(
        private readonly string $type,
        private readonly StoreManager $storeManager,
        private readonly CartManager $cartManager,
        private readonly RequestStack $requestStack,
        private readonly ContentUrlGenerator $urlGenerator,
        private readonly UriSigner $uriSigner,
        private readonly TranslatorInterface $translator,
        private readonly TemplateMarkupFactory $markupFactory,
        Environment $twig,
    ) {
        parent::__construct($twig);
    }

    public function supports(CoreProduct $product, array $configuration, ViewInterface $view): bool
    {
        return true;
    }

    public function render(CoreProduct $product, array $configuration, ViewInterface $view): string
    {
        return $this->renderView($this->type, [
            'label' => $this->translator->trans('MSC.buttonLabel.add_to_cart', [], 'contao_default'),
            'disabled' => ($product->hasVariants() && !$product->isVariant()),
        ]);
    }

    public function handle(CoreProduct $product, array $configuration, ViewInterface $view): ActionResult
    {
        $quantity = 1;
        $request = $this->requestStack->getCurrentRequest();

        if ($view->useQuantity($product) && ($quantityRequested = (int) $request?->request->get('quantity_requested')) > 0) {
            $quantity = $quantityRequested;
        }

        // Do not add parent of variant product to the cart
        if ($product->hasVariants() && !$product->isVariant()) {
            return new ActionResult(success: false);
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

        if (
            ($page = $request?->attributes->get('originalPageModel')) instanceof PageModel
            || ($page = $request?->attributes->get('pageModel')) instanceof PageModel
        ) {
            $item->setReferencePage($page);
        }

        $this->cartManager->add($context->getCart(), $item, $context);

        return new ActionResult($this->markupFactory->create('@Contao/isotope/notification/added_to_cart.html.twig', [
            'product' => $product,
            'configuration' => $configuration,
            'context' => $context,
            'view' => $view,
            'cart_url' => $this->buildUrl((int) $view->get('iso_addProductJumpTo'), $request),
            'checkout_url' => $this->buildUrl((int) ($context->getMemberId() ? $view->get('iso_checkout_jumpTo') : ($view->get('iso_checkout_guest_jumpTo') ?? $view->get('iso_checkout_jumpTo')))),
        ]));
    }

    private function buildUrl(int $pageId, Request|null $request = null): string|null
    {
        if (!$pageId) {
            return null;
        }

        try {
            if ($request) {
                return $this->uriSigner->sign($this->urlGenerator->generate(
                    PageModel::findById($pageId),
                    ['continue' => base64_encode($request->getRequestUri())],
                    UrlGeneratorInterface::ABSOLUTE_URL,
                ));
            }

            return $this->urlGenerator->generate(
                PageModel::findById($pageId),
                [],
                UrlGeneratorInterface::ABSOLUTE_URL,
            );
        } catch (ExceptionInterface) {
            return null;
        }
    }
}
