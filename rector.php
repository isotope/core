<?php

declare(strict_types=1);

use Contao\Rector\Rector\ContainerSessionToRequestStackSessionRector;
use Contao\Rector\Set\ContaoSetList;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {

    $rectorConfig->sets([
        ContaoSetList::CONTAO_49,
        ContaoSetList::CONTAO_413,
        ContaoSetList::FQCN,
    ]);

    $rectorConfig->rule(ContainerSessionToRequestStackSessionRector::class);

    $rectorConfig->fileExtensions(['php', 'html5']);
    $rectorConfig->parallel();
};
