<?php

namespace GpaketBundle\Controller;

use GpaketBundle\Entity\Dictionary;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use GpaketBundle\Telegram\Paket;
use Symfony\Component\HttpFoundation\Request;

class TelegramController extends Controller
{
    public function indexAction()
    {
        $db = $this->getDoctrine()->getManager();
        $token = $db->getRepository('GpaketBundle:Config')
                    ->find('GPAKET_TELEGRAM_TOKEN')
                    ->getValue();

        $dics = $db->getRepository('GpaketBundle:Dictionary')
                   ->findAll();


        $gp = new Paket($token, $dics);
        $gp->process();
        die(json_encode(array(
            'status' => 'OK'
        )));
    }

    public function setHookAction() {
        $db = $this->getDoctrine()->getManager();
        $token = $db->getRepository('GpaketBundle:Config')
            ->find('GPAKET_TELEGRAM_TOKEN')
            ->getValue();
        $gp = new Paket($token);
        dump($gp->setHook('telegram/'));
    }
}
