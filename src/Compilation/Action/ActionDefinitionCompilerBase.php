<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Compilation\Action;

use Jmf\CrudEngine\Compilation\FormDefinitionCompiler;
use Jmf\CrudEngine\Compilation\RedirectionDefinitionCompiler;
use Jmf\CrudEngine\Compilation\Resolution\ConfigurationValueResolver;
use Jmf\CrudEngine\Compilation\Resolution\MapResolver;
use Jmf\CrudEngine\Compilation\Resolution\PatternsResolver;
use Jmf\CrudEngine\Compilation\RouteDefinitionCompiler;
use Jmf\CrudEngine\Compilation\ViewDefinitionCompiler;
use Jmf\CrudEngine\Exception\CrudEngineInvalidConfigurationException;
use Jmf\CrudEngine\Exception\CrudEngineMissingConfigurationException;
use Jmf\CrudEngine\Model\EntityAction;
use Override;
use Webmozart\Assert\Assert;

/**
 * Shared template for the per-action compilers. Subclasses fill in the per-action specifics
 * via the hooks below; the section work and the key/helper resolution stay here, shared.
 *
 * @phpstan-import-type CompiledForm from FormDefinitionCompiler
 * @phpstan-import-type CompiledRedirection from RedirectionDefinitionCompiler
 * @phpstan-import-type CompiledAction from ActionDefinitionCompilerInterface
 */
