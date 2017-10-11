<?php

namespace CubeTools\CubeCustomFieldsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('cube_tools_cube_custom_fields');

        $rootNode
            ->children()
                ->arrayNode('entities')
                    ->useAttributeAsKey('entity')
                    ->arrayPrototype()
                        ->useAttributeAsKey('field_name')
                        ->arrayPrototype()
                            ->ignoreExtraKeys(false) // TODO delete when definition is finished
                            ->children()
                                ->enumNode('field_type')
                                    ->values(array('text', 'select', 'date', 'entity'))
                                ->end()
                                ->scalarNode('field_label')
                                    ->defaultNull()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('access_rights_table')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
