<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Member;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Isotope\CoreBundle\Store\Event\ContextCreatedEvent;
use Isotope\CoreBundle\Store\StoreManager;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;

class CountryLimitListener
{
    private const CONTEXT_PROPERTY = 'limitMemberCountries';

    public function __construct(
        private readonly ScopeMatcher $scopeMatcher,
        private readonly StoreManager $storeManager,
        private readonly RequestStack $requestStack,
    ) {
    }

    #[AsEventListener]
    public function storeContextConfig(ContextCreatedEvent $event): void
    {
        $context = $event->getContext();
        $config = $event->getConfig();

        $context->set(self::CONTEXT_PROPERTY, (bool) $config->limitMemberCountries);
    }

    #[AsCallback('tl_member', 'fields.country.attributes')]
    public function limitMemberCountries(array $attributes): array
    {
        if (!$this->scopeMatcher->isFrontendRequest($this->requestStack->getCurrentRequest())) {
            return $attributes;
        }

        $context = $this->storeManager->getCurrentContext();

        if (!$context->get(self::CONTEXT_PROPERTY)) {
            return $attributes;
        }

        $countries = array_unique(array_merge($context->getBillingAddressConfig()->getCountryCodes(), $context->getShippingAddressConfig()->getCountryCodes()));

        $options = (array) ($attributes['options'] ?? []);
        $options = array_values(array_filter($options, static fn (array $option) => \in_array($option['value'], $countries, true)));

        if (1 === \count($options)) {
            $GLOBALS['TL_DCA']['tl_member']['fields']['country']['default'] = $options[0]['value'];
        }

        $attributes['options'] = $options;

        return $attributes;
    }
}
