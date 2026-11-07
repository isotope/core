<?php

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return (new Configuration())
    // Optional integrations
    ->ignoreErrorsOnPackage('terminal42/contao-changelanguage', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    // Provides array_find(), array_any() and array_all() on PHP 8.3.
    ->ignoreErrorsOnPackage('symfony/polyfill-php84', [ErrorType::UNUSED_DEPENDENCY])
    // Doctrine's test configuration requires the LazyGhost support removed in VarExporter 8.
    ->ignoreErrorsOnPackage('symfony/var-exporter', [ErrorType::UNUSED_DEPENDENCY])
;
