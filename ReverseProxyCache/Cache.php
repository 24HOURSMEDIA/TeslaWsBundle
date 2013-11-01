<?php
// $Id:  $
// $HeadURL:  $
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 01/11/13
 * Time: 01:39
 * To change this template use File | Settings | File Templates.
 */

namespace Tesla\Bundle\WsBundle\ReverseProxyCache;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Cache\Cache as CacheInterface;

class Cache
{

    private $indexKeyGenerationStrategy;

    /**
     * @var CacheIndex[]
     */
    private $indices = array();

    /**
     * @var CacheInterface
     */
    private $storage;

    public function __construct(CacheInterface $storage)
    {
        $this->indexKeyGenerationStrategy = new CacheIndexKeyGenerationStrategy();
        $this->entryKeyGenerationStrategy = new CacheEntryKeyGenerationStrategy();
        $this->storage = $storage;
    }

    private function getIndex(Request $request)
    {
        $now = new \DateTime();
        $key = $this->indexKeyGenerationStrategy->getKey($request);
        // attempt to load the index from local cache, then from storage
        if (isset($this->indices[$key])) {
            return $this->indices[$key];
        }
        // load from storage
        $index = $this->storage->fetch($key);
        if (is_object($index) && $index instanceof CacheIndex) {
            return $this->indices[$key] = $index;
        }

        return null;
    }

    function load(Request $request)
    {
        $now = new \DateTime();
        $index = $this->getIndex($request);
        if (!$index) {
            return null;
        }
        $entryKey = $this->entryKeyGenerationStrategy->getKey($index, $request);
        $entry = $index->getEntry($entryKey);
        if (is_object($entry)) {
            // check expiration!
            $response = $this->storage->fetch($entryKey);
            if ($response instanceof Response) {
                if ($now < $response->getExpires()) {
                    return $response;
                } else {
                    //        die('expired');
                }
            }
        }
        return null;
    }


    function save(Request $request, Response $response, $grace)
    {
        $now = new \DateTime();

        $index = $this->getIndex($request);
        if (!$index) {
            $key = $this->indexKeyGenerationStrategy->getKey($request);
            $index = CacheIndex::create($key)->setUri($request->getUri());
        }
        $indexExpires = new \DateTime('+20 minute');
        $index
            ->setVary($response->headers->get('vary', array()))
            ->setExpires($indexExpires);

        //   echo "\n\n\n";var_dump($index);
        $entryKey = $this->entryKeyGenerationStrategy->getKey($index, $request);
        $entry = $index->getEntry($entryKey);
        if (!$entry) {
            $entry = CacheIndexEntry::create($entryKey);
        }
        // fill the entry with most recent relevant information
        $requestVaryHeaders = array();
        foreach ($index->getVary() as $h) {
            $requestVaryHeaders[$h] = $request->headers->get($h, null);
        }
        $entry->setHeaders($requestVaryHeaders);
        $entry->setExpires($response->getExpires());
        $entry->setGrace($grace);
        $index->setEntry($entry);

        $interval = \DateInterval::createFromDateString($entry->getGrace());
        $extendedExpiration = clone($entry->getExpires());
        $extendedExpiration->add($interval);
        $response->headers->set('X-Original-Expiration', $response->getExpires()->format('D, d M Y H:i:s \G\M\T'));
        $response->headers->set('X-Grace', $entry->getGrace());
        $response->setExpires($extendedExpiration);

        $ttl = $index->getExpires()->getTimestamp() - $now->getTimestamp();
        $this->storage->save($index->getKey(), $index, $ttl);
        $this->storage->save($entry->getKey(), $response);


    }


}