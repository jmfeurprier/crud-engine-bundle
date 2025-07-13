<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector;
use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\Config\RectorConfig;
use Rector\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector;
use Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector;
use Rector\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\YieldDataProviderRector;

$rootPath = realpath(__DIR__ . '/..') . '/';

return RectorConfig::configure()
    ->withCache($rootPath . 'var/cache/rector')
    ->withPaths(
        [
            $rootPath . 'src',
            $rootPath . 'tests',
        ],
    )
    ->withPhpSets()
    ->withSkip(
        [
            CatchExceptionNameMatchingTypeRector::class,
            EncapsedStringsToSprintfRector::class,
            RenameParamToMatchTypeRector::class,
            RenamePropertyToMatchTypeRector::class,
            RenameVariableToMatchMethodCallReturnTypeRector::class,
            YieldDataProviderRector::class,
        ],
    )
    ->withPreparedSets(
        deadCode:            true,
        codeQuality:         true,
        codingStyle:         true,
        typeDeclarations:    true,
        privatization:       true,
        naming:              true,
        instanceOf:          true,
        earlyReturn:         true,
        strictBooleans:      true,
        carbon:              true,
        rectorPreset:        true,
        phpunitCodeQuality:  true,
        doctrineCodeQuality: true,
        symfonyCodeQuality:  true,
        symfonyConfigs:      true,
    )
;
