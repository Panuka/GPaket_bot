<?php

/**
 * Created by PhpStorm.
 * User: Panuka
 * Date: 02.12.2015
 * Time: 18:48
 */

namespace GpaketBundle\Telegram;

use GpaketBundle\Entity\Dictionary;

class Paket extends TelegramBot
{
    /**
     * @var Dictionary
     */
    private $dictionary;

    public function __construct($token, $dics=null) {
        $this->setToken($token);
        $this->dictionary = $dics;
    }

    private function regExp($word) {
        return "/([\\W]|^)($word)[!)[.:;\"'*0-9? ]*$/u";
    }

    private function convertToUtf8($text)
    {
        return mb_convert_encoding($text, 'UTF8', mb_detect_encoding($text));
    }

    public function process()
    {
        $inp = file_get_contents('php://input');
        $msg = json_decode($inp, true);

        $msg = array(
            'message' => array(
                'message_id' => 1,
                'text' => 'а вот и нет',
                'chat' => array(
                    'id' => 1
                ),
            )
        );

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
}