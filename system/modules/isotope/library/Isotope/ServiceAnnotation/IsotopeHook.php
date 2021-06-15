<?php

declare(strict_types=1);

namespace Isotope\ServiceAnnotation;

use Doctrine\Common\Annotations\Annotation\Attribute;
use Doctrine\Common\Annotations\Annotation\Attributes;
use Doctrine\Common\Annotations\Annotation\Target;
use Terminal42\ServiceAnnotationBundle\Annotation\ServiceTagInterface;

/**
 * Annotation to register an Isotope hook.
 *
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 * @Attributes({
 *     @Attribute("value", type="string", required=true),
 *     @Attribute("priority", type="int"),
 * })
 */
final class IsotopeHook implements ServiceTagInterface
{
    /**
     * @var string
     */
    public $value;

    /**
     * @var int
     */
    public $priority;

    public function getName(): string
    {
        return 'isotope.hook';
    }

    public function getAttributes(): array
    {
        $attributes = ['hook' => $this->value];

        if ($this->priority) {
            $attributes['priority'] = $this->priority;
        }

        return $attributes;
    }
}
