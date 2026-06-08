<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Compilation\Action;

use Jmf\CrudEngine\Compilation\FormDefinitionCompiler;
use Jmf\CrudEngine\Compilation\RedirectionDefinitionCompiler;
use Jmf\CrudEngine\Compilation\RouteDefinitionCompiler;
use Jmf\CrudEngine\Compilation\ViewDefinitionCompiler;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\CrudEngine\Model\CrudAction;

/**
 * Resolves the normalized configuration of a single CRUD action into the array shape consumed by
 * {@see \Jmf\CrudEngine\Registry\ActionDefinitionHydrator}. One implementation
 * per action declares (by composition) which sections it carries — e.g. only create/update build a
 * form, only create/update/delete build a redirection.
 *
 * @phpstan-import-type CompiledForm from FormDefinitionCompiler
 * @phpstan-import-type CompiledRedirection from RedirectionDefinitionCompiler
 * @phpstan-import-type CompiledRoute from RouteDefinitionCompiler
 * @phpstan-import-type CompiledView from ViewDefinitionCompiler
 *
 * @phpstan-type CompiledAction array{
 *     form: CompiledForm|null,
 *     helperClass: class-string|null,
 *     redirection: CompiledRedirection|null,
 *     route: CompiledRoute,
 *     view: CompiledView,
 * }
 */
interface ActionDefinitionCompilerInterface
{
    public function getAction(): CrudAction;

    /**
     * @param array<string, mixed> $schema
     * @param class-string         $entityClass
     * @param array<string, mixed> $actionConfig
     *
     * @return CompiledAction
     *
     * @throws CrudEngineInvalidConfigurationException
     * @throws CrudEngineMissingConfigurationException
     */
    public function compile(
        array $schema,
        string $entityClass,
        CrudAction $action,
        array $actionConfig,
    ): array;
}
