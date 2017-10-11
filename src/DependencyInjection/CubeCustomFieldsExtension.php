<?php

namespace CubeTools\CubeCustomFieldsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class CubeCustomFieldsExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $processed = $this->processConfiguration($configuration, $configs);
        $container->setParameter('cubetools.customfields.entities', $processed['entities']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * @inheritdoc
     */
    public function prepend(ContainerBuilder $container)
    {
        $ownConfigs = $container->getExtensionConfig('cube_custom_fields');
        $accessRightsTable = null;
        foreach ($ownConfigs as $config) {
            if (isset($config['access_rights_table'])) {
                $accessRightsTable = $config['access_rights_table'];
            }
        }
        if (null !== $accessRightsTable) { // then write user class
            $interface = 'CubeTools\CubeCustomFieldsBundle\Entity\AccessRightsTableInterface';
            $config = array('orm' => array('resolve_target_entities' => array($interface => $accessRightsTable)));
            $container->prependExtensionConfig('doctrine', $config);
        }
    }
}
