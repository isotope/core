<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Plus\RemoveDeadZeroAndOneOperationRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->skip([
        __DIR__.'/contao/dca/*',
        __DIR__.'/src/DataContainer/*',

        // @see https://github.com/rectorphp/rector/issues/9827
        RemoveDeadZeroAndOneOperationRector::class,
    ]);
};
