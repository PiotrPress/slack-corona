<?php

namespace PiotrPress\Slack\Corona;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Config implements ConfigurationInterface {
    public function getConfigTreeBuilder() {
        $treeBuilder = new TreeBuilder( 'corona' );

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode( 'slack' )
                    ->children()
                        ->scalarNode( 'token' )->isRequired()->end()
                        ->scalarNode( 'command' )->defaultValue( 'corona' )->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
	}
}