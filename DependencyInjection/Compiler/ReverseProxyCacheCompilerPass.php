<?php
// $Id:  $
// $HeadURL:  $
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 01/11/13
 * Time: 14:08
 * To change this template use File | Settings | File Templates.
 */

namespace Tesla\Bundle\WsBundle\DependencyInjection\Compiler;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class ReverseProxyCacheCompilerPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        // TODO: Implement process() method.

        $config = $container->getParameter('_tesla_ws.reverse_proxy_cache.config');

        if ($config['enabled']) {
            // add the reverse proxy cache service to the handler activator
            $activatorDef = $container->findDefinition('tesla_ws.handler_activator');

            $reverseProxyDef = $container->findDefinition('tesla_ws.reverse_proxy_cache');
            $reverseProxyHandlerDef = $container->findDefinition('tesla_ws.reverse_proxy_cache_handler');
            $storageServiceDef = $container->findDefinition($config['storage_service']);
            $reverseProxyDef->replaceArgument(0, $storageServiceDef);

            $activatorDef->addMethodCall('addHandler', array('tesla_ws_reverse_proxy_cache', $reverseProxyHandlerDef));

        }


//        var_dump($config);exit;
    }


} 