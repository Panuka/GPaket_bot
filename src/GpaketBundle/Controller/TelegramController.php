<?php

namespace GpaketBundle\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use ForceUTF8\Encoding;
use GpaketBundle\Entity\Dictionary;
use GpaketBundle\Entity\Log;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

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
        return "/([\\W]|^)({$word})[\\W\\w]{0,4}$/ui";
    }

    private function addToLog($data) {
        $log = new Log();
        $dt = new \DateTime();
        $log->setData(json_encode($data));
        $log->setDate($dt);
        $this->db->persist($log);
        $this->db->flush();
    }

    public function process()
    {
        $msg = json_decode(file_get_contents('php://input'), true);
        $this->addToLog($msg);
        $msg_text = Encoding::toUTF8($msg['message']['text']);
        $txt = mb_strtolower($msg_text);
        foreach ($this->dictionary as $dic_id => $dic) {
            $preg = $this->regExp($dic->getPregKeyword());
            if ($matches = $this->isRegexpMatch($preg, $txt)) {
//                $chat_id = $msg['message']['chat']['id'];
//                $reply = $msg['message']['message_id'];
                $letter_start = mb_strpos($txt, $matches[2]) + mb_strlen($matches[2]);
                $_txt = mb_substr($txt, $letter_start);
                $_answ = $dic->getAnswers();
                $text = urlencode($_answ[array_rand($_answ)] . $_txt);
                $this->makeRequest("/sendMessage?chat_id=$chat_id&text=$text&reply_to_message_id=$reply");
            }
        }
    }

    private function isRegexpMatch($regexp, $txt)
    {
        if (preg_match($regexp, $txt, $matches) === 1)
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
        die("ok");
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
