<?php
// $Id:  $
// $HeadURL:  $
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 27/10/13
 * Time: 19:55
 * To change this template use File | Settings | File Templates.
 */

namespace Tesla\Bundle\WsBundle\Handlers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Tesla\Bundle\WsBundle\Annotation\Annotation;

interface RequestHandlerInterface
{

    /**
     * Called before controller is called
     *
     * @param Annotation[] $annotations
     * @param Response $response
     * @return Response
     */
    function handleRequest(array $annotations, FilterControllerEvent $event);

} 