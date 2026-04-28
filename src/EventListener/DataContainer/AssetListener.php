<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Symfony\Component\Asset\Packages;

#[AsHook('loadDataContainer')]
class AssetListener
{
    private const array ASSETS = [
        'tl_member' => ['list.operations.address_book.icon'],
        'tl_iso_product' => [
            'list.operations.related.icon',
        ],
        'tl_iso_product_collection' => [
            'list.operations.details.icon',
            'list.operations.print_document.icon',
        ],
    ];

    public function __construct(private readonly Packages $packages)
    {
    }

    public function __invoke(string $table): void
    {
        if (!isset(self::ASSETS[$table])) {
            return;
        }

        foreach (self::ASSETS[$table] as $target) {
            $keys = explode('.', $target);

            $dcaRef = &$this->getDcaReference($table, $keys);
            $dcaRef = $this->packages->getUrl($dcaRef, 'isotope');
        }
    }

    private function &getDcaReference(string $table, array $keys): string
    {
        $dcaRef = &$GLOBALS['TL_DCA'][$table];

        foreach ($keys as $key) {
            $dcaRef = &$dcaRef[$key];
        }

        return $dcaRef;
    }
}
