<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Twig\Runtime;

use Isotope\CoreBundle\Exception\ActionException;
use Isotope\CoreBundle\Product\Action\ProductActionRegistry;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\Render\ProductConfiguration;
use Isotope\CoreBundle\Product\Render\ViewInterface;
use Isotope\CoreBundle\ProductCollection\AbstractProductCollection;
use Isotope\CoreBundle\ProductCollection\Action\ProductCollectionActionRegistry;
use Isotope\CoreBundle\ProductCollection\ItemAction\ProductCollectionItemActionRegistry;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\Util\MessagesUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\RuntimeExtensionInterface;

class ActionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly ProductActionRegistry $productActionRegistry,
        private readonly ProductCollectionActionRegistry $collectionActionRegistry,
        private readonly ProductCollectionItemActionRegistry $itemActionRegistry,
        private readonly RequestStack $requestStack,
        private readonly MessagesUtil $messages,
    ) {
    }

    public function renderProductAction(string $type, CoreProduct $product, ViewInterface $view, ProductConfiguration|null $configuration = null): string
    {
        $request = $this->requestStack->getCurrentRequest();
        $action = $this->productActionRegistry->get($type);

        if (null === $action) {
            throw ActionException::unknownProductAction($type);
        }

        if (!$request instanceof Request) {
            throw ActionException::missingRequest();
        }

        $configData = $configuration instanceof ProductConfiguration ? $configuration->getData() : [];

        if (!$action->supports($product, $configData, $view)) {
            return '';
        }

        if ($request->request->get('FORM_SUBMIT') === $view->getFormId($product) && $request->request->get('_action') === $type) {
            $result = $action->handle($product, $configData, $view);

            $this->messages->handleActionResult($type, $result);
        }

        return $action->render($product, $configData, $view);
    }

    public function renderProductCollectionAction(array $context, string $type, array $options = []): string
    {
        $collection = $context['collection'] ?? null;
        $fragmentId = $context['fragment_id'] ?? null;
        $request = $context['app']->getRequest();
        $action = $this->collectionActionRegistry->get($type);

        if (null === $action) {
            throw ActionException::unknownCollectionAction($type);
        }

        if (
            !$collection instanceof AbstractProductCollection
            || !$fragmentId
            || !$request instanceof Request
            || !$action->supports($collection, $context, $options)
        ) {
            return '';
        }

        if (
            $request->request->get('FORM_SUBMIT') === $fragmentId
            && $request->request->get('_action') === $type
        ) {
            $result = $action->handle($collection, $context, $options);

            $this->messages->handleActionResult($type, $result);
        }

        return $action->render($collection, $context, $options);
    }

    public function renderProductCollectionItemAction(array $context, string $type): string
    {
        $collection = $context['collection'] ?? null;
        $fragmentId = $context['fragment_id'] ?? null;
        $item = $context['item'] ?? null;
        $request = $context['app']->getRequest();
        $action = $this->itemActionRegistry->get($type);

        if (null === $action) {
            throw ActionException::unknownItemAction($type);
        }

        if (
            !$collection instanceof AbstractProductCollection
            || !$item instanceof LineItem
            || !$fragmentId
            || !$request instanceof Request
            || !$collection->hasItem($item)
            || !$action->supports($collection, $item, $context)
        ) {
            return '';
        }

        if (
            $request->isMethod(Request::METHOD_POST)
            && $request->request->get('FORM_SUBMIT') === $fragmentId
            && $request->request->has($type)
            && $request->request->getInt($type) === $item->getId()
        ) {
            $result = $action->handle($collection, $item, $context);

            $this->messages->handleActionResult($type, $result);
        }

        return $action->render($collection, $item, $context);
    }
}
