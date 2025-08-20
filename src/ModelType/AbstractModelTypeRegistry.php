<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ModelType;

use Contao\Model;
use Contao\Model\Collection;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Contracts\Service\ServiceProviderInterface;

/**
 * @template TType of object
 * @template TModel of Model
 */
class AbstractModelTypeRegistry
{
    /**
     * @param ServiceProviderInterface<ModelTypeFactoryInterface<TType, TModel>> $factories
     */
    public function __construct(
        protected readonly ServiceProviderInterface $factories,
        protected readonly string $interface,
    ) {
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
            $modelOrCollection instanceof Collection => array_map(fn ($m) => $this->get($m), $modelOrCollection->getModels()),
            \is_array($modelOrCollection) => array_map(fn ($m) => $this->get($m), $modelOrCollection),
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
            throw new \RuntimeException(\sprintf('Missing factory for %s of type "%s"', $model::class, $model->type), previous: $e);
        }

        $instance = $factory->create($model);

        if (!$instance instanceof $this->interface) {
            throw new \RuntimeException($instance::class.' is not instanceof '.$this->interface);
        }

        return $instance;
    }
}
