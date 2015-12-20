<?php

namespace GpaketBundle\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use GpaketBundle\Entity\Dictionary;
use GpaketBundle\Entity\Log;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TelegramController extends Controller
{
    /**
     * @var ObjectManager
     */
    private $db = null;
    /**
     * @var string
     */
    private $telegram_url = "https://api.telegram.org/bot";
    /**
     * @var
     */
    private $token;
    /**
     * @var Dictionary
     */
    private $dictionary;





    private function regExp($word)
    {
        return "/([\\W]|^)($word)[!)[.:;\"'*0-9? ]*$/u";
    }

    private function convertToUtf8($text)
    {
        return mb_convert_encoding($text, 'UTF8', mb_detect_encoding($text));
    }

    public function process()
    {
        $inp = file_get_contents('php://input');
        $log = new Log();
        $log->setData($inp);
        $log->setDate(new \DateTime());
        $this->db->persist($log);
        $this->db->flush();


        $msg = json_decode($inp, true);

        $msg_text = $msg['message']['text'];
        $txt = mb_strtolower($msg_text, 'UTF8');
        foreach ($this->dictionary as $dic_id => $dic) {
            $preg = $this->regExp($dic->getPregKeyword());
            if ($matches = $this->isRegexpMatch($preg, $txt)) {
                dump($matches);
                $chat_id = $msg['message']['chat']['id'];
                $reply = $msg['message']['message_id'];
                $letter_start = $matches[0][1] + $matches[1][1] + $matches[2][1] + mb_strlen($matches[2][0]);
                $letter_total = mb_strlen($txt) - $letter_start;
                $_txt = $this->convertToUtf8(
                    mb_substr(
                        $msg['message']['text'],
                        $letter_start,
                        $letter_total
                    )
                );
                $_answ = $dic->getAnswers();
                $text = urlencode($_answ[array_rand($_answ)] . $_txt);
                $this->makeRequest("/sendMessage?chat_id=$chat_id&text=$text&reply_to_message_id=$reply");
            }
        }
    }

    private function isRegexpMatch($regexp, $txt)
    {
        if (preg_match($regexp, $txt, $matches, PREG_OFFSET_CAPTURE) === 1)
            return $matches;
        else
            return false;
    }

    protected function makeRequest($request)
    {
        $url = $this->telegram_url . $this->token . $request;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        return $response;
    }

    public function setHook($file)
    {
        $url = "https://$_SERVER[SERVER_NAME]/$file";
        return $this->makeRequest("/setWebhook?url=$url");
    }

    public function indexAction()
    {
        $this->db = $this->getDoctrine()->getManager();
        $this->token = $this->db
            ->getRepository('GpaketBundle:Config')
            ->find('GPAKET_TELEGRAM_TOKEN')
            ->getValue();

        $this->dictionary = $this->db
            ->getRepository('GpaketBundle:Dictionary')
            ->findAll();

        $this->process();

//        die(json_encode(array(
//            'status' => 'OK'
//        )));
    }

    public function setHookAction()
    {
        $this->db = $this->getDoctrine()->getManager();
        $token = $this->db->getRepository('GpaketBundle:Config')
            ->find('GPAKET_TELEGRAM_TOKEN')
            ->getValue();
        $data = $this->setHook('telegram/');
    }
}
