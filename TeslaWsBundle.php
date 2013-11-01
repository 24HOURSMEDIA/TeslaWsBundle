<?php

namespace Tesla\Bundle\WsBundle;

use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tesla\Bundle\WsBundle\DependencyInjection\Compiler\ReverseProxyCacheCompilerPass;

class TeslaWsBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container); // TODO: Change the autogenerated stub

        // add compiler pass.
        $container->addCompilerPass(new ReverseProxyCacheCompilerPass());


    }


}
