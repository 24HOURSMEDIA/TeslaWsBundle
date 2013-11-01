<?php
// $Id:  $
// $HeadURL:  $
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 01/11/13
 * Time: 03:39
 * To change this template use File | Settings | File Templates.
 */

namespace Tesla\Bundle\WsBundle\Handlers;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Tesla\Bundle\WsBundle\Annotation\Annotation;

interface KernelRequestHandlerInterface
{

    /**
     *
     * @param GetResponseEvent $event
     * @return mixed
     */
    function handleKernelRequest(GetResponseEvent $event);
} 