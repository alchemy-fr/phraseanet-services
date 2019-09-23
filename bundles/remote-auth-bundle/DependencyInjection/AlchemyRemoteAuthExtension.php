<?php

namespace Alchemy\RemoteAuthBundle\DependencyInjection;

use Alchemy\RemoteAuthBundle\Security\LoginFormAuthenticator;
use Alchemy\RemoteAuthBundle\Security\RemoteUserProvider;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class AlchemyRemoteAuthExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        if ($container->getParameter("kernel.environment") === 'test') {
            $loader->load('services_test.yaml');
        }

        $def = $container->getDefinition(LoginFormAuthenticator::class);
        $def->setArgument('$routeName', $config['login_form']['route_name']);
        $def->setArgument('$defaultTargetPath', $config['login_form']['default_target_path']);
    }
}