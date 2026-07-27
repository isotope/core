<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Content;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Isotope\CoreBundle\Checkout\Step\CheckoutStepInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ServiceLocator;

#[AsCallback('tl_content', 'fields.iso_order_conditions.fields.step.options')]
class OrderConditionStepOptionsListener
{
    /**
     * @param ServiceLocator<CheckoutStepInterface> $checkoutSteps
     */
    public function __construct(
        #[AutowireLocator('isotope.checkout_step', 'type')] private readonly ServiceLocator $checkoutSteps,
    ) {
    }

    public function __invoke(): array
    {
        $options = array_keys(iterator_to_array($this->checkoutSteps));
        $options[] = '_summary';

        return $options;
    }
}
