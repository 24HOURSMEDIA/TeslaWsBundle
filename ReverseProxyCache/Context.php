<?php
// $Id:  $
// $HeadURL:  $
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 01/11/13
 * Time: 12:05
 * To change this template use File | Settings | File Templates.
 */

namespace Tesla\Bundle\WsBundle\ReverseProxyCache;


class Context
{

    public $sessId;


    public $nextAction;

    public $actionLog = array();

    const ACTION_SERVE_FRESH_CACHE_ENTRY_IF_POSSIBLE = 'serve fresh cache entry if possible';
    const ACTION_ATTEMPT_TO_LOAD_STALE_ENTRY_IF_POSSIBLE = 'attempt to load a stale cache entry if possible';
    const ACTION_REFRESH_ENTRY_ON_TERMINATE = 'refresh the entry on kernel termination';
    const ACTION_SAVE_ENTRY_ON_TERMINATE = 'save the entry on terminate';
    const ACTION_PROCESS_CONTROLLER_REQUEST = 'process the normal controller request';
    const ACTION_TERMINATE_STOP = 'stop kernel termination and do nothing';

    static function create()
    {
        $c = new self();
        $c->sessId = uniqid();
        $c->nextAction = self::ACTION_SERVE_FRESH_CACHE_ENTRY_IF_POSSIBLE;
        return $c;
    }

    public function setAction($action)
    {
        $this->actionLog[] = 'NEXT ACTION: ' . $action;
        $this->nextAction = $action;
    }

    public function getAction()
    {
        return $this->nextAction;
    }

    public function actionHandled($msg = '')
    {
        $this->actionLog[] = 'ACTION HANDLED: ' . $this->getAction() . '[' . $msg . ']';
        $this->nextAction = '';
    }

} 