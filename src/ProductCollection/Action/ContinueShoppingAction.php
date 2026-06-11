<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Action;

use Isotope\CoreBundle\DependencyInjection\Attribute\AsProductCollectionAction;
use Isotope\CoreBundle\HttpFoundation\ActionResult;
use Isotope\CoreBundle\ProductCollection\AbstractProductCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

#[AsProductCollectionAction]
class ContinueShoppingAction extends AbstractProductCollectionAction
{
    public function __construct(
        private readonly string $type,
        private readonly UriSigner $uriSigner,
        private readonly TranslatorInterface $translator,
        Environment $twig,
    ) {
        parent::__construct($twig);
    }

    public function supports(AbstractProductCollection $collection, array $context, array $options = []): bool
    {
        return ($context['data']['iso_continueShopping'] ?? null) && null !== $this->getHref($context);
    }

    public function render(AbstractProductCollection $collection, array $context, array $options = []): string
    {
        return $this->renderView($this->type, [
            'label' => $this->translator->trans('MSC.continueShoppingBT', [], 'contao_default'),
            'href' => $this->getHref($context),
            ...$options,
        ]);
    }

    public function handle(AbstractProductCollection $collection, array $context, array $options = []): ActionResult
    {
        return new ActionResult(null, $this->getHref($context));
    }

    private function getHref(array $context): string|null
    {
        $request = $context['app']?->getRequest();

        if (
            !$request instanceof Request
            || !$request->query->has('continue')
            || !$this->uriSigner->checkRequest($request)
        ) {
            return null;
        }

        return base64_decode($request->query->get('continue'), true);
    }
}
