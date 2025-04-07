<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\DependencyInjection;

use Isotope\CoreBundle\DependencyInjection\Attribute\AsDocument;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsIsotopeHook;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsPayment;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsShipping;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class IsotopeCoreExtension extends Extension
{
    public function getAlias(): string
    {
        return 'isotope';
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../../config'),
        );

        $loader->load('services.yaml');

        $attributesForAutoconfiguration = [
            AsIsotopeHook::class => 'isotope.hook',
            AsPayment::class => 'isotope.payment',
            AsShipping::class => 'isotope.shipping',
            AsDocument::class => 'isotope.document',
        ];

        foreach ($attributesForAutoconfiguration as $attributeClass => $tag) {
            $container->registerAttributeForAutoconfiguration(
                $attributeClass,
                static function (ChildDefinition $definition, object $attribute, \Reflector $reflector) use ($attributeClass, $tag): void {
                    $tagAttributes = get_object_vars($attribute);

                    if ($reflector instanceof \ReflectionMethod) {
                        if (isset($tagAttributes['method'])) {
                            throw new LogicException(\sprintf('%s attribute cannot declare a method on "%s::%s()".', $attributeClass, $reflector->getDeclaringClass()->getName(), $reflector->getName()));
                        }

                        $tagAttributes['method'] = $reflector->getName();
                    }

                    $definition->addTag($tag, $tagAttributes);
                },
            );
        }
    }
}
