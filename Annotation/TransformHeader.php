<?php
// $Id:  $
// $HeadURL:  $
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 31/10/13
 * Time: 22:23
 * To change this template use File | Settings | File Templates.
 */

namespace Tesla\Bundle\WsBundle\Annotation;


/**
 * Class TransformHeader
 * @package Tesla\Bundle\WsBundle\Annotation
 * @Annotation
 */
final class TransformHeader extends Annotation
{

    private $service;

    private $method = 'normalize';

    private $header;

    /**
     * @param mixed $header
     * @return $this
     */
    public function setHeader($header)
    {
        $this->header = $header;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @param string $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param mixed $service
     * @return $this
     */
    public function setService($service)
    {
        $this->service = $service;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getService()
    {
        return $this->service;
    }

    function getAliasName()
    {
        return 'tesla_ws_transform_header';
    }

    function allowArray()
    {
        return true;
    }


} 