<?php
// $Id:  $
// $HeadURL:  $
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 01/11/13
 * Time: 01:01
 * To change this template use File | Settings | File Templates.
 */

namespace Tesla\Bundle\WsBundle\Handlers;


use Symfony\Component\Form\FormInterface;


use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Tesla\Bundle\WsBundle\Annotation\Annotation;
use Tesla\Bundle\WsBundle\ReverseProxyCache\Cache;
use Monolog\Logger;
use Tesla\Bundle\WsBundle\ReverseProxyCache\Context;

class ReverseProxyCacheHandler implements PostResponseHandlerInterface, KernelRequestHandlerInterface, RequestHandlerInterface
{

    private $cache;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(Cache $cache = null)
    {
        $this->cache = $cache;
    }

    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param GetResponseEvent $event
     * @return mixed
     */
    function handleKernelRequest(GetResponseEvent $event)
    {

        $request = $event->getRequest();
        // get the context
        /* @var Context $context */
        $context = $request->attributes->get('_tesla_ws_context');
        if (!$context) {
            $context = Context::create();
            $request->attributes->set('_tesla_ws_context', $context);
        }

        // before any controller method is invoked,
        // see if there is a fresh entry for the request.
        // if yes, serve it and cancel event propagation.
        if ($context->getAction() == $context::ACTION_SERVE_FRESH_CACHE_ENTRY_IF_POSSIBLE) {
            $cachedResponse = $this->cache->load($request);
            if ($cachedResponse) {
                if ($cachedResponse->isFresh()) {
                    $event->setResponse($cachedResponse->getResponse());
                    // $event->stopPropagation();
                    $context->actionHandled('fresh entry found and served.');
                    $context->setAction($context::ACTION_TERMINATE_STOP);
                    return;
                }
            }
            $context->actionHandled('no fresh entry found');
            $context->setAction($context::ACTION_ATTEMPT_TO_LOAD_STALE_ENTRY_IF_POSSIBLE);
        }

    }

    /**
     * @param Annotation[] $annotations
     * @param Response $response
     * @return Response
     */
    function handleRequest(array $annotations, FilterControllerEvent $event)
    {
        $request = $event->getRequest();
        // get the context
        /* @var Context $context */
        $context = $request->attributes->get('_tesla_ws_context');

        if ($context->getAction() == $context::ACTION_ATTEMPT_TO_LOAD_STALE_ENTRY_IF_POSSIBLE) {
            $cachedResponse = $this->cache->load($request);
            if ($cachedResponse) {
                if ($cachedResponse->isStale()) {
                    // grace period is over. do serve the cached response, but
                    // refresh the entry on kernel termination
                    $oldController = $event->getController();
                    $request->attributes->set('_tesla_ws_old_controller', $oldController);
                    $response = $cachedResponse->getResponse();
                    $event->setController(function () use ($response) {
                        return $response;
                    });
                    //$event->stopPropagation();
                    // stale entry has been served, attempt to refresh the entry on termination
                    $context->actionHandled('stale entry served.');
                    $context->setAction($context::ACTION_REFRESH_ENTRY_ON_TERMINATE);
                    return;
                }
            }
            $context->actionHandled('no stale entry found');
            $context->setAction($context::ACTION_PROCESS_CONTROLLER_REQUEST);
        }
        if ($context->getAction() == $context::ACTION_PROCESS_CONTROLLER_REQUEST) {
            $context->actionHandled('instruct to save on terminate');
            $context->setAction($context::ACTION_SAVE_ENTRY_ON_TERMINATE);
        } else {
            $event->stopPropagation();
        }

    }

    function handlePostResponse(array $annotations, PostResponseEvent $event)
    {
        $request = $event->getRequest();
        // get the context
        /* @var Context $context */
        $context = $request->attributes->get('_tesla_ws_context');

        // we arrive here when either a stale response has been served,
        // or a new response created by the controller. In the latter case,
        // save to the cache.
        $annotation = $annotations[0];
        $response = $event->getResponse();


        if ($context->getAction() == $context::ACTION_REFRESH_ENTRY_ON_TERMINATE) {
            $context->actionHandled('retrieve fresh response');
            $context->setAction($context::ACTION_PROCESS_CONTROLLER_REQUEST);
            $response = $event->getKernel()->handle($request);
            $this->cache->save($request, $response, $annotation->getGrace());
            $context->setAction($context::ACTION_SAVE_ENTRY_ON_TERMINATE);
        }
        if ($context->getAction() == $context::ACTION_SAVE_ENTRY_ON_TERMINATE) {
            $context->actionHandled('saved response');
            $this->cache->save($request, $response, $annotation->getGrace());
        }
        $this->logger->addInfo('TESLA REVERSE PROXY', array($context));
    }


}