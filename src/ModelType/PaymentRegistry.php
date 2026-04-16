<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ModelType;

use Isotope\CoreBundle\Checkout\Payment\PaymentInterface;
use Isotope\CoreBundle\Model\PaymentModel;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Contracts\Service\ServiceProviderInterface;

/**
 * @extends AbstractModelTypeRegistry<PaymentInterface, PaymentModel>
 */
#[Autoconfigure(public: true)]
class PaymentRegistry extends AbstractModelTypeRegistry
{
    public function __construct(ServiceProviderInterface $factories)
    {
        parent::__construct($factories, PaymentInterface::class);
    }
}
