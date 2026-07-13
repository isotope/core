<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Action;

use Isotope\CoreBundle\DependencyInjection\Attribute\AsProductAction;
use Isotope\CoreBundle\HttpFoundation\ActionResult;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\Render\ViewInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

#[AsProductAction]
class UpdateAction extends AbstractProductAction
{
    public function __construct(
        private readonly string $type,
        private readonly TranslatorInterface $translator,
        Environment $twig,
    ) {
        parent::__construct($twig);
    }

    public function supports(CoreProduct $product, array $configuration, ViewInterface $view): bool
    {
        return true;
    }

    public function render(CoreProduct $product, array $configuration, ViewInterface $view): string
    {
        return $this->renderView($this->type, [
            'label' => $this->translator->trans('MSC.buttonLabel.update', [], 'contao_default'),
        ]);
    }

    public function handle(CoreProduct $product, array $configuration, ViewInterface $view): ActionResult
    {
        return new ActionResult();
    }
}
