<?php
// $Id:  $
// $HeadURL:  $
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 01/11/13
 * Time: 01:10
 * To change this template use File | Settings | File Templates.
 */

namespace Tesla\Bundle\WsBundle\ReverseProxyCache;


class CacheIndexEntry
{


    private $key = '';

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var \DateTime
     */
    private $expires;

    private $grace = 0;

    private $headers = array();

    static function create($key)
    {
        $e = new self();
        $e->key = $key;
        $e->created = date('c');
        $e->expires = date('c');
        return $e;
    }

    /**
     * @param array $headers
     * @return $this
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }


    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }


    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return new \DateTime($this->created);
    }

    /**
     * @param mixed $expires
     * @return $this
     */
    public function setExpires($expires)
    {
        $this->expires = $expires->format('c');
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpires()
    {
        return new \DateTime($this->expires);
    }

    /**
     * @param int $grace
     * @return $this
     */
    public function setGrace($grace)
    {
        $this->grace = $grace;
        return $this;
    }

    /**
     * @return int
     */
    public function getGrace()
    {
        return $this->grace;
    }


} 