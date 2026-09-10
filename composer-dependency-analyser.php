<?php

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return (new Configuration())
    // Optional integrations
    ->ignoreErrorsOnPackage('terminal42/contao-changelanguage', [ErrorType::DEV_DEPENDENCY_IN_PROD])
;
