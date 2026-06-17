<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration;

use Jmf\CrudEngine\Exception\CrudEngineDuplicateEntityException;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;
use Webmozart\Assert\Assert;

/**
 * Owns the `entities` side of the bundle configuration: assembles the final map from the per-entity
 * files discovered under the configured `paths` (filename + base namespace = FQCN) merged with the
 * inline `entities`, and registers a cache-invalidation resource per directory. A class defined more
 * than once (across files, or against an inline entry) is a configuration error.
 */
final readonly class EntityConfigurationLoader
{
    /**
     * @param array<string, mixed> $config         resolved `jmf_crud_engine` config (`paths` + `entities`)
     * @param string               $extensionAlias used to derive the default path
     *
     * @return array<string, array<string, mixed>> entity configs keyed by FQCN
     *
     * @throws CrudEngineDuplicateEntityException
     */
    public function load(
        array $config,
        ContainerBuilder $container,
        string $extensionAlias,
    ): array {
        $pathConfigs = $this->resolvePathConfigs(
            $config,
            $container,
            $extensionAlias,
        );

        $this->registerResources($pathConfigs, $container);

        $entitiesFromPaths = $this->loadFromPaths($pathConfigs);
        $inlineEntities    = $this->getInlineEntities($config);

        $duplicates = array_intersect_key(
            $entitiesFromPaths,
            $inlineEntities,
        );

        if ([] !== $duplicates) {
            throw new CrudEngineDuplicateEntityException(array_keys($duplicates));
        }

        return $entitiesFromPaths + $inlineEntities;
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return list<array{path: string, namespace: string}> resolved (absolute) directories
     */
    private function resolvePathConfigs(
        array $config,
        ContainerBuilder $container,
        string $extensionAlias,
    ): array {
        $pathConfigs = $config['paths'];
        Assert::isArray($pathConfigs);

        if ([] === $pathConfigs) {
            // Default: <config-dir>/packages/<extension alias>, e.g. config/packages/jmf_crud_engine.
            // `.kernel.config_dir` is the build-time materialization of Kernel::getConfigDir(), the
            // only handle a bundle extension has to it; the alias keeps the segment rename-safe.
            $pathConfigs = [
                [
                    'path'      => '%.kernel.config_dir%/packages/' . $extensionAlias,
                    'namespace' => 'App\\Entity',
                ],
            ];
        }

        $resolved = [];

        foreach ($pathConfigs as $pathConfig) {
            Assert::isArray($pathConfig);
            Assert::string($pathConfig['path']);
            Assert::string($pathConfig['namespace']);

            $directory = $container->getParameterBag()->resolveValue($pathConfig['path']);
            Assert::string($directory);

            $resolved[] = [
                'path'      => $directory,
                'namespace' => $pathConfig['namespace'],
            ];
        }

        return $resolved;
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return array<string, array<string, mixed>>
     */
    private function getInlineEntities(array $config): array
    {
        $inlineEntities = $config['entities'];

        Assert::isArray($inlineEntities);

        /** @var array<string, array<string, mixed>> $inlineEntities */
        return $inlineEntities;
    }

    /**
     * @param list<array{path: string, namespace: string}> $pathConfigs
     */
    private function registerResources(
        array $pathConfigs,
        ContainerBuilder $container,
    ): void {
        foreach ($pathConfigs as $pathConfig) {
            if (is_dir($pathConfig['path'])) {
                // DirectoryResource is mtime-based + recursive, so the compiled container is
                // rebuilt when a file in the directory is added, removed or edited.
                $container->addResource(new DirectoryResource($pathConfig['path'], '/\.yaml$/'));
            }
        }
    }

    /**
     * @param list<array{path: string, namespace: string}> $pathConfigs
     *
     * @return array<string, array<string, mixed>>
     *
     * @throws CrudEngineDuplicateEntityException
     */
    private function loadFromPaths(
        array $pathConfigs,
    ): array {
        $entities = [];

        foreach ($pathConfigs as $pathConfig) {
            $directory = $pathConfig['path'];

            if (!is_dir($directory)) {
                continue;
            }

            $namespace = trim($pathConfig['namespace'], '\\');

            foreach ((new Finder())->files()->in($directory)->name('*.yaml')->sortByName() as $file) {
                $relativeName = substr($file->getRelativePathname(), 0, -strlen('.yaml'));
                $class        = str_replace('/', '\\', $relativeName);
                $entityClass  = '' !== $namespace ? "{$namespace}\\{$class}" : $class;

                if (isset($entities[$entityClass])) {
                    throw new CrudEngineDuplicateEntityException([$entityClass]);
                }

                // PARSE_CONSTANT so files may use `!php/const ...` (e.g. route requirements),
                // matching what Symfony's own config loader enables for inline config.
                $parsed = Yaml::parseFile($file->getRealPath(), Yaml::PARSE_CONSTANT);

                if (is_array($parsed)) {
                    /** @var array<string, mixed> $parsed */
                    $entities[$entityClass] = $this->normalizeEntityConfig($parsed);
                } else {
                    $entities[$entityClass] = [];
                }
            }
        }

        return $entities;
    }

    /**
     * File loading bypasses the semantic-config tree, so reproduce the one normalization the
     * compiler relies on: the empty-action shorthand (`read:` with no body) parses to null and is
     * turned into [], matching the inline `entities` shape (the compiler fills the rest from the
     * schema).
     *
     * @param array<string, mixed> $entityConfig
     *
     * @return array<string, mixed>
     */
    private function normalizeEntityConfig(
        array $entityConfig,
    ): array {
        if (isset($entityConfig['actions']) && is_array($entityConfig['actions'])) {
            $entityConfig['actions'] = array_map(
                static fn(
                    mixed $actionConfig,
                ): mixed => $actionConfig ?? [],
                $entityConfig['actions'],
            );
        }

        return $entityConfig;
    }
}
