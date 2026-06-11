<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\ItemAction;

use Isotope\CoreBundle\DependencyInjection\Attribute\AsProductCollectionItemAction;
use Isotope\CoreBundle\Exception\RuntimeException;
use Isotope\CoreBundle\HttpFoundation\ActionResult;
use Isotope\CoreBundle\ProductCollection\AbstractProductCollection;
use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\Store\CartManager;
use Isotope\CoreBundle\Store\StoreManager;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

#[AsProductCollectionItemAction]
class RemoveAction extends AbstractProductCollectionItemAction
{
    public function __construct(
        private readonly string $type,
        private readonly StoreManager $storeManager,
        private readonly CartManager $cartManager,
        private readonly TranslatorInterface $translator,
        Environment $twig,
    ) {
        parent::__construct($twig);
    }

    public function supports(AbstractProductCollection $collection, LineItem $item, array $context): bool
    {
        return $collection instanceof Cart && !$item->isDynamic() && $item->getId() > 0;
    }

    public function render(AbstractProductCollection $collection, LineItem $item, array $context): string
    {
        return $this->renderView($this->type, $item->getId(), [
            'title' => $this->translator->trans('MSC.removeProductLinkTitle', [$item->getLabel()], 'contao_default'),
            'label' => $this->translator->trans('MSC.removeProductLinkText', [], 'contao_default'),
        ]);
    }

    public function handle(AbstractProductCollection $collection, LineItem $item, array $context, bool $isAjax = true): ActionResult
    {
        if (!$collection instanceof Cart) {
            throw new RuntimeException(__METHOD__.' expects a Cart');
        }

        $this->cartManager->remove($collection, $item->getId(), $this->storeManager->getCurrentContext());

        return new ActionResult();
    }
}
