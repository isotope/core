<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Action;

use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\PageModel;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsProductCollectionAction;
use Isotope\CoreBundle\HttpFoundation\ActionResult;
use Isotope\CoreBundle\ProductCollection\AbstractProductCollection;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

#[AsProductCollectionAction]
class GoToCartAction extends AbstractProductCollectionAction
{
    public function __construct(
        private readonly string $type,
        private readonly ContentUrlGenerator $urlGenerator,
        private readonly TranslatorInterface $translator,
        Environment $twig,
    ) {
        parent::__construct($twig);
    }

    public function supports(AbstractProductCollection $collection, array $context, array $options = []): bool
    {
        return null !== $this->getPageModel($context);
    }

    public function render(AbstractProductCollection $collection, array $context, array $options = []): string
    {
        return $this->renderView($this->type, [
            'label' => $this->translator->trans('MSC.cartBT', [], 'contao_default'),
            'href' => $this->urlGenerator->generate($this->getPageModel($context)),
            ...$options,
        ]);
    }

    public function handle(AbstractProductCollection $collection, array $context, array $options = []): ActionResult
    {
        return new ActionResult(
            null,
            $this->urlGenerator->generate($this->getPageModel($context)),
        );
    }

    private function getPageModel(array $context): PageModel|null
    {
        if (!isset($context['data']['iso_cart_jumpTo'])) {
            return null;
        }

        return PageModel::findById($context['data']['iso_cart_jumpTo']);
    }
}
