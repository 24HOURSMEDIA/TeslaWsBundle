<?php
// $Id:  $
// $HeadURL:  $
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 31/10/13
 * Time: 20:45
 * To change this template use File | Settings | File Templates.
 */

namespace Tesla\Bundle\WsBundle\Handlers;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Tesla\Bundle\WsBundle\Annotation\Annotation;
use Tesla\Bundle\WsBundle\Annotation\Vary;

/**
 * Class VaryHandler
 * @package Tesla\Bundle\WsBundle\Handlers
 * @DI\Service("tesla_ws.vary_handler")
 */
class VaryHandler implements RequestHandlerInterface, ResponseHandlerInterface
{

    const VARY_ATTRIBUTE_NAME = '_tesla_ws_vary';

    /**
     * Called before controller is called
     *
     * @param Annotation[] $annotations
     * @param Response $response
     * @return Response
     */
    function handleRequest(array $annotations, FilterControllerEvent $event)
    {
        // collect the vary headers early and store them in request attributes
        // this way other response handlers will have access to them if needed
        $v = array();
        foreach ($annotations as $vary) {
            $v[] = $name = implode('-', array_map('ucfirst', explode('-', $vary->getValue())));
        }

        $event->getRequest()->attributes->set(self::VARY_ATTRIBUTE_NAME, array_unique($v));
    }


    /**
     * Called when a controller returns a response object
     * Modify the response here..
     *
     * @param Vary[] $annotations
     * @param Response $response
     * @return Response
     */
    function handleResponse(array $annotations, FilterResponseEvent $event)
    {

        // get the vary headers from the attributes
        $vary = $event->getRequest()->attributes->get(self::VARY_ATTRIBUTE_NAME, array());
        if (count($vary)) {
            $event->getResponse()->setVary($vary);
        }
    }


}