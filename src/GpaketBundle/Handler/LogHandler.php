<?php

namespace GpaketBundle\Handler;

use Doctrine\ORM\EntityManager;
use GpaketBundle\Entity\Chat;
use GpaketBundle\Entity\Log;
use GpaketBundle\Entity\Message;
use GpaketBundle\Entity\User;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Stopwatch\Stopwatch;

class LogHandler {

	private $em;
	private $log;
	private $sw;
	public $VERBOSE = true;

	function __construct(EntityManager $em) {
		$this->em = $em;
		$this->sw = new Stopwatch();
	}

	public function write() {
		for ($connect_count = 0; $connect_count < 100; $connect_count++)
			try {
				$this->em->flush();
				if ($this->VERBOSE) echo '> Write'.PHP_EOL;
				$connect_count = 100;
			} catch (Exception $e) {
				if ($this->VERBOSE) echo "Reconnect #$connect_count".PHP_EOL;
				$this->em->getConnection()->close();
				$this->em->getConnection()->connect();
			}
	}

	public function add(Log $l) {
		$log = json_decode($l->getRaw(), true);
		if ($this->VERBOSE) echo ".";
		//TODO: Придумать, как поступать с измененными сообщениями.
		if (isset($log['edited_message']))
			return $this; //$log['message'] = $log['edited_message'];
		$this->log = $log;
		$this->processLog($l);
		$this->em->persist($l);
		return $this;
	}


	private function processMessage() {
		$message_data = $this->log['message'];
		$message = $this->em->find('GpaketBundle:Message', $message_data['message_id']);

		if (is_null($message)) {
			$message = new Message();
			$dt = new \DateTime();
			$dt->setTimestamp($message_data['date']);
			$message->setMessageId($message_data['message_id']);
			$message->setDate($dt);
			$this->em->persist($message);
		}
		if (isset($message_data['reply_to_message'])) {
			$message->setReplyToMessage($this->em->find('GpaketBundle:Message', $message_data['reply_to_message']['message_id']));
			$this->em->persist($message);
		}
		if (isset($message_data['text'])) {
			$message->setText($message_data['text']);
			$this->em->persist($message);
		}
		return $message;
	}

	private function processUser(Message $message) {
		$user_data = $this->log['message']['from'];
		$user = $this->em->find('GpaketBundle:User', $user_data['id']);

		if (is_null($user)) {
			$user = new User();
			$user->setUserId($user_data['id']);
			$message->setFrom($user);
			$this->em->persist($user);
			$this->em->persist($message);
		}
		if (isset($user_data['first_name'])) {
			$user->setFirstName($user_data['first_name']);
			$this->em->persist($user);
		}
		if (isset($user_data['last_name'])) {
			$user->setLastName($user_data['last_name']);
			$this->em->persist($user);
		}
		if (isset($user_data['username'])) {
			$user->setUsername($user_data['username']);
			$this->em->persist($user);
		}

		return $user;
	}

	private function processChat(Message $message) {
		$chat_data = $this->log['message']['chat'];
		$chat = $this->em->find('GpaketBundle:Chat', $chat_data['id']);

		if (is_null($chat)) {
			$chat = new Chat();
			$chat->setChatId($chat_data['id']);
			$chat->setType($chat_data['type']);
			$this->em->persist($chat);
		}
		if (isset($chat_data['username']) && ($chat_data['username'] != $chat->getUsername())) {
			$chat->setUsername($chat_data['username']);
			$this->em->persist($chat);
		}
		if (isset($chat_data['title']) && ($chat_data['title'] != $chat->getTitle())) {
			$chat->setTitle($chat_data['title']);
			$this->em->persist($chat);
		}
		if (is_null($message->getChat())) {
			$message->setChat($chat);
			$this->em->persist($message);
		}

		return $chat;
	}

	private function processLog(Log $l) {
		$message = $this->processMessage();
		$user = $this->processUser($message);
		$chat = $this->processChat($message);

		$request = $this->em->createQueryBuilder()
			->select('u')
			->from('GpaketBundle\Entity\User', 'u')
			->join('u.chats', 'c')
			->where('u.user_id = :user AND c.chat_id = :chat')
			->setParameter('user', $user->getUserId())
			->setParameter('chat', $chat->getChatId())
			->getQuery()
			->getOneOrNullResult();
		if (is_null($request))
			$user->addChat($chat);
		$this->em->persist($user);
		$l->setMessage($message);
		$l->setUpdateId($this->log['update_id']);
	}
}