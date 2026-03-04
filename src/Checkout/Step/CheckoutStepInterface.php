<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Step;

use Isotope\CoreBundle\Store\Context\StoreContext;
use Symfony\Component\HttpFoundation\Request;

interface CheckoutStepInterface
{
    public function getSummary(Request $request, StoreContext $context, array $config): CheckoutStepSummary|null;
}
