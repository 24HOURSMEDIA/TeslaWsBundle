<?php
// $Id:  $
// $HeadURL:  $
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 31/10/13
 * Time: 20:24
 * To change this template use File | Settings | File Templates.
 */

namespace Tesla\Bundle\WsBundle\Annotation;

/**
 * Class Vary
 * @package Tesla\Bundle\WsBundle\Annotation
 * @Annotation
 */
class Vary extends Annotation
{

    /**
     * @var string
     */
    private $value;

    /**
     * @param string $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }


    function getAliasName()
    {
        return 'tesla_ws_vary';
    }

    function allowArray()
    {
        return true;
    }

} 