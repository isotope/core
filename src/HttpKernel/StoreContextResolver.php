<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\HttpKernel;

use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\Model;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\StoreManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * @internal
 */
class StoreContextResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly ScopeMatcher $scopeMatcher,
        private readonly StoreManager $storeManager,
    ) {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): array
    {
        if (!$this->scopeMatcher->isContaoRequest($request)) {
            return [];
        }

        if (!is_a($argument->getType(), StoreContext::class, true)) {
            return [];
        }

        return [$this->storeManager->getCurrentContext()];
    }
}
