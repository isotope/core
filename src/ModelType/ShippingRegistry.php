<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ModelType;

use Isotope\CoreBundle\Checkout\Shipping\ShippingInterface;
use Isotope\CoreBundle\Model\Shipping;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Contracts\Service\ServiceProviderInterface;

/**
 * @extends AbstractModelTypeRegistry<ShippingInterface, Shipping>
 */
#[Autoconfigure(public: true)]
class ShippingRegistry extends AbstractModelTypeRegistry
{
    public const TAG_NAME = 'isotope.shipping';

    public function __construct(ServiceProviderInterface $factories)
    {
        parent::__construct($factories, ShippingInterface::class);
    }
}
