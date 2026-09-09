<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\PageModel;
use Isotope\CoreBundle\Model\ProductModel;
use Isotope\CoreBundle\Product\CoreProduct;
use Symfony\Component\HttpFoundation\RequestStack;
use Terminal42\ChangeLanguage\Event\ChangelanguageNavigationEvent;
use Terminal42\ChangeLanguage\PageFinder;

#[AsHook('changelanguageNavigation')]
class ChangelanguageNavigationListener
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly PageFinder|null $pageFinder = null,
    ) {
    }

    public function __invoke(ChangelanguageNavigationEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $product = $request?->attributes->get('_content');
        $currentPage = $request?->attributes->get('originalPageModel');

        if (
            !$this->pageFinder
            || !$product instanceof CoreProduct
            || !$currentPage instanceof PageModel
            || !($model = ProductModel::findById($product->getProductId()))
        ) {
            return;
        }

        $navigationItem = $event->getNavigationItem();
        $alias = $model->getAlias($navigationItem->getRootPage()->language);

        $event->getUrlParameterBag()->setUrlAttribute('auto_item', $alias ?: $model->id);
        $navigationItem->setTitle($product->getName());
        $navigationItem->setPageTitle($product->meta_title);

        if (!$navigationItem->isCurrentPage()) {
            $targetPage = $this->pageFinder->findAssociatedForLanguage($currentPage, $navigationItem->getLocaleId());
            $navigationItem->setTargetPage($targetPage, true);
        }
    }
}
