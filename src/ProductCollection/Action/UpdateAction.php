<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Action;

use Isotope\CoreBundle\DependencyInjection\Attribute\AsProductCollectionAction;
use Isotope\CoreBundle\HttpFoundation\ActionResult;
use Isotope\CoreBundle\ProductCollection\AbstractProductCollection;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

#[AsProductCollectionAction]
class UpdateAction extends AbstractProductCollectionAction
{
    public function __construct(
        private readonly string $type,
        private readonly TranslatorInterface $translator,
        Environment $twig,
    ) {
        parent::__construct($twig);
    }

    public function supports(AbstractProductCollection $collection, array $context, array $options = []): bool
    {
        return true;
    }

    public function render(AbstractProductCollection $collection, array $context, array $options = []): string
    {
        return $this->renderView($this->type, [
            'label' => $this->translator->trans('MSC.buttonLabel.update', [], 'contao_default'),
        ]);
    }

    public function handle(AbstractProductCollection $collection, array $context, array $options = []): ActionResult
    {
        return new ActionResult();
    }
}
