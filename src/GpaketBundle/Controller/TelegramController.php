<?php

namespace GpaketBundle\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use ForceUTF8\Encoding;
use GpaketBundle\Entity\Dictionary;
use GpaketBundle\Entity\Log;
use GpaketBundle\Text\Similarity;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Telegram\Bot\Api;

class TelegramController extends Controller {
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

	/**
	 * @var Api TelegramRequest
	 */
	private $telegram;

	private $msg;

	private function regExp($word) {
		$n = mb_strlen($word);
		$_w = "";
		for ($i = 0; $i < $n; $i++) {
			$_w .= mb_substr($word, $i, 1) . '+';
		}
		$word = $_w;
		return "/([\\W]|^)({$word})[\\W\\w]{0,4}$/ui";
	}

	private function addToLog() {
		$log = new Log();
		$dt = new \DateTime();
		$log->setUpdateId($this->msg['update_id']);
		$log->setRaw(file_get_contents('php://input'));
		$log->setDate($dt);
		$this->db->persist($log);
		$this->db->flush();
	}

	public function process() {
		if (!$this->prepare())
			return false;

		$method = $this->checkType();
		$this->whisperToLobster($method);
		$this->paket();

	}

	function auth() {
		
	}

	private function checkType() {
		$function = 'default';
		$str = $this->msg['message']['text'];
		if ($str[0] === '/' && strlen($str)<8 && strlen($str)>2)
			if (method_exists($this, $str))
				$function = $str;
		return $function;
	}

	private function prepare() {
		$msg = json_decode(file_get_contents('php://input'), true);
		if (!is_null($this->db->getRepository('GpaketBundle:Log')->find($this->msg['update_id'])) || !isset($this->msg['message']['text']) || !is_array($this->msg))
			return false;
		$this->msg = $msg;
		return true;
	}

	private function paket() {
		$this->addToLog();
		$txt = $this->normalize_text($this->msg['message']['text']);
		foreach ($this->dictionary as $dic_id => $dic) {
			$preg = $this->regExp($dic->getPregKeyword());
			if (($matches = $this->isRegexpMatch($preg, $txt)) && ((rand(0, 3) >= 2))) {
				$chat_id = $this->msg['message']['chat']['id'];
				$reply = $this->msg['message']['message_id'];
				$letter_start = mb_strpos($txt, $matches[2]) + mb_strlen($matches[2]);
				$_txt = mb_substr($txt, $letter_start);
				$_answ = $dic->getAnswers();
				$text = urlencode($_answ[array_rand($_answ)] . $_txt);

				return $this->telegram->sendMessage([
					'chat_id' => $chat_id,
					'text' => $text,
					'reply_to_message_id' => $reply,
				]);
			}
		}
		return false;
	}

	private function normalize_text($txt) {
		$txt = Encoding::toUTF8($txt);
		$txt = mb_strtolower($txt);
		return $txt;
		$n = mb_strlen($txt);
		$cyr = [224, 255];
		$lat = [65, 90];
		if (Similarity::isCyr($txt)) {
			$not_check = $cyr;
			$find_sym = ['a', 'у', 'е', 'х', 'а', 'р', 'о', 'с'];
		} else {
			$not_check = $lat;
			$find_sym = ['e', 'y', 'i', 'o', 'p', 'a', 'l', 'x', 'c'];
		}
		$_w = "";
		for ($i = 0; $i < $n; $i++) {
			$cur_sym = mb_substr($txt, $i, 1);
			$num_cur_sym = ord($cur_sym);
			if ($num_cur_sym < $not_check || $num_cur_sym > $not_check)
				foreach ($find_sym as $s)
					if (Similarity::isSimilarity($cur_sym, $s)) {
						$cur_sym = $s;
						break;
					}
			$_w .= $cur_sym;

		}
		return $_w;
	}

	private function isRegexpMatch($regexp, $txt) {
		if (preg_match($regexp, $txt, $matches) === 1)
			return $matches;
		else
			return false;
	}

	protected function makeRequest($request) {
		$url = $this->telegram_url . $this->token . $request;
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($ch);
		return $response;
	}

	protected function makeRequestParams($url, $params) {
		$url = $this->telegram_url . $this->token . $url;
		$requset = http_build_query($params);
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $requset);
		$response = curl_exec($ch);
		echo "$url?$requset\n";
		return $response;
	}

	private function whisperToLobster($data) {
		$this->telegram->sendMessage([
			'chat_id' => '90819247',
			'text' => json_encode($data)
		]);
	}

	private function setHook($file) {
		$url = "https://$_SERVER[SERVER_NAME]/$file";
        return $this->makeRequest("/setWebhook?url=$url");
	}
	private function setKeyboard() {
		$keyboard = [
			['Авторизация'],
			['Смена пароля'],
			['нет', 'да']
		];

		$reply_markup = $this->telegram->replyKeyboardMarkup([
			'keyboard' => $keyboard,
			'resize_keyboard' => true
		]);

		$mp = $this->telegram->forceReply();
		$response = $this->telegram->sendMessage([
			'chat_id' => '90819247',
			'text' => 'Hello World',
			'reply_markup' => $mp
		]);
		return $response;
	}

	public function indexAction() {
		$this->init();
		$this->telegram = new Api($this->token);
		$this->dictionary = $this->db
			->getRepository('GpaketBundle:Dictionary')
			->findAll();
		$this->process();
		return new Response('ok');
	}

	public function setHookAction() {
		$this->init();

//		$data = $this->setHook('telegram/');
		$data = $this->setKeyboard();

		var_dump($data);die();
	}

	public function init() {
		$this->db = $this->getDoctrine()->getManager();
		$this->token = $this->db->getRepository('GpaketBundle:Config')
			->find('GPAKET_TELEGRAM_TOKEN')
			->getValue();
		$this->telegram = new Api($this->token);
	}
}