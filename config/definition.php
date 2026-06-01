<?php

declare(strict_types=1);

use Jmf\CrudEngine\Configuration\Schema\View\ViewFallbackMode;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition): void {
    $definition->rootNode()
        ->fixXmlConfig('entity', 'entities')
        ->children()

            ->arrayNode('schema')
                ->fixXmlConfig('key', 'keys')
                ->children()

                    ->arrayNode('helper')
                        ->stringPrototype()
                            ->cannotBeEmpty()
                            ->defaultValue(
                                [
                                    "App\\Controller\\{{ EntityKey }}\\{{ ActionKey }}ActionHelper",
                                    "App\\Controller\\{{ EntityKey }}{{ ActionKey }}ActionHelper",
                                ]
                            )
                        ->end()
                    ->end()

                    ->arrayNode('keys')
                        ->cannotBeEmpty()
                        ->defaultValue(
                            [
                                'ActionKey'=>      "{{ action|u.camel.title }}",
                                'ActionKeys'=>     "{{ action|u.camel.title|plural }}",
                                'actionKey'=>      "{{ action|u.camel }}",
                                'actionKeys'=>     "{{ action|u.camel|plural }}",
                                'action_key'=>     "{{ action|u.snake }}",
                                'action_keys'=>    "{{ action|u.snake|plural }}",
                                'actiondashkey'=>  "{{ action|u.kebab }}",
                                'actiondashkeys'=> "{{ action|u.kebab|plural }}",
                                'EntityKey'=>      "{{ entityClass|u.afterLast('\\\\').camel.title }}",
                                'EntityKeys'=>     "{{ entityClass|u.afterLast('\\\\').camel.title|plural }}",
                                'entityKey'=>      "{{ entityClass|u.afterLast('\\\\').camel }}",
                                'entityKeys'=>     "{{ entityClass|u.afterLast('\\\\').camel|plural }}",
                                'entity_key'=>     "{{ entityClass|u.afterLast('\\\\').snake }}",
                                'entity_keys'=>    "{{ entityClass|u.afterLast('\\\\').snake|plural }}",
                                'entitydashkey'=>  "{{ entityClass|u.afterLast('\\\\').kebab }}",
                                'entitydashkeys'=> "{{ entityClass|u.afterLast('\\\\').kebab|plural }}",
                            ]
                        )
                        ->useAttributeAsKey('key')
                        ->stringPrototype()->end()
                    ->end()

                    ->arrayNode('formType')
                        ->stringPrototype()
                            ->cannotBeEmpty()
                            ->defaultValue(
                                [
                                    "App\\Form\\{{ EntityKey }}\\{{ ActionKey }}Type",
                                    "App\\Form\\{{ EntityKey }}{{ ActionKey }}Type",
                                    "App\\Form\\{{ EntityKey }}Type",
                                ]
                            )
                        ->end()
                    ->end()

                    ->arrayNode('view')
                        ->children()
                            ->scalarNode('path')->end()
                            ->enumNode('fallback')
                                ->info('Behavior when a view template is missing.')
                                ->values(
                                    [
                                        ViewFallbackMode::BuiltIn->value,
                                        ViewFallbackMode::Error->value,
                                    ]
                                )
                                ->defaultValue(ViewFallbackMode::BuiltIn->value)
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
                            ->scalarNode('name')->end()
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
                            ->useAttributeAsKey('action')
                            ->arrayPrototype()
                                ->ignoreExtraKeys()
                                ->children()
                                    ->scalarNode('formType')->end()
                                    ->scalarNode('helper')->end()
                                    ->arrayNode('redirection')
                                        ->children()
                                            ->scalarNode('fragment')->end()
                                            ->scalarNode('route')
                                                ->isRequired()
                                            ->end()
                                            ->arrayNode('parameters')
                                                ->variablePrototype()->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                    ->arrayNode('route')
                                        ->children()
                                            ->scalarNode('path')->end()
                                            ->arrayNode('parameters')
                                                ->variablePrototype()->end()
                                            ->end()
                                            ->arrayNode('requirements')
                                                ->variablePrototype()->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                    ->arrayNode('view')
                                        ->children()
                                            ->scalarNode('path')->end()
                                            ->arrayNode('variables')
                                                ->variablePrototype()->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end()
    ;
};
