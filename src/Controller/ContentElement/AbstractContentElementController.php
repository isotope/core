<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController as CoreContentElementController;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Isotope\CoreBundle\Product\Query\ProductQuery;
use Isotope\CoreBundle\Product\Query\ProductQueryFactory;
use Isotope\CoreBundle\ProductCollection\AbstractProductCollection;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Price\PriceDisplay;
use Isotope\CoreBundle\Store\Price\TaxCalculation;
use Isotope\CoreBundle\Store\StoreManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @experimental
 */
abstract class AbstractContentElementController extends CoreContentElementController
{
    #[\Override]
    public function __invoke(Request $request, ContentModel $model, string $section, array|null $classes = null): Response
    {
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

    #[\Override]
    public static function getSubscribedServices(): array
    {
        $services = parent::getSubscribedServices();

        $services[StoreManager::class] = StoreManager::class;
        $services[ProductQueryFactory::class] = ProductQueryFactory::class;

        return $services;
    }

    #[\Override]
    protected function addDefaultDataToTemplate(FragmentTemplate $template, array $modelData = [], string $section = 'main', array $classes = [], array $properties = [], bool $asEditorView = false, array $nestedFragments = []): void
    {
        parent::addDefaultDataToTemplate($template, $modelData, $section, $classes, $properties, $asEditorView);

        $template->fragment_id = $this->getFragmentId($modelData);
    }

    protected function getProductQuery(Request $request): ProductQuery|null
    {
        /** @var ProductQueryFactory $factory */
        $factory = $this->container->get(ProductQueryFactory::class);

        return $factory->getCurrent($request);
    }

    protected function getFragmentId(array $modelData): string
    {
        return 'cte'.($modelData['id'] ?? '-'.hash('xxh3', serialize($modelData)));
    }

    protected function getStoreContext(): StoreContext
    {
        /** @var StoreManager $storeManager */
        $storeManager = $this->container->get(StoreManager::class);

        return $storeManager->getCurrentContext();
    }

    protected function addCollectionToTemplate(FragmentTemplate $template, AbstractProductCollection $collection, StoreContext $context): void
    {
        $template->collection = $collection;
        $template->show_taxes = TaxCalculation::Horizontal === $context->getTaxCalculation();
        $template->multiple_taxes = $collection->getTotal()?->getCalculatedTaxes()->count() > 1;
        $template->is_gross = PriceDisplay::Net !== $context->getPriceDisplay();
    }
}
