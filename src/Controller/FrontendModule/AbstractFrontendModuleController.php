<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController as CoreFrontendModuleController;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\Model;
use Contao\ModuleModel;
use Isotope\CoreBundle\Product\Query\ProductQuery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * TODO: replace this with an abstract that can be an element or a module
 *
 * @internal
 */
abstract class AbstractFrontendModuleController extends CoreFrontendModuleController
{
    public function __invoke(Request $request, ModuleModel $model, string $section, ?array $classes = null): Response
    {
        if ($this->isBackendScope($request)) {
            return $this->getBackendWildcard($model);
        }

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

    protected function addDefaultDataToTemplate(FragmentTemplate $template, array $modelData = [], string $section = 'main', array $classes = [], array $properties = [], bool $asEditorView = false): void
    {
        parent::addDefaultDataToTemplate($template, $modelData, $section, $classes, $properties, $asEditorView);

        $template->fragment_id = $this->getFragmentId($modelData);
    }

    protected function getProductQuery(Request $request): ProductQuery|null
    {
        $productQuery = $request->attributes->get('_product_query');

        return $productQuery instanceof ProductQuery ? $productQuery : null;
    }

    private function getFragmentId(array $modelData): string
    {
        return 'fmd'.($modelData['id'] ?? null ?: substr(md5(serialize($modelData)), 0, 8));
    }
}
