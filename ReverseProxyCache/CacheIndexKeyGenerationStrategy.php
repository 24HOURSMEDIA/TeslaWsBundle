<?php
// $Id:  $
// $HeadURL:  $
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 01/11/13
 * Time: 01:34
 * To change this template use File | Settings | File Templates.
 */

namespace Tesla\Bundle\WsBundle\ReverseProxyCache;

use Symfony\Component\HttpFoundation\Request;

class CacheIndexKeyGenerationStrategy
{

    private $salt = '214qkdyf2c';

    private $debug = false;

    public function __construct($salt)
    {
        $this->salt = $salt;
    }

    public function getKey(Request $request)
    {

        $compound = array($request->getUri());
        if ($this->debug) {
            return json_encode($compound);
        }
        return sha1(
            json_encode(
                $compound
            )
        ) . '.' . $this->salt;
    }

} 