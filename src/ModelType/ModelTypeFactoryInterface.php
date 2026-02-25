<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ModelType;

use Contao\Model;
use Isotope\CoreBundle\Store\Context\StoreContext;

/**
 * @template TType of object
 * @template TModel of Model
 */
interface ModelTypeFactoryInterface
{
    /**
     * @param TModel $model
     *
     * @return TType
     */
    public function create(Model $model, StoreContext $context): object;
}
