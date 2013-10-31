<?php
// $Id:  $
// $HeadURL:  $
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 27/10/13
 * Time: 19:47
 * To change this template use File | Settings | File Templates.
 */

namespace Tesla\Bundle\WsBundle\Handlers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Tesla\Bundle\WsBundle\Annotation\Annotation;

interface ResponseHandlerInterface
{

    /**
     * Called when a controller returns a response object
     * Modify the response here..
     *
     * @param Annotation[] $annotations
     * @param Response $response
     * @return Response
     */
    function handleResponse(array $annotations, FilterResponseEvent $event);
} 