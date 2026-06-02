<?php

declare(strict_types=1);

use Jmf\CrudEngine\Configuration\Entities\Action\Form\FormFallbackMode;
use Jmf\CrudEngine\Configuration\Entities\Action\View\ViewFallbackMode;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition): void {
    $definition->rootNode()
        ->fixXmlConfig('entity', 'entities')
        ->children()

            // Defaults for the patterns below live in ActionConfigurationResolver; the
            // values here only override them. Maps (keys/route.paths/redirection) are
            // merged with the defaults, scalars/lists (route.name/view.path/formType/
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

                    ->arrayNode('formType')
                        ->stringPrototype()->cannotBeEmpty()->end()
                    ->end()

                    ->arrayNode('form')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->enumNode('fallback')
                                ->info('Behavior when no form type is configured or discovered.')
                                ->values(
                                    [
                                        FormFallbackMode::PROVIDE->value,
                                        FormFallbackMode::FAIL->value,
                                    ]
                                )
                                ->defaultValue(FormFallbackMode::PROVIDE->value)
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
                                        ViewFallbackMode::PROVIDE->value,
                                        ViewFallbackMode::FAIL->value,
                                    ]
                                )
                                ->defaultValue(ViewFallbackMode::PROVIDE->value)
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
