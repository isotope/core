<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Twig;

use Contao\Controller;
use Contao\CoreBundle\Exception\ResponseException;
use Isotope\CoreBundle\HttpFoundation\ActionResult;
use Isotope\CoreBundle\Product\Action\ProductActionInterface;
use Isotope\CoreBundle\Product\Action\ProductActionRegistry;
use Isotope\CoreBundle\Product\Render\RenderConfig;
use Isotope\CoreBundle\ProductCollection\Action\ProductCollectionActionInterface;
use Isotope\CoreBundle\ProductCollection\Action\ProductCollectionActionRegistry;
use Isotope\CoreBundle\ProductCollection\ItemAction\ProductCollectionItemActionRegistry;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Message;
use Isotope\Model\Product;
use Isotope\Model\ProductCollectionItem;
use Symfony\Component\HttpFoundation\Request;
use Twig\Extension\RuntimeExtensionInterface;

class ActionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly ProductActionRegistry $productActionRegistry,
        private readonly ProductCollectionActionRegistry $collectionActionRegistry,
        private readonly ProductCollectionItemActionRegistry $itemActionRegistry,
    ) {
    }

    public function renderProductAction(array $context, string $name): string
    {
        $product = $context['product'] ?? null;
        $renderConfig = $context['config'] ?? null;
        $request = $context['app']->getRequest();
        $action = $this->productActionRegistry->get($name);

        if (null === $action) {
            throw new \RuntimeException('Unknown product action "'.$name.'"');
        }

        if (
            !$product instanceof Product
            || !$renderConfig instanceof RenderConfig
            || !$request instanceof Request
            || !$action->supports($product, $renderConfig)
        ) {
            return '';
        }

        if ($request->request->get('FORM_SUBMIT') === $renderConfig->id /*&& !$this->doNotSubmit*/ && $request->request->get('_action') === $name) {
            $result = $action->handle($product, $renderConfig);

            $this->handleActionResult($request, $result);
        }

        return $action->render($product, $renderConfig);
    }

    public function renderProductCollectionAction(array $context, string $name): string
    {
        $collection = $context['collection'] ?? null;
        $fragmentId = $context['fragment_id'] ?? null;
        $request = $context['app']->getRequest();
        $action = $this->collectionActionRegistry->get($name);

        if (null === $action) {
            throw new \RuntimeException('Unknown product collection action "'.$name.'"');
        }

        if (
            !$collection instanceof IsotopeProductCollection
            || !$fragmentId
            || !$request instanceof Request
            || !$action->supports($collection, $context)
        ) {
            return '';
        }

        if ($request->request->get('FORM_SUBMIT') === $fragmentId /*&& !$this->doNotSubmit*/ && $request->request->get('_action') === $name) {
            $result = $action->handle($collection, $context);

            $this->handleActionResult($request, $result);
        }

        return $action->render($collection, $context);
    }

    public function renderProductCollectionItemAction(array $context, string $name): string
    {
        $item = $context['item'] ?? null;
        $request = $context['app']->getRequest();
        $action = $this->itemActionRegistry->get($name);

        if (null === $action) {
            throw new \RuntimeException('Unknown product collection item action "'.$name.'"');
        }

        if (
            !$item instanceof ProductCollectionItem
            || !$request instanceof Request
            || !$action->supports($item, $context)
        ) {
            return '';
        }

        if ($request->query->getString('_action') === $name && $request->query->getInt('_item') === (int) $item->id) {
            $result = $action->handle($item, $context);

            $this->handleActionResult($request, $result);
        }

        return $action->render($item, $context);
    }

    private function handleActionResult(Request $request, ActionResult $result): never
    {
        if ($request->isXmlHttpRequest()) {
            throw new ResponseException($result->toResponse());
        }

        if ($result->message) {
            Message::addConfirmation($result->message);
        }

        if ($result->redirect) {
            Controller::redirect($result->redirect);
        }

        Controller::reload();
    }
}
