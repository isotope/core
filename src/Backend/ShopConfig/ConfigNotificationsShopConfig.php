<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Backend\ShopConfig;

use Symfony\Component\Asset\Packages;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @internal
 */
final class ConfigNotificationsShopConfig extends AbstractCoreShopConfig
{
    public function __construct(
        Packages $assets,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
        parent::__construct($assets);
    }

    public function getUrl(): string
    {
        return $this->urlGenerator->generate('contao_backend', ['do' => 'nc_notifications']);
    }

    public function getTables(): array
    {
        return [];
    }

    public static function getDefaultPriority(): int
    {
        return 30;
    }
}
