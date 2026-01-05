<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController as CoreContentElementController;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Isotope\CoreBundle\Product\Query\ProductQuery;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\StoreManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
abstract class AbstractContentElementController extends CoreContentElementController
{
    public function __invoke(Request $request, ContentModel $model, string $section, ?array $classes = null): Response
    {
        //if ($this->isBackendScope($request)) {
        //    return $this->getBackendWildcard($model);
        //}

        $fragmentId = $this->getFragmentId($model->row());
        $isFragmentRequest = $request->isXmlHttpRequest() && $request->headers->has('Isotope-Fragment');
        $isCurrentFragment = $request->headers->get('Isotope-Fragment') === $fragmentId;

        if ($isFragmentRequest && !$isCurrentFragment) {
            return new Response();
        }

        $response = parent::__invoke($request, $model, $section, $classes);

        if ($isFragmentRequest && $isCurrentFragment) {
            $response->setVary('Isotope-Fragment', false);

            throw new ResponseException($response);
        }

        return $response;
    }

    protected function addDefaultDataToTemplate(FragmentTemplate $template, array $modelData = [], string $section = 'main', array $classes = [], array $properties = [], bool $asEditorView = false, array $nestedFragments = []): void
    {
        parent::addDefaultDataToTemplate($template, $modelData, $section, $classes, $properties, $asEditorView);

        $template->fragment_id = $this->getFragmentId($modelData);
    }

    protected function getProductQuery(Request $request): ProductQuery|null
    {
        $productQuery = $request->attributes->get('_product_query');

        return $productQuery instanceof ProductQuery ? $productQuery : null;
    }

    protected function getFragmentId(array $modelData): string
    {
        return 'fmd'.($modelData['id'] ?? null ?: '-'.substr(md5(serialize($modelData)), 0, 8));
    }

    protected function getStoreContext(): StoreContext
    {
        /** @var StoreManager $storeManager */
        $storeManager = $this->container->get(StoreManager::class);

        return $storeManager->getCurrentContext();
    }

    public static function getSubscribedServices(): array
    {
        $services = parent::getSubscribedServices();

        $services[StoreManager::class] = StoreManager::class;

        return $services;
    }
}
