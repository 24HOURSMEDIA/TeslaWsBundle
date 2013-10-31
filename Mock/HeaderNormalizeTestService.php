<?php
// $Id:  $
// $HeadURL:  $
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 31/10/13
 * Time: 22:30
 * To change this template use File | Settings | File Templates.
 */

namespace Tesla\Bundle\WsBundle\Mock;


class HeaderNormalizeTestService
{

    public function normalize($value)
    {
        return 'newvalue';
    }
} 