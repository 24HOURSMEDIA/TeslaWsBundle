<?php
// $Id:  $
// $HeadURL:  $
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 27/10/13
 * Time: 19:28
 * To change this template use File | Settings | File Templates.
 */

namespace Tesla\Bundle\WsBundle\Listener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Tesla\Bundle\WsBundle\Annotation as WS;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\Common\Annotations\Reader;
use Tesla\Bundle\WsBundle\Handlers\ExceptionHandlerInterface;

use Tesla\Bundle\WsBundle\Handlers\PostResponseHandlerInterface;
use Tesla\Bundle\WsBundle\Handlers\RequestHandlerInterface;
use Tesla\Bundle\WsBundle\Handlers\ResponseHandlerInterface;
use Tesla\Bundle\WsBundle\Handlers\ViewHandlerInterface;
use Tesla\Bundle\WsBundle\Handlers\KernelRequestHandlerInterface;

use Doctrine\Common\Annotations\FileCacheReader;


/**
 * Class HandlerActivator
 * Mediator that activates annotation handlers
 * Achieves performonce optimization because annotations do not have to be read over and over again
 *
 * @package Tesla\Bundle\WsBundle\Listener
 */
class HandlerActivator
{

    /**
     *
     * @var Reader
     */
    public $reader;

    /**
     * @DI\Inject("logger")
     */
    public $logger;

    /**
     * @var array
     */
    public $handlers = array();

    private $cacheDir;

    private $debug = false;


    /**
     * @param $cacheDir
     * @param Reader $reader
     * @param $env
     */
    public function __construct($cacheDir, Reader $reader, $env)
    {
        if (!is_dir($cacheDir)) {
            $this->createDir($cacheDir);
        }
        if (!is_writable($cacheDir)) {
            throw new \RuntimeException(sprintf('The cache directory "%s" is not writable.', $dir));
        }
        $this->debug = stristr($env, 'dev');
        $this->cacheDir = $cacheDir;
        $this->reader = new FileCacheReader($reader, $this->cacheDir, $this->debug);
    }


    public function addHandler($alias, $service)
    {

        $this->handlers[trim($alias)] = $service;
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    private function createDir($dir)
    {
        if (is_dir($dir)) {
            return;
        }

        if (false === @mkdir($dir, 0777, true)) {
            throw new \RuntimeException(sprintf('Could not create directory "%s".', $dir));
        }
    }

    private function getHandler(WS\Annotation $annotation)
    {
        if (!isset($this->handlers[$annotation->getAliasName()])) {
            // throw new \RuntimeException('Handler ' . $annotation->getAliasName() . ' not found for annotation ' . get_class($annotation));
            return;
        }
        return $this->handlers[$annotation->getAliasName()];

    }

    function onKernelRequest(GetResponseEvent $event)
    {
        foreach ($this->handlers as $handler) {
            if ($handler instanceof KernelRequestHandlerInterface) {
                $handler->handleKernelRequest($event);
            }
        }
    }

    function onKernelController(FilterControllerEvent $event)
    {


        if (!is_array($controller = $event->getController())) {
            return;
        }
        $className = class_exists('Doctrine\Common\Util\ClassUtils') ? ClassUtils::getClass($controller[0]) : get_class($controller[0]);
        $object = new \ReflectionClass($className);
        $method = $object->getMethod($controller[1]);
        $responseChain = array();

        foreach ($this->reader->getMethodAnnotations($method) as $annotation) {
            if ($annotation instanceof WS\Annotation) {
                $alias = $annotation->getAliasName();

                if (!isset($responseChain[$alias])) {
                    $responseChain[$alias] = array($annotation);
                } else {
                    $responseChain[$alias][] = $annotation;
                }
                if (!isset($handlers[$alias])) {
                    $handler = $this->getHandler($annotation);
                    if ($handler) {
                        $handlers[$alias] = $handler;
                    } else {

                    }

                }
            }
        }
        //var_dump($handlers);exit;
        // echo '<pre>'; var_dump($annotationHandlers);
        // resort the response chain so that they correspond to the order in which 'addHandlers' were added
        $sortedHandlers = array();
        foreach (array_keys($this->handlers) as $alias) {
            if (isset($handlers[$alias])) {
                $sortedHandlers[$alias] = $handlers[$alias];
            }
        }

        $event->getRequest()->attributes->set('_tesla_ws_chain', $responseChain);
        $event->getRequest()->attributes->set('_tesla_ws_handlers', $sortedHandlers);
        $annotations = $event->getRequest()->attributes->get('_tesla_ws_chain');
        foreach ($event->getRequest()->attributes->get('_tesla_ws_handlers') as $alias => $handler) {
            if ($handler instanceof RequestHandlerInterface) {
                $handler->handleRequest($annotations[$alias], $event);
            }
        }
    }


    function onKernelResponse(FilterResponseEvent $event)
    {
        $annotations = $event->getRequest()->attributes->get('_tesla_ws_chain');
        foreach ($event->getRequest()->attributes->get('_tesla_ws_handlers', array()) as $alias => $handler) {
            if ($handler instanceof ResponseHandlerInterface) {
                $handler->handleResponse($annotations[$alias], $event);
            }
        }
    }


    function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $annotations = $event->getRequest()->attributes->get('_tesla_ws_chain');
        foreach ($event->getRequest()->attributes->get('_tesla_ws_handlers', array()) as $alias => $handler) {
            if ($handler instanceof ViewHandlerInterface) {
                $handler->handleView($annotations[$alias], $event);
            }
        }
    }


    function onKernelException(GetResponseForExceptionEvent $event)
    {
        $annotations = $event->getRequest()->attributes->get('_tesla_ws_chain');
        foreach ($event->getRequest()->attributes->get('_tesla_ws_handlers', array()) as $alias => $handler) {
            if ($handler instanceof ExceptionHandlerInterface) {
                $handler->handleException($annotations[$alias], $event);
            }
        }
    }

    function onKernelTerminate(PostResponseEvent $event)
    {

        $annotations = $event->getRequest()->attributes->get('_tesla_ws_chain');
        foreach ($event->getRequest()->attributes->get('_tesla_ws_handlers', array()) as $alias => $handler) {

            if ($handler instanceof PostResponseHandlerInterface) {

                $handler->handlePostResponse($annotations[$alias], $event);
            }
        }
    }

} 