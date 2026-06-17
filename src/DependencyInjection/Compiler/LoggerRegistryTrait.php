<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\DependencyInjection\Compiler;

use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

trait LoggerRegistryTrait
{
    private function registerLogger(Reference $reference, Definition $definition, Definition $registry, string $channel): void
    {
        $class = $definition->getClass() ?: (string) $reference;

        if (!is_a($class, LoggerAwareInterface::class, true)) {
            return;
        }

        $serviceId = 'monolog.logger';

        $ref = new \ReflectionClass($class);

        if ($attributes = $ref->getAttributes(WithMonologChannel::class)) {
            $channel = $attributes[0]->getArguments()[0];
            $serviceId .= '.'.$channel;
        }

        // Add tag to the registry to make Monolog create the channel
        $registry->addTag('monolog.logger', ['channel' => $channel]);

        $registry->addMethodCall('setLogger', [$class, new Reference($serviceId)]);
    }
}
