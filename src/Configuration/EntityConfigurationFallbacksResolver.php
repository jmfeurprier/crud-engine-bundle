<?php

namespace Jmf\CrudEngine\Configuration;

use Symfony\Component\String\Inflector\EnglishInflector;
use Symfony\Component\String\Inflector\InflectorInterface;
use function Symfony\Component\String\u;

readonly class EntityConfigurationFallbacksResolver
{
    public function __construct(
        private InflectorInterface $inflector,
        private string $baseNamespace = 'App',
    ) {
    }

    /**
     * @param class-string $class
     */
    public function resolveEntityName(
        string $class,
    ): string {
        return u($class)->afterLast('\\')->snake()->toString();
    }

    /**
     * @param class-string     $class
     * @param non-empty-string $action
     *
     * @return null|class-string
     */
    public function tryResolveHelperClass(
        string $class,
        string $action,
    ): ?string {
        $classShortName  = $this->getClassShortName($class);
        $actionCamelName = $this->getActionCamelName($action);

        $candidates = [
            "{$this->baseNamespace}\\Controller\\{$classShortName}\\{$actionCamelName}ActionHelper",
            "{$this->baseNamespace}\\Controller\\{$classShortName}{$actionCamelName}ActionHelper",
        ];

        foreach ($candidates as $candidate) {
            if (class_exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @param class-string     $class
     * @param non-empty-string $action
     *
     * @return null|class-string
     */
    public function tryResolveFormTypeClass(
        string $class,
        string $action,
    ): ?string {
        $classShortName  = $this->getClassShortName($class);
        $actionCamelName = $this->getActionCamelName($action);

        $candidates = [
            "{$this->baseNamespace}\\Form\\{$classShortName}\\{$actionCamelName}Type",
            "{$this->baseNamespace}\\Form\\{$classShortName}{$actionCamelName}Type",
        ];

        foreach ($candidates as $candidate) {
            if (class_exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @param class-string     $class
     * @param non-empty-string $action
     */
    public function resolveViewPath(
        string $class,
        string $action,
    ): string {
        return u($class)->afterLast('\\')->snake()->append("/{$action}.html.twig")->toString();
    }

    /**
     * @param class-string     $class
     * @param non-empty-string $action
     */
    public function tryResolveRoutePath(
        string $class,
        string $action,
    ): ?string {
        $token  = u($class)->afterLast('\\');
        $tokens = $this->inflector->pluralize($token);

        if (1 !== count($tokens)) {
            return null;
        }

        $token = $tokens[0];
        $token = u($token)->kebab()->toString();

        return match ($action) {
            'create' => "{$token}/create",
            'delete' => "{$token}/{id}/delete",
            'index' => "{$token}",
            'read' => "{$token}/{id}",
            'update' => "{$token}/{id}/update",
            default => null,
        };
    }

    /**
     * @param class-string $class
     */
    private function getClassShortName(string $class): string
    {
        return u($class)->afterLast('\\')->toString();
    }

    /**
     * @param non-empty-string $action
     */
    private function getActionCamelName(string $action): string
    {
        return u($action)->camel()->title()->toString();
    }
}
