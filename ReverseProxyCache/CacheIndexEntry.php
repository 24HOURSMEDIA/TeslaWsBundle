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

use Symfony\Component\HttpFoundation\Response;

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

    /**
     * @var Response
     */
    private $response;

    public function __sleep()
    {
        return array('key', 'created', 'expires', 'grace', 'headers');
    }

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
     * Returns the extended expiration time (including the grace time)
     * @return \DateTime
     */
    public function getExtendedExpiration()
    {
        $interval = \DateInterval::createFromDateString($this->getGrace());
        $extendedExpiration = clone($this->getExpires());
        $extendedExpiration->add($interval);
        return $extendedExpiration;
    }

    /**
     * @param string $grace
     * @return $this
     */
    public function setGrace($grace)
    {
        $this->grace = $grace;
        return $this;
    }

    /**
     * @return string
     */
    public function getGrace()
    {
        return $this->grace;
    }

    /**
     * Returns the response
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @return $this
     */
    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * Gets the response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    public function isFresh($time = null)
    {
        if (!$time) {
            $time = new \DateTime();
        }
        return $time < $this->getExpires();
    }

    public function isStale($time = null)
    {
        if (!$time) {
            $time = new \DateTime();
        }
        return $time > $this->getExpires();
    }


    public function isInvalid($time = null)
    {
        if (!$time) {
            $time = new \DateTime();
        }
        return $time > $this->getExtendedExpiration();
    }

    public function getTtl($time = null)
    {
        if (!$time) {
            $time = new \DateTime();
        }
        $ttl = $this->getExtendedExpiration($time)->getTimestamp() - $time->getTimestamp();
        return $ttl < 0 ? 0 : 0;
    }


}