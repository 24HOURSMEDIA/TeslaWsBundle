<?php
// $Id:  $
// $HeadURL:  $
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 01/11/13
 * Time: 01:02
 * To change this template use File | Settings | File Templates.
 */

namespace Tesla\Bundle\WsBundle\Handlers;

use Symfony\Component\HttpKernel\Event\PostResponseEvent;

interface PostResponseHandlerInterface
{

    function handlePostResponse(array $annotations, PostResponseEvent $event);
}