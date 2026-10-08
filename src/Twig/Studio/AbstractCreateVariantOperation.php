<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Twig\Studio;

use Contao\CoreBundle\Twig\Studio\Operation\AbstractCreateVariantOperation as CoreAbstractCreateVariantOperationAlias;
use Contao\CoreBundle\Twig\Studio\Operation\OperationContext;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * @internal
 */
#[AutoconfigureTag('container.service_subscriber', ['id' => 'contao.twig.studio.cache_invalidator'])]
abstract class AbstractCreateVariantOperation extends CoreAbstractCreateVariantOperationAlias
{
    #[\Override]
    public function canExecute(OperationContext $context): bool
    {
        if ($context->isThemeContext()) {
            return false;
        }

        return 1 === preg_match('%^'.preg_quote($this->getPrefix(), '%').'($|/[^_/][^/]*$)%', $context->getIdentifier());
    }
}
