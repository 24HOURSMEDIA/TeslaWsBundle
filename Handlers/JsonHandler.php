<?php
// $Id:  $
// $HeadURL:  $
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 27/10/13
 * Time: 19:40
 * To change this template use File | Settings | File Templates.
 */

namespace Tesla\Bundle\WsBundle\Handlers;


use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Tesla\Bundle\WsBundle\Annotation\Annotation;
use Tesla\Bundle\ClientBundle\Client\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class JsonHandler
 * @package Tesla\Bundle\WsBundle\Handlers
 * @DI\Service("tesla_ws.json_handler")
 */
class JsonHandler implements ViewHandlerInterface, ExceptionHandlerInterface
{


    private $executionEnvironment = '';

    public function __construct($env)
    {
        $this->executionEnvironment = $env;
    }

    /**
     * Called when the controller does throws an exception
     * Convert this to a response
     *
     * @param Annotation[] $annotations
     * @param GetResponseForControllerResultEvent $event
     * @return mixed
     */
    function handleException(array $annotations, GetResponseForExceptionEvent $event)
    {

        $annotation = $annotations[0];
        $exception = $event->getException();
        if ($exception  instanceof HttpException) {
            $error = new \stdClass();
            $error->code = $exception->getStatusCode();
            $error->message = $exception->getMessage();
            $error->errors = array();

        } elseif ($exception  instanceof \Exception) {
            $error = new \stdClass();
            $error->code = 500;
            $error->message = 'Application error';
            $error->errors = array();
        }
        if ($this->executionEnvironment == 'dev') {
            $error->errors[] = $exception->getMessage();
            $error->errors[] = $exception->getFile();
            $error->errors[] = $exception->getLine();
            $error->errors[] = $exception->getTrace();
        }
        $event->setResponse(
            Response::create(
                json_encode($error),
                $error->code,
                array('content-type' => 'application/json')
            )
        );
    }

    /**
     * Called when the controller does not return a response object but data
     * Convert this to a response
     *
     * @param Annotation[] $annotations
     * @param GetResponseForControllerResultEvent $event
     * @return mixed
     */
    function handleView(array $annotations, GetResponseForControllerResultEvent $event)
    {
        $annotation = $annotations[0];
        $event->setResponse(
            Response::create(
                json_encode($event->getControllerResult()),
                200,
                array('content-type' => 'application/json')
            )
        );
    }


}