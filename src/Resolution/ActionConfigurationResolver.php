<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Resolution;

use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineUnsupportedActionException;
use Jmf\CrudEngine\Resolution\Action\ActionConfigResolverInterface;
use Webmozart\Assert\Assert;

/**
 * Resolves the bundle configuration array into a normalized, fully-expanded array, once, at
 * container build time. The entities/actions traversal lives here; each action is delegated to its
 * dedicated {@see ActionConfigResolverInterface} (which composes only the section resolvers that
 * action needs). Placeholders that depend only on the entity class and the action are expanded by
 * the part resolvers; request-time placeholders (e.g. redirection parameters referencing the
 * entity) are kept verbatim.
 *
 * @phpstan-import-type ResolvedAction from ActionConfigResolverInterface
 *
 * @phpstan-type ResolvedConfigurations array<class-string, array<non-empty-string, ResolvedAction>>
 */
readonly class ActionConfigurationResolver
{
    /**
     * @var array<string, ActionConfigResolverInterface>
     */
    private array $resolverByAction;

    /**
     * @param ActionConfigResolverInterface[] $actionConfigResolvers
     */
    public function __construct(
        private MapResolver $mapResolver,
        iterable $actionConfigResolvers,
    ) {
        Assert::allIsInstanceOf($actionConfigResolvers, ActionConfigResolverInterface::class);

        $indexed = [];

        foreach ($actionConfigResolvers as $actionConfigResolver) {
            $indexed[$actionConfigResolver->getActionName()] = $actionConfigResolver;
        }

        $this->resolverByAction = $indexed;
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return ResolvedConfigurations
     *
     * @throws CrudEngineInvalidConfigurationException
     * @throws CrudEngineMissingConfigurationException
     * @throws CrudEngineUnsupportedActionException
     */
    public function resolve(array $config): array
    {
        $schema = $this->mapResolver->resolve($config, 'schema');

        Assert::keyExists($config, 'entities');
        $entitiesConfig = $config['entities'];
        Assert::isMap($entitiesConfig);

        $resolved = [];

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

                $resolved[$entityClass][$action] = $this->getResolver($entityClass, $action)->resolve(
                    $schema,
                    $entityClass,
                    $action,
                    $actionConfig,
                );
            }
        }

        return $resolved;
    }

    /**
     * @param class-string     $entityClass
     * @param non-empty-string $action
     *
     * @throws CrudEngineUnsupportedActionException
     */
    private function getResolver(
        string $entityClass,
        string $action,
    ): ActionConfigResolverInterface {
        return $this->resolverByAction[$action]
            ??
            throw CrudEngineUnsupportedActionException::forAction($entityClass, $action);
    }
}
