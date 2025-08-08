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
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Message;
use Isotope\Model\Product;
use Symfony\Component\HttpFoundation\Request;
use Twig\Extension\RuntimeExtensionInterface;

class ActionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly ProductActionRegistry $productActionRegistry, private readonly ProductCollectionActionRegistry $productCollectionActionRegistry
    ) {
    }

    public function renderProductAction(array $context, string $name): string
    {
        $product = $context['product'] ?? null;
        $renderConfig = $context['config'] ?? null;
        $request = $context['app']->getRequest();
        $action = $this->productActionRegistry->get($name);

        if (null === $action) {
            throw new \RuntimeException('Unkonwn product action "'.$name.'"');
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
        $action = $this->productCollectionActionRegistry->get($name);

        if (null === $action) {
            throw new \RuntimeException('Unkonwn product collection action "'.$name.'"');
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

    private function handleActionResult(Request $request, ActionResult $result): never
    {
        if ($request->isXmlHttpRequest()) {
            throw new ResponseException($result->toResponse());
        }

        if ($result->redirect) {
            Controller::redirect($result->redirect);
        }

        if ($result->message) {
            Message::addConfirmation($result->message);
        }

        Controller::reload();
    }
}
