<?php

/**
 * This file is part of TestWebService package.
 *
 * @author Jean-Baptiste Nahan <macintoshplus@users.noreply.github.com>
 * @copyright 2016-2019 - Jean-Baptiste Nahan
 * @license MIT
 */

namespace Mactronique\TestWs\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class MainConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('main');

        // ... add node definitions to the root of the tree
        $rootNode
            ->children()
            ->arrayNode('webservices')
            ->useAttributeAsKey('name')
            ->prototype('array')
            ->children()
            ->scalarNode('class')->end()
            ->arrayNode('config')
            ->prototype('variable')->end()
            ->end()
            ->arrayNode('storage')
            ->children()
            ->scalarNode('type')->end()
            ->arrayNode('config')
            ->prototype('variable')->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
