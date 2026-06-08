<?php

declare(strict_types=1);

use Jmf\CrudEngine\Definition\FallbackMode;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition): void {
    // Re-usable per-action configuration fragments. Each builder returns a fresh
    // node, so the same fragment can be appended to several action nodes without
    // sharing (and mutating) a single instance.
    $helperNode = static fn (): NodeDefinition => (new TreeBuilder('helper', 'scalar'))->getRootNode();

    $formNode = static function (): NodeDefinition {
        /** @var ArrayNodeDefinition $node */
        $node = (new TreeBuilder('form'))->getRootNode();
        $node
            ->children()
                ->scalarNode('type')->end()
            ->end()
        ;

        return $node;
    };

    $redirectionNode = static function (): NodeDefinition {
        /** @var ArrayNodeDefinition $node */
        $node = (new TreeBuilder('redirection'))->getRootNode();
        $node
            ->children()
                ->scalarNode('fragment')->end()
                ->scalarNode('route')
                    ->isRequired()
                ->end()
                ->arrayNode('parameters')
                    ->variablePrototype()->end()
                ->end()
            ->end()
        ;

        return $node;
    };

    $routeNode = static function (): NodeDefinition {
        /** @var ArrayNodeDefinition $node */
        $node = (new TreeBuilder('route'))->getRootNode();
        $node
            ->children()
                ->scalarNode('path')->end()
                ->arrayNode('parameters')
                    ->variablePrototype()->end()
                ->end()
                ->arrayNode('requirements')
                    ->variablePrototype()->end()
                ->end()
            ->end()
        ;

        return $node;
    };

    $viewNode = static function (): NodeDefinition {
        /** @var ArrayNodeDefinition $node */
        $node = (new TreeBuilder('view'))->getRootNode();
        $node
            ->children()
                ->scalarNode('path')->end()
                ->arrayNode('variables')
                    ->variablePrototype()->end()
                ->end()
            ->end()
        ;

        return $node;
    };

    $definition->rootNode()
        ->fixXmlConfig('entity', 'entities')
        ->children()

            // Defaults for the patterns below live in Compiler; the
            // values here only override them. Maps (keys/route.paths/redirection) are
            // merged with the defaults, scalars/lists (route.name/view.path/form.type/
            // helper) replace them.
            ->arrayNode('schema')
                ->fixXmlConfig('key', 'keys')
                ->children()

                    ->arrayNode('helper')
                        ->stringPrototype()->cannotBeEmpty()->end()
                    ->end()

                    ->arrayNode('keys')
                        ->useAttributeAsKey('key')
                        ->stringPrototype()->cannotBeEmpty()->end()
                    ->end()

                    ->arrayNode('form')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->arrayNode('type')
                                ->stringPrototype()->cannotBeEmpty()->end()
                            ->end()
                            ->enumNode('fallback')
                                ->info('Behavior when no form type is configured or discovered.')
                                ->values(
                                    [
                                        FallbackMode::PROVIDE->value,
                                        FallbackMode::FAIL->value,
                                    ]
                                )
                                ->defaultValue(FallbackMode::PROVIDE->value)
                            ->end()
                        ->end()
                    ->end()

                    ->arrayNode('view')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('path')->cannotBeEmpty()->end()
                            ->enumNode('fallback')
                                ->info('Behavior when a view template is missing.')
                                ->values(
                                    [
                                        FallbackMode::PROVIDE->value,
                                        FallbackMode::FAIL->value,
                                    ]
                                )
                                ->defaultValue(FallbackMode::PROVIDE->value)
                            ->end()
                            ->arrayNode('variables')
                                ->variablePrototype()->end()
                            ->end()
                        ->end()
                    ->end()

                    ->arrayNode('redirection')
                        ->variablePrototype()->end()
                    ->end()

                    ->arrayNode('route')
                        ->children()
                            ->scalarNode('name')->cannotBeEmpty()->end()
                            ->arrayNode('paths')
                                ->variablePrototype()->end()
                            ->end()
                        ->end()
                    ->end()

                ->end()
            ->end()

            ->arrayNode('entities')
                ->info('Properties of CRUD entities.')
                ->useAttributeAsKey('class')
                ->arrayPrototype()
                    ->ignoreExtraKeys()
                    ->children()
                        ->arrayNode('actions')
                            ->isRequired()
                            ->children()

                                ->arrayNode('create')
                                    ->append($formNode())
                                    ->append($helperNode())
                                    ->append($redirectionNode())
                                    ->append($routeNode())
                                    ->append($viewNode())
                                ->end()

                                ->arrayNode('delete')
                                    ->append($helperNode())
                                    ->append($redirectionNode())
                                    ->append($routeNode())
                                    ->append($viewNode())
                                ->end()

                                ->arrayNode('index')
                                    ->append($helperNode())
                                    ->append($routeNode())
                                    ->append($viewNode())
                                ->end()

                                ->arrayNode('read')
                                    ->append($helperNode())
                                    ->append($routeNode())
                                    ->append($viewNode())
                                ->end()

                                ->arrayNode('update')
                                    ->append($formNode())
                                    ->append($helperNode())
                                    ->append($redirectionNode())
                                    ->append($routeNode())
                                    ->append($viewNode())
                                ->end()

                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end()
    ;
};
