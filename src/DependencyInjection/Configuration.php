<?php

namespace Jmf\CrudEngine\DependencyInjection;

use Override;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    #[Override]
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('jmf_crud_engine');

        $treeBuilder->getRootNode()
            ->fixXmlConfig('entity', 'entities')
            ->children()
                ->arrayNode('entities')
                    ->info('Properties of CRUD entities.')
                    ->useAttributeAsKey('class')
                    ->arrayPrototype()
                        ->ignoreExtraKeys()
                        ->children()
                            ->scalarNode('name')
                            ->end()
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
                                                ->scalarNode('route')
                                                    ->isRequired()
                                                ->end()
                                                ->arrayNode('parameters')
                                                    ->variablePrototype()->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                        ->arrayNode('route')
                                            ->isRequired()
                                            ->children()
                                                ->scalarNode('path')
                                                    ->isRequired()
                                                ->end()
                                                ->arrayNode('parameters')
                                                    ->variablePrototype()->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                        ->arrayNode('view')
                                            ->children()
                                                ->scalarNode('path')
                                                    ->isRequired()
                                                ->end()
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

        return $treeBuilder;
    }
}
