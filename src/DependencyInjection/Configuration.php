<?php

namespace Jmf\CrudEngine\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('jmf_crud_engine');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('entities')
                    ->info('Properties of CRUD entities.')
                    ->useAttributeAsKey('class')
                    ->variablePrototype()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
