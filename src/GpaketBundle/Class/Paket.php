<?php

/**
 * Created by PhpStorm.
 * User: Panuka
 * Date: 02.12.2015
 * Time: 18:48
 */

namespace Telegram;

class Paket extends TelegramBot
{
    private $answers = [];
    private $regexps;
    private $root;

    public function __construct($dir)
    {
        $this->root = $dir . DIRECTORY_SEPARATOR;
        require_once $this->file('config.php');
        $confDefined = isset ($token) && isset ($answers) && isset ($regexps);
        if (!$confDefined)
            die('Config file not found or broken');
        $this->setToken($token);

        $this->answers = $answers;
        foreach ($regexps as $regexp)
            $this->regexps[] = $this->regExp($regexp);
    }

    private function regExp($word)
    {
        return '/([\W]|^)(' . preg_quote($word) . ')[!)[.:;\"\'*0-9? ]*$/u';
    }

    private function file($relative_path)
    {
        $path = $this->root . $relative_path;
        return $path;
    }

    private function convertToUtf8($text)
    {
        return mb_convert_encoding($text, 'UTF8', mb_detect_encoding($text));
    }

    public function process()
    {
        $inp = file_get_contents('php://input');
        $msg = json_decode($inp, true);
        $msg_text = $msg['message']['text'];
        $txt = mb_strtolower($msg_text, 'UTF8');
        foreach ($this->regexps as $i => $regexp) {
            if ($matches = $this->isRegexpMatch($regexp, $txt)) {
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
                $_answ = &$this->answers[$i];
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