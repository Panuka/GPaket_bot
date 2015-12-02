<?php

/**
 * Created by PhpStorm.
 * User: Panuka
 * Date: 02.12.2015
 * Time: 18:48
 */
class Paket {
	private $answers = [];
	private $regexps;
	private $token;
	private $root;

	public function __construct($dir) {
		$this->root = $dir.'/';

		require_once $this->file('config.php');
		$confDefined = isset ($token) && isset ($answers) && isset ($regexps);
		if (!$confDefined)
			die('Config file not found or broken');
		$this->token = $token;
		$this->answers = $answers;
		foreach ($regexps as $regexp)
			$this->regexps[] = $this->regExp($regexp);

		$this->process();
	}

	private function regExp($word) {
		return '/([\W]|^)('.preg_quote($word).')[!)[.\"\'*0-9?]*$/u';
	}

	private function file($relative_path) {
		return $this->root.$relative_path;
	}

	private function convertToUtf8($text) {
		return mb_convert_encoding($text, 'UTF-8', mb_detect_encoding($text));
	}

	private function process() {
		$_url = 'https://api.telegram.org/bot' . $this->token;

		$update_id = file_get_contents($this->file('upd'));
		$upd = file_get_contents("$_url/getUpdates?offset=$update_id");
		$data = json_decode($upd, true);

		if ($data[ 'ok' ]&&isset($data[ 'result' ][ 1 ])) {
			foreach ($data[ 'result' ] as $msg) {
				if ($update_id < $msg[ 'update_id' ]) {
					$msg_text = $msg[ 'message' ][ 'text' ];
					$txt = mb_strtolower($this->convertToUtf8($msg_text));
					foreach ($this->regexps as $i => $regexp) {
						if ($matches = $this->isRegexpMatch($regexp, $txt)) {
							$chat_id = $msg[ 'message' ][ 'chat' ][ 'id' ];
							$reply = $msg[ 'message' ][ 'message_id' ];
							$letter_start = $matches[ 0 ][ 1 ] + $matches[ 1 ][ 1 ] + $matches[ 2 ][ 1 ] + mb_strlen($matches[ 2 ][ 0 ]);
							$letter_total = strlen($txt) - $letter_start;
							$_txt = substr($msg[ 'message' ][ 'text' ], $letter_start, $letter_total);
							$_answ = &$this->answers[ $i ];
							$text = urlencode($_answ[ array_rand($_answ) ] . $_txt);
							file_get_contents("https://api.telegram.org/bot$this->token/sendMessage?chat_id=$chat_id&text=$text&reply_to_message_id=$reply");
						}
					}
				}
				$update_id = $msg[ 'update_id' ];
			}
			file_put_contents($this->file('upd'), $update_id);
		}
	}

	private function isRegexpMatch($regexp, $txt) {
		if (preg_match($regexp, $txt, $matches, PREG_OFFSET_CAPTURE) === 1)
			return $matches;
		else
			return false;
	}
}