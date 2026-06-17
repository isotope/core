<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ModelType;

use Contao\Model;
use Contao\Model\Collection;
use Isotope\CoreBundle\ModelType\Exception\ContainerNotFoundException;
use Isotope\CoreBundle\ModelType\Exception\FactoryNotFoundException;
use Isotope\CoreBundle\ModelType\Exception\InvalidInstanceException;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Contracts\Service\ServiceProviderInterface;

/**
 * @template TType of object
 * @template TModel of Model
 */
class AbstractModelTypeRegistry
{
    /**
     * @var array<class-string<ModelTypeInterface::class>, ContainerInterface|null>
     */
    protected array $subscribedServices = [];

    /**
     * @var array<class-string, LoggerAwareInterface>
     */
    protected array $loggers = [];

    /**
     * @param ServiceProviderInterface<ModelTypeFactoryInterface<TType, TModel>> $factories
     */
    public function __construct(
        protected readonly ServiceProviderInterface $factories,
        protected readonly string $interface,
    ) {
    }

    public function setContainer(string $class, ContainerInterface $container): void
    {
        $this->subscribedServices[$class] = $container;
    }

    public function setLogger(string $class, LoggerInterface $logger): void
    {
        $this->loggers[$class] = $logger;
    }

    /**
     * @param TModel|Collection<TModel>|array<TModel>|null $modelOrCollection
     *
     * @return ($modelOrCollection is array|Collection<TModel> ? array<TType> : ($modelOrCollection is null ? null : TType) )
     */
    public function get(Collection|Model|array|null $modelOrCollection, StoreContext $context, bool $skipMissing = false): array|object|null
    {
        try {
            return match (true) {
                null === $modelOrCollection => null,
                $modelOrCollection instanceof Collection => array_filter(array_map(fn (Model $model) => $this->get($model, $context, $skipMissing), $modelOrCollection->getModels())),
                \is_array($modelOrCollection) => array_filter(array_map(fn (Model $model) => $this->get($model, $context, $skipMissing), $modelOrCollection)),
                empty($modelOrCollection->type) => null,
                default => $this->create($modelOrCollection, $context),
            };
        } catch (FactoryNotFoundException $e) {
            if ($skipMissing) {
                return null;
            }

            throw $e;
        }
    }

    /**
     * @param TModel $model
     *
     * @return TType
     */
    protected function create(Model $model, StoreContext $context): object
    {
        try {
            $factory = $this->factories->get($model->type);
        } catch (ServiceNotFoundException $e) {
            throw new FactoryNotFoundException($model->type, $model::class, previous: $e);
        }

        $instance = $factory->create($model, $context);

        if (!$instance instanceof $this->interface) {
            throw new InvalidInstanceException($instance, $this->interface);
        }

        if ($instance instanceof ContainerAwareInterface) {
            if (!isset($this->subscribedServices[$instance::class])) {
                throw new ContainerNotFoundException($instance::class);
            }

            $instance->setContainer($this->subscribedServices[$instance::class]);
        }

        if ($instance instanceof LoggerAwareInterface && isset($this->loggers[$instance::class])) {
            $instance->setLogger($this->loggers[$instance::class]);
        }

        return $instance;
    }
}
