<?php
// $Id:  $
// $HeadURL:  $
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 27/10/13
 * Time: 19:24
 * To change this template use File | Settings | File Templates.
 */

namespace Tesla\Bundle\WsBundle\Annotation;


/**
 * Class Json
 * Serializes the output of the controller as JSON
 * and handles errors as json errors
 *
 * @package Tesla\Bundle\WsBundle\Annotation
 * @Annotation
 */
final class Json extends Annotation
{

    /**
     * Returns the alias name for an annotated configuration.
     *
     * @return string
     */
    function getAliasName()
    {
        return 'tesla_ws_json';
    }

    /**
     * Returns whether multiple annotations of this type are allowed
     *
     * @return Boolean
     */
    function allowArray()
    {
        return false;
    }


} 