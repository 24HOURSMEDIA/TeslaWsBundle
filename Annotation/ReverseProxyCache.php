<?php
// $Id:  $
// $HeadURL:  $
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 01/11/13
 * Time: 01:00
 * To change this template use File | Settings | File Templates.
 */

namespace Tesla\Bundle\WsBundle\Annotation;

/**
 * Class ReverseProxyCache
 * @package Tesla\Bundle\WsBundle\Annotation
 * @Annotation
 */
class ReverseProxyCache extends Annotation
{

    private $grace = '1 second';

    function getAliasName()
    {
        return 'tesla_ws_reverse_proxy_cache';
    }

    function allowArray()
    {
        return false;
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