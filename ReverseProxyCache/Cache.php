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
use Tesla\Bundle\WsBundle\ReverseProxyCache\CacheIndexEntry;

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

    private $allowPrivate = false;

    public function __construct(CacheInterface $storage, $salt1, $salt2, $allowPrivate)
    {
        $this->indexKeyGenerationStrategy = new CacheIndexKeyGenerationStrategy($salt1);
        $this->entryKeyGenerationStrategy = new CacheEntryKeyGenerationStrategy($salt2);
        $this->storage = $storage;
        $this->allowPrivate = $allowPrivate;
    }

    private function getIndex(Request $request)
    {
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

    /**
     * 'visits' the entry and apply some headers to the response
     * @param CacheIndexEntry $entry
     */
    private function visitCacheEntry(CacheIndexEntry $entry)
    {
        $now = new \DateTime();
        $response = $entry->getResponse();
        $response->headers->set('age', $now->getTimestamp() - $response->getDate()->getTimestamp());

        /*
        $response->headers->set('X-Tesla-Rpc-Expires', $entry->getExpires()->format('c'));
        $response->headers->set('X-Tesla-Rpc-Ext-Expires', $entry->getExtendedExpiration()->format('c'));
        $response->headers->set('X-Tesla-Rpc-Is-Valid', $entry->isInvalid($now) ? 'invalid' : 'valid');
        $response->headers->set('X-Tesla-Rpc-Is-Fresh', $entry->isFresh($now) ? 'fresh' : '-');
        $response->headers->set('X-Tesla-Rpc-Is-Stale', $entry->isStale($now) ? 'stale' : '-');
        */
        return $this;
    }

    /**
     * @param Request $request
     * @return CacheIndexEntry|null
     */
    function load(Request $request)
    {
        $now = new \DateTime();
        $index = $this->getIndex($request);
        if (!$index) {
            return null;
        }
        $entryKey = $this->entryKeyGenerationStrategy->getKey($index, $request);
        $entry = $index->getEntry($entryKey);
        if ($entry instanceof CacheIndexEntry) {
            if (!$entry->isInvalid($now)) {
                $response = $this->storage->fetch($entryKey);
                if ($response instanceof Response) {
                    $entry->setResponse($response);
                    $this->visitCacheEntry($entry);
                    return $entry;
                }
            }
        }
        return null;
    }


    function save(Request $request, Response $response, $grace)
    {

        // prevent from private caching
        if (!$response->isCacheable() && !$this->allowPrivate) {
            return;
        }

        $now = new \DateTime();
        // create an index
        $index = $this->getIndex($request);
        if (!$index) {
            $key = $this->indexKeyGenerationStrategy->getKey($request);
            $index = CacheIndex::create($key)->setUri($request->getUri());
        }

        $index
            ->setVary($response->headers->get('vary', array()));

        // create an entry for the response
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
        $entry
            ->setHeaders($requestVaryHeaders)
            ->setExpires($response->getExpires())
            ->setGrace($grace)
            ->setResponse($response);;
        $index->setEntry($entry);


        $entryTtl = $entry->getTtl($now);
        $indexTtl = $index->getTtl($now);

        // clean up index and cache
        $invalidEntries = $index->cleanIndex($now);
        foreach ($invalidEntries as $invalidEntry) {
            $this->storage->delete($invalidEntry->getKey());
        }


        $this->storage->save($index->getKey(), $index, $indexTtl);
        $this->storage->save($entry->getKey(), $response, $entryTtl);

        $this->visitCacheEntry($entry);

    }


}