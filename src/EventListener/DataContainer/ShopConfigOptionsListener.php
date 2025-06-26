<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Isotope\CoreBundle\Backend\ShopConfig\ShopConfigInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

#[AsCallback('tl_user', 'fields.iso_modules.options')]
#[AsCallback('tl_user_group', 'fields.iso_modules.options')]
class ShopConfigOptionsListener
{
    /**
     * @param iterable<ShopConfigInterface> $shopConfigs
     */
    public function __construct(
        #[AutowireIterator('isotope.shop_config')] private readonly iterable $shopConfigs,
    ) {
    }

    public function __invoke(): array
    {
        $options = [];

        foreach ($this->shopConfigs as $config) {
            $options[$config->getGroup()][] = $config->getName();
        }

        return $options;
    }
}
