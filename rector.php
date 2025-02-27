<?php

declare(strict_types=1);

use Contao\Rector\Rector\ContainerSessionToRequestStackSessionRector;
use Contao\Rector\Rector\LegacyFrameworkCallToServiceCallRector;
use Contao\Rector\Set\ContaoSetList;
use Contao\Rector\ValueObject\LegacyFrameworkCallToServiceCall;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->skip([__DIR__.'/system/modules/*']);
};
