<?php

namespace Tesla\Bundle\WsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('tesla_ws');

        $this->buildReverseProxyConfig($rootNode);
        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.


        return $treeBuilder;
    }

    function buildReverseProxyConfig(ArrayNodeDefinition $parent)
    {
        $node = $parent->children()->arrayNode('reverse_proxy_cache')->info('configuration of the reverse proxy cache');
        $node->children()->booleanNode('enabled')->defaultFalse()->info('indicate whether the reverse proxy is active');
        $node->children()->scalarNode('storage_service')->defaultNull()->info('storage service id for caching. must implement methods like Doctrine\Common\Cache\Cache as CacheInterface');
        $node->children()->booleanNode('allow_private')->defaultFalse()->info('Wether the cache stores responses marked as private. Take care when setting this option so that any response header identifying a user is taken into the vary headers');

    }
}
