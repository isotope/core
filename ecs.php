<?php

declare(strict_types=1);

use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->skip([
        './contao/templates',
        './system/modules/isotope',
        './system/modules/isotope_rules',
        './system/modules/isotope_reports',
        './src/DataContainer/*'
    ]);
};
