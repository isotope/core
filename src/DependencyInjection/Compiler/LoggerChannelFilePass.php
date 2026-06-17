<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\DependencyInjection\Compiler;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @internal
 */
class LoggerChannelFilePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $channels = array_map(static fn ($config) => $config['channels'] ?? [], $container->getExtensionConfig('monolog'));

        foreach ($container->findTaggedServiceIds('monolog.logger') as $tags) {
            $channels[] = array_column($tags, 'channel');
        }

        $channels = array_unique(array_merge(...$channels));
        $channels = array_filter($channels, static fn (string $channel) => str_starts_with($channel, 'isotope.'));

        $handlersToChannels = $container->getParameter('monolog.handlers_to_channels');

        /** @var string $channel */
        foreach ($channels as $channel) {
            $id = 'monolog.handler.'.$channel;
            [$root, $type, $subchannel] = explode('.', $channel, 3) + [null, null, null];

            $definition = new Definition(RotatingFileHandler::class);
            $definition->setArguments([
                "%kernel.logs_dir%/{$root}_{$type}.log",
                10,
                Level::Debug,
                false,
            ]);

            $container->setDefinition($id, $definition);

            $logger = $container->getDefinition('monolog.logger.'.$channel);
            $logger->addMethodCall('pushHandler', [new Reference($id)]);

            if ($subchannel) {
                $logger->replaceArgument(0, $subchannel);
            }

            $handlersToChannels[$id] = [
                'type' => 'inclusive',
                'elements' => [$channel],
            ];
        }

        $container->setParameter('monolog.handlers_to_channels', $handlersToChannels);
    }
}
