<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ModelType;

use Contao\Model;
use Contao\Model\Collection;
use Isotope\CoreBundle\Attribute\AttributeInterface;
use Isotope\CoreBundle\ModelType\Exception\InvalidInstanceException;
use Isotope\CoreBundle\ModelType\Exception\MissingFactoryException;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Contracts\Service\ServiceProviderInterface;

/**
 * @template TType of object
 * @template TModel of Model
 */
class AbstractModelTypeRegistry
{
    /**
     * @var array<class-string<AttributeInterface>, ContainerInterface|null>
     */
    protected array $subscribedServices = [];

    /**
     * @param ServiceProviderInterface<ModelTypeFactoryInterface<TType, TModel>> $factories
     */
    public function __construct(
        protected readonly ServiceProviderInterface $factories,
        protected readonly string $interface,
    ) {
    }

    public function setContainer(string $attributeClass, ContainerInterface $container): void
    {
        $this->subscribedServices[$attributeClass] = $container;
    }

    /**
     * @param TModel|Collection<TModel>|array<TModel>|null $modelOrCollection
     *
     * @return ($modelOrCollection is array|Collection<TModel> ? array<TType> : ($modelOrCollection is null ? null : TType) )
     */
    public function get(Collection|Model|array|null $modelOrCollection): array|object|null
    {
        return match (true) {
            null === $modelOrCollection => null,
            $modelOrCollection instanceof Collection => array_map($this->get(...), $modelOrCollection->getModels()),
            \is_array($modelOrCollection) => array_map($this->get(...), $modelOrCollection),
            empty($modelOrCollection->type) => null,
            default => $this->create($modelOrCollection),
        };
    }

    /**
     * @param TModel $model
     *
     * @return TType
     */
    protected function create(Model $model): object
    {
        try {
            $factory = $this->factories->get($model->type);
        } catch (ServiceNotFoundException $e) {
            throw new MissingFactoryException($model->type, $model::class, previous: $e);
        }

        $instance = $factory->create($model);

        if (!$instance instanceof $this->interface) {
            throw new InvalidInstanceException($instance, $this->interface);
        }

        if ($instance instanceof ContainerAwareInterface) {
            if (!isset($this->subscribedServices[$instance::class])) {
                throw new \RuntimeException($instance::class.' requires a service container but the registry does not have one');
            }

            $instance->setContainer($this->subscribedServices[$instance::class]);
        }

        return $instance;
    }
}
