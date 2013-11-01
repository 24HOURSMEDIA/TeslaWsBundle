<?php
// $Id:  $
// $HeadURL:  $
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 01/11/13
 * Time: 01:09
 * To change this template use File | Settings | File Templates.
 */

namespace Tesla\Bundle\WsBundle\ReverseProxyCache;


class CacheIndex
{

    private $key = '';

    /**
     * @var string
     */
    private $uri = '';

    /**
     * @var array
     */
    private $vary;


    /**
     * @var CacheIndexEntry[]
     */
    private $entries;

    /**
     * @var string
     */
    private $created;

    static function create($key)
    {
        $r = new self();
        $r->expires = date('c');
        $r->created = date('c');
        $r->key = $key;
        $r->vary = array();
        $r->entries = array();
        return $r;
    }

    /**
     * @param \Tesla\Bundle\WsBundle\ReverseProxyCache\CacheIndexEntry[] $entries
     * @return $this
     */
    function setEntries($entries)
    {
        $this->entries = $entries;
        return $this;
    }

    /**
     * @return \Tesla\Bundle\WsBundle\ReverseProxyCache\CacheIndexEntry[]
     */
    function getEntries()
    {
        return $this->entries;
    }

    /**
     * @param string $uri
     * @return $this
     */
    function setUri($uri)
    {
        $this->uri = $uri;
        return $this;
    }

    /**
     * @return string
     */
    function getUri()
    {
        return $this->uri;
    }

    /**
     * @param array $vary
     * @return $this
     */
    function setVary($vary)
    {
        if (!is_array($vary)) {
            $vary = array($vary);
        }
        if ($this->vary != $vary) {
            $this->entries = array();
        }

        $this->vary = $vary;
        return $this;
    }

    /**
     * @return array
     */
    function getVary()
    {
        return $this->vary;
    }


    public function getEntry($key)
    {
        if (isset($this->entries[$key])) {
            return $this->entries[$key];
        }
        return null;
    }

    public function setEntry(CacheIndexEntry $entry)
    {

        $this->entries[$entry->getKey()] = $entry;
    }


    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return new \DateTime($this->created);
    }


    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    public function getTtl($time)
    {
        if (!$time) {
            $time = new \DateTime();
        }
        $ttl = 0;
        foreach ($this->entries as $entry) {
            $ttl = max($ttl, $entry->getTtl($time));
        }
        return $ttl < 0 ? 0 : 0;
    }

    /**
     * Removes expired entries from the index saving some space in the cache
     * returns array with expired entries so they can be removed from the cache
     * @param $time
     */
    public function cleanIndex($time)
    {
        if (!$time) {
            $time = new \DateTime();
        }
        $ret = array();
        foreach ($this->entries as $k => $v) {
            if ($v->isInvalid()) {
                $ret[] = $v;
                unset($this->entries[$k]);
            }
        }
        return $ret;
    }

} 