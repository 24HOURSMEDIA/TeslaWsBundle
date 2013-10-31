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
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Tesla\Bundle\WsBundle\Annotation as WS;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\Common\Annotations\Reader;
use Tesla\Bundle\WsBundle\Handlers\ExceptionHandlerInterface;
use Tesla\Bundle\WsBundle\Handlers\JsonHandler;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Tesla\Bundle\WsBundle\Handlers\RequestHandlerInterface;
use Tesla\Bundle\WsBundle\Handlers\ResponseHandlerInterface;
use Tesla\Bundle\WsBundle\Handlers\ViewHandlerInterface;
use Doctrine\Common\Annotations\FileCacheReader;

/**
 * Class ControllerEventListener
 * @package Tesla\Bundle\WsBundle\Listener
 * @DI\Service
 */
class ControllerEventListener {

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
     * @DI\Inject("tesla_ws.json_handler")
     * @var JsonHandler
     */
    public $jsonHandler;

    private $cacheDir;

    private $debug = false;

    /**
     * @DI\InjectParams({
     *     "cacheDir" = @DI\Inject("%tesla_ws.reader_cache_path%"),
     *     "reader" = @DI\Inject("annotation_reader"),
     *     "env" = @DI\Inject("%KERNEL.ENVIRONMENT%")
     * })
     */
    public function __construct($cacheDir, $reader, $env) {
        if (!is_dir($cacheDir)) {
            $this->createDir($cacheDir);
        }
        if (!is_writable($cacheDir)) {
            throw new InvalidArgumentException(sprintf('The cache directory "%s" is not writable.', $dir));
        }
        $this->debug = stristr($env, 'dev');
        $this->cacheDir = $cacheDir;
        $this->reader = new FileCacheReader($reader, $this->cacheDir, $this->debug);
    }

    private function createDir($dir)
    {
        if (is_dir($dir)) {
            return;
        }

        if (false === @mkdir($dir, 0777, true)) {
            throw new RuntimeException(sprintf('Could not create directory "%s".', $dir));
        }
    }

    protected function getHandler(WS\Annotation $annotation) {
        switch ($annotation->getAliasName()) {
            case  'tesla_ws_json':
                return $this->jsonHandler;
            break;
        }
        return null;
    }

    /**
     * @DI\Observe("kernel.controller", priority = -1)
     * @param FilterControllerEvent $event
     */
    function onKernelController(FilterControllerEvent $event) {

        if (!is_array($controller = $event->getController())) {
            return;
        }
        $className = class_exists('Doctrine\Common\Util\ClassUtils') ? ClassUtils::getClass($controller[0]) : get_class($controller[0]);
        $object = new \ReflectionClass($className);
        $method = $object->getMethod($controller[1]);
        $responseChain = array();
        foreach ($this->reader->getMethodAnnotations($method) as $annotation) {
            if ($annotation instanceof WS\Annotation) {
                $handler = $this->getHandler($annotation);
                if ($handler && $handler instanceof RequestHandlerInterface) {
                    $handler->handleRequest($annotation, $event);
                }
                if ($handler) {
                    $responseChain[] = $annotation;
                }

            }
        }

        $event->getRequest()->attributes->set('_tesla_ws_chain', $responseChain);
    }

    /**
     * @DI\Observe("kernel.response", priority = -1)
     * @param FilterResponseEvent $event
     */
    function onKernelResponse(FilterResponseEvent $event)
    {

        $chain = $event->getRequest()->attributes->get('_tesla_ws_chain', array());
        foreach ($chain as $annotation) {
            $handler = $this->getHandler($annotation);
            if ($handler && $handler instanceof ResponseHandlerInterface) {

                $handler->handleResponse($annotation, $event);
            }
        }
    }

    /**
     * @DI\Observe("kernel.view", priority = -1)
     */
    function onKernelView(GetResponseForControllerResultEvent $event) {

        $chain = $event->getRequest()->attributes->get('_tesla_ws_chain', array());
        foreach ($chain as $annotation) {
            $handler = $this->getHandler($annotation);
            if ($handler && $handler instanceof ViewHandlerInterface) {

                $handler->handleView($annotation, $event);
            }
        }
    }

    /**
     * @DI\Observe("kernel.exception", priority = -1)
     */
    function onKernelException(GetResponseForExceptionEvent $event) {

        $chain = $event->getRequest()->attributes->get('_tesla_ws_chain', array());
        foreach ($chain as $annotation) {
            $handler = $this->getHandler($annotation);
            if ($handler && $handler instanceof ExceptionHandlerInterface) {

                $handler->handleException($annotation, $event);
            }
        }
    }

} 