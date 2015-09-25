<?php
/**
 * Created by PhpStorm.
 * User: Panuka
 * Date: 23.09.2015
 * Time: 15:48
 */

include 'config.php';
include 'answers.php';

global $answers;
global $regexps;
global $token;

$_url = 'https://api.telegram.org/bot'.$token;

$update_id = file_get_contents('upd');
$upd = file_get_contents("$_url/getUpdates?offset=$update_id");
$data = json_decode($upd, true);


if ($data['ok'] && isset($data['result'][1])) {
    foreach ($data['result'] as $msg) {
        if ($update_id < $msg['update_id']) {
            $encode = iconv_get_encoding($msg['message']['text']);
            $txt = strtolower(iconv($encode, 'UTF-8', $msg['message']['text']));
            foreach ($regexps as $i=>$regexp)
                if (preg_match($regexp, $txt, $matches, PREG_OFFSET_CAPTURE) === 1) {
                    $chat_id = $msg['message']['chat']['id'];
                    $reply = $msg['message']['message_id'];
                    $letter_start = $matches[1][1] + mb_strlen($matches[2][0]);
                    $letter_total = strlen($txt) - $letter_start;
                    $_txt = substr($msg['message']['text'], $letter_start, $letter_total);
                    $_answ = &$answers[$i];
                    $text = urlencode($_answ[array_rand($_answ)].$_txt);
                    file_get_contents("https://api.telegram.org/bot$token/sendMessage?chat_id=$chat_id&text=$text&reply_to_message_id=$reply");
                }
        }
        $update_id = $msg['update_id'];
    }
    file_put_contents('upd', $update_id);
}