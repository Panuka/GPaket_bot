<?php
/**
 * Created by PhpStorm.
 * User: Panuka
 * Date: 23.09.2015
 * Time: 15:48
 */

include 'config.php';

global $token;

$_url = 'https://api.telegram.org/bot'.$token;
$regexp = '/([^а-яА-Я]|^)(\н+\е+\т+)[!)[."\'*0-9?]*$/';

$update_id = file_get_contents('upd');
$upd = file_get_contents("$_url/getUpdates?offset=$update_id");
$data = json_decode($upd, true);
$net = array('говна пакет', 'пидора ответ');

if ($data['ok'] && isset($data['result'][1])) {
    foreach ($data['result'] as $msg) {
        if ($update_id < $msg['update_id']) {
            $encode = iconv_get_encoding($msg['message']['text']);
            $txt = iconv($encode, 'UTF-8', $msg['message']['text']);

            $txt = strtolower($txt);
            if (preg_match($regexp, $txt, $matches, PREG_OFFSET_CAPTURE) === 1) {
                $chat_id = $msg['message']['chat']['id'];
                $replay = $msg['message']['message_id'];
                $letter_start = $matches[1][1] + mb_strlen($matches[2][0]);
                $letter_total = strlen($txt) - $letter_start;
                $_txt = substr($msg['message']['text'], $letter_start, $letter_total);
                $text = urlencode($net[array_rand($net)].$_txt);
                file_get_contents("https://api.telegram.org/bot$token/sendMessage?chat_id=$chat_id&text=$text&reply_to_message_id=$replay");
            }
        }
        $update_id = $msg['update_id'];
    }
    file_put_contents('upd', $update_id);
}