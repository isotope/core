<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Migration\Event;

use Symfony\Contracts\EventDispatcher\Event;

class ModuleToElementMigrationEvent extends Event
{
    public function __construct(
        public readonly array $data,
        public array $tableData,
        public array $jsonData,
    ) {
    }
}
