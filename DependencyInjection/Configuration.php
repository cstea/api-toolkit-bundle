<?php declare(strict_types = 1);

namespace Cstea\ApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements \Symfony\Component\Config\Definition\ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('cstea_api');

        $rootNode
            ->children()
            ->booleanNode('handle_exceptions')->end()
            ->scalarNode('route_match')->end()
            ->arrayNode('response_headers')
            ->ignoreExtraKeys(false)
            ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
