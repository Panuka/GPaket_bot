<?php
/**
 * Created by PhpStorm.
 * User: panuka
 * Date: 03.12.15
 * Time: 10:12
 */

namespace Telegram;

abstract class TelegramBot {

    private $token;
    private $telegram_url = "https://api.telegram.org/bot";

    protected function makeRequest($request) {
        return file_get_contents($this->telegram_url.$this->token.$request);
    }

    protected function setToken($token) {
        $this->token = $token;
    }

    public function setHook($file = "hook.php") {
        $url = "https://$_SERVER[SERVER_NAME]/$file";
        return $this->makeRequest("/setWebhook?url=$url");
    }

}