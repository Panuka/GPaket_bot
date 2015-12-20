<?php

namespace GpaketBundle\Controller;

use Doctrine\Common\Persistence\ObjectManager;
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
        return "/([\\W]|^)($word)[!)[.:;\"'*0-9? ]*$/u";
    }

    private function convertToUtf8($text)
    {
        return mb_convert_encoding($text, 'UTF8', mb_detect_encoding($text));
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
//        $msg = json_decode('{"update_id":979812671,"message":{"message_id":235747,"from":{"id":90819247,"first_name":"Kirill","last_name":"Nikolaenko","username":"JIoBsTeP"},"chat":{"id":90819247,"first_name":"Kirill","last_name":"Nikolaenko","username":"JIoBsTeP","type":"private"},"date":1450594314,"text":"\u043d\u0435\u0442"}}', true);
        $this->addToLog($msg);


        $msg_text = $msg['message']['text'];
        $txt = mb_strtolower($msg_text, 'UTF8');
        foreach ($this->dictionary as $dic_id => $dic) {
            $preg = $this->regExp($dic->getPregKeyword());
            if ($matches = $this->isRegexpMatch($preg, $txt)) {
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
