<?php

namespace Ibrows\EasySysBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class IbrowsEasySysExtension extends Extension
{
    /**
     * @param array $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $this->registerContainerParametersRecursive($config, $container);

        $connection = $container->getDefinition("ibrows.easysys.connection");
        $connection->replaceArgument(0, new Reference($config['connection']['httpClientServiceId']));
        unset($config['connection']['httpClientServiceId']);

        foreach ($config['connection'] as $key => $value) {
            $connection->addMethodCall('set' . ucfirst($key), array($value));
        }
    }

    /**
     * @param array $configs
     * @param ContainerBuilder $container
     * @param null $prefix
     */
    protected function registerContainerParametersRecursive(array $configs, ContainerBuilder $container, $prefix = null)
    {
        if (!$prefix) {
            $prefix = $this->getAlias();
        }

        $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($configs), \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $value) {
            $path = array();
            for ($i = 0; $i <= $iterator->getDepth(); $i++) {
                $path[] = $iterator->getSubIterator($i)->key();
            }
            $key = $prefix . '.' . implode(".", $path);
            $container->setParameter($key, $value);
        }

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');
    }
}
