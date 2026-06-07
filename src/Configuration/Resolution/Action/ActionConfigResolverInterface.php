<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration\Resolution\Action;

use Jmf\CrudEngine\Configuration\Resolution\FormConfigurationResolver;
use Jmf\CrudEngine\Configuration\Resolution\RedirectionConfigurationResolver;
use Jmf\CrudEngine\Configuration\Resolution\RouteConfigurationResolver;
use Jmf\CrudEngine\Configuration\Resolution\ViewConfigurationResolver;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;

/**
 * Resolves the normalized configuration of a single CRUD action into the array shape consumed by
 * {@see \Jmf\CrudEngine\Registry\ActionConfigurationHydrator}. One implementation
 * per action declares (by composition) which sections it carries — e.g. only create/update build a
 * form, only create/update/delete build a redirection.
 *
 * @phpstan-import-type ResolvedForm from FormConfigurationResolver
 * @phpstan-import-type ResolvedRedirection from RedirectionConfigurationResolver
 * @phpstan-import-type ResolvedRoute from RouteConfigurationResolver
 * @phpstan-import-type ResolvedView from ViewConfigurationResolver
 *
 * @phpstan-type ResolvedAction array{
 *     form: ResolvedForm|null,
 *     helperClass: class-string|null,
 *     redirection: ResolvedRedirection|null,
 *     route: ResolvedRoute,
 *     view: ResolvedView,
 * }
 */
interface ActionConfigResolverInterface
{
    public function getActionName(): string;

    /**
     * @param array<string, mixed> $schema
     * @param class-string         $entityClass
     * @param non-empty-string     $action
     * @param array<string, mixed> $actionConfig
     *
     * @return ResolvedAction
     *
     * @throws CrudEngineInvalidConfigurationException
     * @throws CrudEngineMissingConfigurationException
     */
    public function resolve(
        array $schema,
        string $entityClass,
        string $action,
        array $actionConfig,
    ): array;
}
