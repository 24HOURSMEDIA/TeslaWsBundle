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

class ReverseProxyCacheHandler implements PostResponseHandlerInterface, KernelRequestHandlerInterface, RequestHandlerInterface
{

    private $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     *
     * @param GetResponseEvent $event
     * @return mixed
     */
    function handleKernelRequest(GetResponseEvent $event)
    {

        // very early activation, before controller is called.
        // see if we have a cached response within it's ttl and grace period,
        // if yes pass it. If not, do nothing and proceed to the 'controller' phade
        $request = $event->getRequest();
        $cachedResponse = $this->cache->load($request);
        if ($cachedResponse) {

            $now = new \DateTime();
            if ($cachedResponse->headers->has('X-Original-Expiration')) {
                $cachedResponse->headers->set('Age', $now->getTimestamp() - $cachedResponse->getDate()->getTimestamp());
                $cachedResponse->headers->set('X-Handled-By', 'Tesla Kernel Request');
                $cachedResponse->headers->set('X-From-Cache', 1);
                $originalExpiration = new \DateTime($cachedResponse->headers->get('X-Original-Expiration'));
//                echo($originalExpiration->format('Y-m-d H:i:s'));
                if ($now < $originalExpiration) {
                    $event->setResponse($cachedResponse);

                    $event->stopPropagation();
                } else {

                }
            }

        }
    }

    /**
     * Called before controller is called
     *
     * @param Annotation[] $annotations
     * @param Response $response
     * @return Response
     */
    function handleRequest(array $annotations, FilterControllerEvent $event)
    {


        $request = $event->getRequest();
        if ($request->headers->get('X-No-Cache', 0)) {
            return;
        } else {
        }
        $cachedResponse = $this->cache->load($request);
        if ($cachedResponse) {
            $now = new \DateTime();
            $cachedResponse->headers->set('Age', $now->getTimestamp() - $cachedResponse->getDate()->getTimestamp());
            // set the old controller if the grace period is over
            $oldController = $event->getController();
            $request->attributes->set('_tesla_ws_old_controller', $oldController);
            $cachedResponse->headers->set('X-From-Cache', 2);
            $event
                ->setController(
                    function () use ($cachedResponse) {
                        return $cachedResponse;
                    });

        }
    }

    function handlePostResponse(array $annotations, PostResponseEvent $event)
    {

        $annotation = $annotations[0];
        $request = $event->getRequest();
        $response = $event->getResponse();

        $controller = $request->attributes->get('_tesla_ws_old_controller');
        if ($controller) {
            $request->headers->set('X-No-Cache', 1);
            $response = $event->getKernel()->handle($request);
            $this->cache->save($request, $response, $annotation->getGrace());
        } else {
            if ($response->headers->set('X-From-Cache', 0)) {
            } else {
                $this->cache->save($request, $response, $annotation->getGrace());
            }
        }

    }


}