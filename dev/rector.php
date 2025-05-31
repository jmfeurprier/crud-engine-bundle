<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector;
use Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector;
use Rector\CodingStyle\Rector\ClassMethod\NewlineBeforeNewAssignSetRector;
use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector;
use Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Rector\Config\RectorConfig;

$rootPath = realpath(__DIR__ . '/..') . '/';

return RectorConfig::configure()
    ->withCache($rootPath . 'var/cache')
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
            CountArrayToEmptyArrayComparisonRector::class,
            EncapsedStringsToSprintfRector::class,
            FlipTypeControlToUseExclusiveTypeRector::class,
            NewlineAfterStatementRector::class,
            NewlineBeforeNewAssignSetRector::class,
            SimplifyIfElseToTernaryRector::class,
        ],
    )
    ->withPreparedSets(
        deadCode:         true,
        codeQuality:      true,
        codingStyle:      true,
        typeDeclarations: true,
        privatization:    true,
        instanceOf:       true,
        earlyReturn:      true,
        strictBooleans:   true,
    )
;
