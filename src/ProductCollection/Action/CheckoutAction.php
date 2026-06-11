<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Action;

use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\PageModel;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsProductCollectionAction;
use Isotope\CoreBundle\HttpFoundation\ActionResult;
use Isotope\CoreBundle\ProductCollection\AbstractProductCollection;
use Isotope\CoreBundle\Store\StoreManager;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

#[AsProductCollectionAction]
class CheckoutAction extends AbstractProductCollectionAction
{
    public function __construct(
        private readonly string $type,
        private readonly ContentUrlGenerator $urlGenerator,
        private readonly StoreManager $storeManager,
        private readonly TranslatorInterface $translator,
        Environment $twig,
    ) {
        parent::__construct($twig);
    }

    public function supports(AbstractProductCollection $collection, array $context, array $options = []): bool
    {
        return !$collection->hasErrors() && null !== $this->getPageModel($context);
    }

    public function render(AbstractProductCollection $collection, array $context, array $options = []): string
    {
        return $this->renderView($this->type, [
            'link' => (bool) ($options['link'] ?? false),
            'label' => $options['label'] ?? $this->translator->trans('MSC.checkoutBT', [], 'contao_default'),
            'href' => $this->urlGenerator->generate($this->getPageModel($context)),
            ...$options,
        ]);
    }

    public function handle(AbstractProductCollection $collection, array $context, array $options = []): ActionResult
    {
        return new ActionResult(null, $this->urlGenerator->generate($this->getPageModel($context)));
    }

    private function getPageModel(array $context): PageModel|null
    {
        $pageId = $context['data']['iso_checkout_jumpTo'] ?? null;

        if (
            !$this->storeManager->getCurrentContext()->getMemberId()
            && !empty($context['data']['iso_checkout_guest_jumpTo'])
        ) {
            $pageId = $context['data']['iso_checkout_guest_jumpTo'];
        }

        return $pageId ? PageModel::findById($pageId) : null;
    }
}
