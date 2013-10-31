<?php
// $Id:  $
// $HeadURL:  $
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 27/10/13
 * Time: 20:08
 * To change this template use File | Settings | File Templates.
 */

namespace Tesla\Bundle\WsBundle\Handlers;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Tesla\Bundle\WsBundle\Annotation\Annotation;

interface ViewHandlerInterface {

    /**
     * Called when the controller does not return a response object but data
     * Convert this to a response
     *
     * @param Annotation $annotation
     * @param GetResponseForControllerResultEvent $event
     * @return mixed
     */
    function handleView(Annotation $annotation, GetResponseForControllerResultEvent $event);
}