<?php

namespace NTI\TicketBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('nti_ticket');
        $rootNode
            ->children()
                ->scalarNode('ticket_service')->end()
            ->end()
            ->children()
                ->scalarNode('documents_directory')->end()
            ->end()
            ->children()
                ->arrayNode('entities')
                    ->children()
                        ->arrayNode('resource')
                            ->children()
                                ->scalarNode('class')->end()
                                ->scalarNode('unique_field')->end()
                                ->scalarNode('email_field')->end()
                            ->end()
                        ->end() // resource node
                    ->end()

                    ->children()
                        ->arrayNode('contact')
                            ->children()
                                ->scalarNode('class')->end()
                                ->scalarNode('unique_field')->end()
                                ->scalarNode('email_field')->end()
                            ->end()
                        ->end() // contact node
                    ->end()
                ->end()
            ->end()
            ->children()
                ->arrayNode('email_connector')
                    ->children()
                        ->scalarNode('provider')->end()
                        ->scalarNode('server')->end()
                        ->scalarNode('account')->end()
                        ->scalarNode('password')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
