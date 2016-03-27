<?php

namespace GpaketBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    // главная
    public function indexAction() {
        return $this->render('GpaketBundle:Default:index.html.twig');
    }
}