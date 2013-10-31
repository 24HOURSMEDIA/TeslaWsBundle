<?php
// $Id:  $
// $HeadURL:  $
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 27/10/13
 * Time: 20:59
 * To change this template use File | Settings | File Templates.
 */

namespace Tesla\Bundle\WsBundle\Handlers;


use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Tesla\Bundle\WsBundle\Annotation\Annotation;

interface ExceptionHandlerInterface
{
    /**
     * Called when the controller does throws an exception
     * Convert this to a response
     *
     * @param Annotation[] $annotations
     * @param GetResponseForControllerResultEvent $event
     * @return mixed
     */
    function handleException(array $annotations, GetResponseForExceptionEvent $event);
} 