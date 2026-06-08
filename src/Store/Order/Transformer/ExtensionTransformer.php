<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Order\Transformer;

use Isotope\CoreBundle\ProductCollection\Extension\ProductCollectionExtensionInterface;

/**
 * @experimental
 */
class ExtensionTransformer
{
    public static function transform(array $extensions): array
    {
        $data = [];

        foreach ($extensions as $extension) {
            if (!$extension instanceof ProductCollectionExtensionInterface) {
                continue;
            }

            $data[$extension::class] = $extension->jsonSerialize();
        }

        return $data;
    }
}
