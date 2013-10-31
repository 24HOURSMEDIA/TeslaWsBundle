<?php
// $Id:  $
// $HeadURL:  $
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 31/10/13
 * Time: 22:26
 * To change this template use File | Settings | File Templates.
 */

namespace Tesla\Bundle\WsBundle\Handlers;


use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Tesla\Bundle\WsBundle\Annotation\TransformHeader;

class TransformHeaderHandler implements ContainerAwareInterface, RequestHandlerInterface
{

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {

        $this->container = $container;
    }


    /**
     * Normalizes request headers by passing them through a normalization service
     * Called before controller is called
     *
     * @param TransformHeader[] $annotations
     * @param Response $response
     * @return Response
     */
    function handleRequest(array $annotations, FilterControllerEvent $event)
    {
        $headers = $event->getRequest()->headers;
        foreach ($annotations as $annot) {
            if ($headers->has($annot->getHeader())) {
                $hvalues = $headers->get($annot->getHeader());
                if (!is_array($hvalues)) {
                    $hvalues = array($hvalues);
                }
                $rvalues = array();
                if (!$this->container->has($annot->getService())) {
                    throw new \RuntimeException('Service ' . $annot->getService() . ' not found');
                }
                $transformer = $this->container->get($annot->getService());
                if (!$transformer) {
                    throw new \RuntimeException('Service not found ' . $annot->getService());
                }
                if (!method_exists($transformer, $annot->getMethod())) {
                    throw new \RuntimeException('Method in service not found ' . $annot->getService() . ':' . $annot->getMethod());
                }
                foreach ($hvalues as $h) {
                    $rvalues[] = $transformer->{$annot->getMethod()}($h);
                }
                $headers->set($annot->getHeader(), $rvalues);
            }
        }
    }


}