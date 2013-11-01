<?php
// $Id:  $
// $HeadURL:  $
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 01/11/13
 * Time: 01:58
 * To change this template use File | Settings | File Templates.
 */

namespace Tesla\Bundle\WsBundle\ReverseProxyCache;

use Symfony\Component\HttpFoundation\Request;

class CacheEntryKeyGenerationStrategy
{

    private $salt = '214qk22';

    private $debug = false;

    public function getKey(CacheIndex $index, Request $request)
    {
        $v = array();
        foreach ($index->getVary() as $vary) {
            $v[$vary] = $request->headers->get($vary, null);
        }
        ksort($v);
        $compound = array($request->getUri(), $v);
        if ($this->debug) {
            return json_encode($compound);
        }
        return
            sha1(
                json_encode(
                    $compound
                )
            ) . '.' . $this->salt;
    }
} 