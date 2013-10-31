<?php

namespace Tesla\Bundle\WsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('TeslaWsBundle:Default:index.html.twig', array('name' => $name));
    }
}