abstract readonly class ActionDefinitionCompilerBase implements ActionDefinitionCompilerInterface
{
    /**
     * @var array<non-empty-string, non-empty-string>
     */
    private const array DEFAULT_KEYS = [
        'ActionKey'      => "{{ action|u.camel.title }}",
        'ActionKeys'     => "{{ action|u.camel.title|plural }}",
        'actionKey'      => "{{ action|u.camel }}",
        'actionKeys'     => "{{ action|u.camel|plural }}",
        'action_key'     => "{{ action|u.snake }}",
        'action_keys'    => "{{ action|u.snake|plural }}",
        'actiondashkey'  => "{{ action|u.kebab }}",
        'actiondashkeys' => "{{ action|u.kebab|plural }}",
        'EntityKey'      => "{{ entityClass|u.afterLast('\\\\').camel.title }}",
        'EntityKeys'     => "{{ entityClass|u.afterLast('\\\\').camel.title|plural }}",
        'entityKey'      => "{{ entityClass|u.afterLast('\\\\').camel }}",
        'entityKeys'     => "{{ entityClass|u.afterLast('\\\\').camel|plural }}",
        'entity_key'     => "{{ entityClass|u.afterLast('\\\\').snake }}",
        'entity_keys'    => "{{ entityClass|u.afterLast('\\\\').snake|plural }}",
        'entitydashkey'  => "{{ entityClass|u.afterLast('\\\\').kebab }}",
        'entitydashkeys' => "{{ entityClass|u.afterLast('\\\\').kebab|plural }}",
    ];

    /**
     * @var list<non-empty-string>
     */
    private const array DEFAULT_HELPERS = [
        "App\\Controller\\{{ EntityKey }}\\{{ ActionKey }}ActionHelper",
        "App\\Controller\\{{ EntityKey }}{{ ActionKey }}ActionHelper",
    ];

    public function __construct(
        protected ConfigurationValueResolver $configurationValueResolver,
        protected MapResolver $mapResolver,
        protected PatternsResolver $patternsResolver,
        protected FormDefinitionCompiler $formDefinitionCompiler,
        protected RouteDefinitionCompiler $routeDefinitionCompiler,
        protected RedirectionDefinitionCompiler $redirectionDefinitionCompiler,
        protected ViewDefinitionCompiler $viewDefinitionCompiler,
    ) {
    }

    #[Override]
    final public function compile(
        array $schema,
        EntityAction $entityAction,
        array $actionConfig,
    ): array {
        $keys = $this->resolveKeys($schema, $entityAction);

        return [
            'form'        => $this->compileFormDefinition(
                $schema,
                $keys,
                $entityAction,
                $actionConfig,
            ),
            'helperClass' => $this->resolveHelperClass(
                $schema,
                $keys,
                $entityAction,
                $actionConfig,
            ),
            'route'       => $this->routeDefinitionCompiler->compile(
                $schema,
                $keys,
                $entityAction,
                $actionConfig,
                $this->getDefaultRoutePath(),
            ),
            'redirection' => $this->compileRedirectionDefinition(
                $schema,
                $keys,
                $entityAction,
                $actionConfig,
            ),
            'view'        => $this->viewDefinitionCompiler->compile(
                $schema,
                $keys,
                $entityAction,
                $actionConfig,
            ),
        ];
    }

    /**
     * The conventional route path pattern for this action, used when neither the action config nor
     * the schema overrides it.
     *
     * @return non-empty-string
     */
    abstract protected function getDefaultRoutePath(): string;

    /**
     * Whether this action carries a form (create/update). Off by default.
     */
    protected function hasForm(): bool
    {
        return false;
    }

    /**
     * @param array<string, mixed>                      $schema
     * @param array<non-empty-string, non-empty-string> $keys
     * @param array<string, mixed>                      $actionConfig
     *
     * @return CompiledForm|null
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    private function compileFormDefinition(
        array $schema,
        array $keys,
        EntityAction $entityAction,
        array $actionConfig,
    ): ?array {
        if (!$this->hasForm()) {
            return null;
        }

        return $this->formDefinitionCompiler->compile(
            $schema,
            $keys,
            $entityAction,
            $actionConfig,
        );
    }

    /**
     * @param array<string, mixed>                      $schema
     * @param array<non-empty-string, non-empty-string> $keys
     * @param array<string, mixed>                      $actionConfig
     *
     * @return CompiledRedirection|null
     *
     * @throws CrudEngineInvalidConfigurationException
     * @throws CrudEngineMissingConfigurationException
     */
    private function compileRedirectionDefinition(
        array $schema,
        array $keys,
        EntityAction $entityAction,
        array $actionConfig,
    ): ?array {
        $default = $this->getDefaultRedirection();

        if (null === $default) {
            return null;
        }

        return $this->redirectionDefinitionCompiler->compile(
            $schema,
            $keys,
            $entityAction,
            $actionConfig,
            $default,
        );
    }

    /**
     * The conventional redirection for this action, applied after a successful mutation. Null means
     * the action has no redirection (index/read).
     *
     * @return array{route: non-empty-string, parameters: array<string, non-empty-string>}|null
     */
    protected function getDefaultRedirection(): ?array
    {
        return null;
    }

    /**
     * @param array<string, mixed> $schema
     *
     * @return array<non-empty-string, non-empty-string>
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    private function resolveKeys(
        array $schema,
        EntityAction $entityAction,
    ): array {
        /** @var array<non-empty-string, non-empty-string> $patterns */
        $patterns = array_merge(
            self::DEFAULT_KEYS,
            $this->mapResolver->resolve($schema, 'keys'),
        );

        $keys = [];

        foreach ($patterns as $name => $pattern) {
            // Keys are resolved against the source variables only (no $keys passed): a key may
            // reference "entityClass" and "action", but not another key. Referencing a key would
            // raise an "unknown variable" error under strict_variables; rethrow it as guidance.
            try {
                $value = $this->configurationValueResolver->resolve(
                    value:        $pattern,
                    keys:         [],
                    entityAction: $entityAction,
                );
            } catch (CrudEngineInvalidConfigurationException $e) {
                throw new CrudEngineInvalidConfigurationException(
                    message:  sprintf(
                                  'Failed resolving schema key "%s" (pattern "%s"). A key may only reference the source variables "entityClass" and "action", not other keys.',
                                  $name,
                                  $pattern,
                              ),
                    code:     $e->getCode(),
                    previous: $e,
                );
            }

            Assert::stringNotEmpty($value);

            $keys[$name] = $value;
        }

        return $keys;
    }

    /**
     * @param array<string, mixed>                      $schema
     * @param array<non-empty-string, non-empty-string> $keys
     * @param array<string, mixed>                      $actionConfig
     *
     * @return class-string|null
     *
     * @throws CrudEngineInvalidConfigurationException
     */
    private function resolveHelperClass(
        array $schema,
        array $keys,
        EntityAction $entityAction,
        array $actionConfig,
    ): ?string {
        if (array_key_exists('helper', $actionConfig)) {
            $helperClass = $actionConfig['helper'];

            if (null === $helperClass) {
                return null;
            }

            Assert::string($helperClass);
            Assert::classExists($helperClass);

            return $helperClass;
        }

        foreach ($this->patternsResolver->resolve($schema, 'helper', self::DEFAULT_HELPERS) as $pattern) {
            $class = $this->configurationValueResolver->resolve(
                $pattern,
                $keys,
                $entityAction,
            );

            if (class_exists($class)) {
                return $class;
            }
        }

        return null;
    }
}
