<?php
/**
 * Created by PhpStorm.
 * User: panuka
 * Date: 03.12.15
 * Time: 10:12
 */

namespace GpaketBundle\Telegram;

use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\Request;

abstract class TelegramBot {

    private $token;
    private $telegram_url = "https://api.telegram.org/bot";

    protected function makeRequest($request) {
        $ch = curl_init();
        curl_init("$this->telegram_url.$this->token.$request");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        return $response;
    }

    protected function setToken($token) {
        $this->token = $token;
    }

    public function setHook($file = "hook.php") {
        $url = "https://$_SERVER[SERVER_NAME]/$file";
        return $this->makeRequest("/setWebhook?url=$url");
    }

}