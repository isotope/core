<?php

declare(strict_types=1);

use Contao\Rector\Rector\ContainerSessionToRequestStackSessionRector;
use Contao\Rector\Rector\LegacyFrameworkCallToServiceCallRector;
use Contao\Rector\Set\ContaoSetList;
use Contao\Rector\ValueObject\LegacyFrameworkCallToServiceCall;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {

    $rectorConfig->sets([
        //ContaoSetList::CONTAO_49,
        //ContaoSetList::CONTAO_413,
        //ContaoSetList::FQCN,
    ]);

    $rectorConfig->rule(ContainerSessionToRequestStackSessionRector::class);

    $rectorConfig->ruleWithConfiguration(LegacyFrameworkCallToServiceCallRector::class, [
        new LegacyFrameworkCallToServiceCall(\Contao\RequestToken::class, 'get', 'contao.csrf.token_manager', 'getDefaultTokenValue'),
    ]);

    $rectorConfig->fileExtensions(['php', 'html5']);
    $rectorConfig->parallel();
};
