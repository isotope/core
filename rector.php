<?php

declare(strict_types=1);

use Contao\Rector\Rector\ConstantToServiceParameterRector;
use Contao\Rector\Rector\InsertTagsServiceRector;
use Contao\Rector\Rector\LoginConstantsToSymfonySecurityRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector;
use Rector\DeadCode\Rector\Plus\RemoveDeadZeroAndOneOperationRector;
use Rector\Php54\Rector\Array_\LongArrayToShortArrayRector;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->skip([
        LongArrayToShortArrayRector::class,
        ClosureToArrowFunctionRector::class,
        InsertTagsServiceRector::class,
        ConstantToServiceParameterRector::class,
        StringClassNameToClassConstantRector::class,
        RemoveEmptyClassMethodRector::class => ['system/modules/isotope_rules/library/Isotope/Rules.php'],
        RemoveDeadZeroAndOneOperationRector::class => ['system/modules/isotope/library/Isotope/Upgrade.php'],
        RemoveExtraParametersRector::class => ['system/modules/isotope/library/Isotope/Model/ProductCollection.php'],
        'system/modules/isotope/drivers/*',
        'system/modules/isotope/library/UnitedPrototype/*',
    ]);
};
