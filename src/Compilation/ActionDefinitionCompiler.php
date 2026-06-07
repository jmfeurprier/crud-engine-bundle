<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Compilation;

use Jmf\CrudEngine\Compilation\Action\ActionDefinitionCompilerInterface;
use Jmf\CrudEngine\Compilation\Resolution\MapResolver;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineUnsupportedActionException;
use Webmozart\Assert\Assert;

/**
 * Resolves the bundle configuration array into a normalized, fully-expanded array, once, at
 * container build time. The entities/actions traversal lives here; each action is delegated to its
 * dedicated {@see ActionDefinitionCompilerInterface} (which composes only the section resolvers that
 * action needs). Placeholders that depend only on the entity class and the action are expanded by
 * the part resolvers; request-time placeholders (e.g. redirection parameters referencing the
 * entity) are kept verbatim.
 *
 * @phpstan-import-type CompiledAction from ActionDefinitionCompilerInterface
 *
 * @phpstan-type CompiledDefinitions array<class-string, array<non-empty-string, CompiledAction>>
 */
readonly class ActionDefinitionCompiler
{
    /**
     * @var array<string, ActionDefinitionCompilerInterface>
     */
    private array $compilerByAction;

    /**
     * @param ActionDefinitionCompilerInterface[] $actionDefinitionCompilers
     */
    public function __construct(
        private MapResolver $mapResolver,
        iterable $actionDefinitionCompilers,
    ) {
        Assert::allIsInstanceOf($actionDefinitionCompilers, ActionDefinitionCompilerInterface::class);

        $indexed = [];

        foreach ($actionDefinitionCompilers as $actionDefinitionCompiler) {
            $indexed[$actionDefinitionCompiler->getActionName()] = $actionDefinitionCompiler;
        }

        $this->compilerByAction = $indexed;
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return CompiledDefinitions
     *
     * @throws CrudEngineInvalidConfigurationException
     * @throws CrudEngineMissingConfigurationException
     * @throws CrudEngineUnsupportedActionException
     */
    public function compile(array $config): array
    {
        $schema = $this->mapResolver->resolve($config, 'schema');

        Assert::keyExists($config, 'entities');
        $entitiesConfig = $config['entities'];
        Assert::isMap($entitiesConfig);

        $compiled = [];

        foreach ($entitiesConfig as $entityClass => $entityConfig) {
            Assert::classExists($entityClass);
            Assert::isMap($entityConfig);

            if (!array_key_exists('actions', $entityConfig)) {
                throw new CrudEngineMissingConfigurationException(
                    entityClass:      $entityClass,
                    configurationKey: 'actions',
                );
            }

            $actionsConfig = $entityConfig['actions'];
            Assert::isMap($actionsConfig);

            foreach ($actionsConfig as $action => $actionConfig) {
                Assert::stringNotEmpty($action);
                Assert::isMap($actionConfig);

                $compiled[$entityClass][$action] = $this->getCompiler($entityClass, $action)->compile(
                    $schema,
                    $entityClass,
                    $action,
                    $actionConfig,
                );
            }
        }

        return $compiled;
    }

    /**
     * @param class-string     $entityClass
     * @param non-empty-string $action
     *
     * @throws CrudEngineUnsupportedActionException
     */
    private function getCompiler(
        string $entityClass,
        string $action,
    ): ActionDefinitionCompilerInterface {
        return $this->compilerByAction[$action]
            ??
            throw CrudEngineUnsupportedActionException::forAction($entityClass, $action);
    }
}
