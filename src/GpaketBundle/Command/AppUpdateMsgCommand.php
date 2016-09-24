<?php

namespace GpaketBundle\Command;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use GpaketBundle\Entity\Chat;
use GpaketBundle\Entity\Log;
use GpaketBundle\Entity\Message;
use GpaketBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AppUpdateMsgCommand extends ContainerAwareCommand {
	private $logs_id = [];
	private $log;
	/**
	 * @var EntityManager
	 */
	private $em;
	/**
	 * @var EntityRepository
	 */
	private $em_user;
	/**
	 * @var EntityRepository
	 */
	private $em_chat;
	/**
	 * @var EntityRepository
	 */
	private $em_log;
	/**
	 * @var EntityRepository
	 */
	private $em_message;

    protected function configure()
    {
        $this
            ->setName('app:update-msg')
	        ->setDescription('Update messages')
	        ->addArgument('multiple', InputArgument::REQUIRED, 'Multiple of selected row by one iteration');
        ;
    }

	protected function execute(InputInterface $input, OutputInterface $output) {
		$args = $input->getArguments();
		$this->em = $this->getContainer()->get('doctrine')->getManager();

		$count = (int)$this->em->createQuery('SELECT COUNT(l.update_id) FROM GpaketBundle\Entity\Log l')->getSingleScalarResult();
		$first = (int)$this->em->createQuery('SELECT l.update_id FROM GpaketBundle\Entity\Log l')->setMaxResults(1)->getSingleScalarResult();
		$by_page = (int) $args['multiple'];
		$n = ceil($count / $by_page) + 1;

		$this->em_user = $this->em->getRepository('GpaketBundle:User');
		$this->em_chat = $this->em->getRepository('GpaketBundle:Chat');
		$this->em_log = $this->em->getRepository('GpaketBundle:Log');
		$this->em_message = $this->em->getRepository('GpaketBundle:Message');
		$request = $this->em->createQueryBuilder()
			->select('l')
			->from('GpaketBundle\Entity\Log', 'l')
			->where('l.update_id >= :start AND l.update_id < :end');


		for ($i = 0; $i <= $n; $i++) {
			$start = $first + $by_page * $i;
			$end = $start + $by_page;
			echo "Process rows: [$start - $end]\n";
			$logs = $request->setParameter('start', $start)
				->setParameter('end', $end)
				->getQuery()
				->getResult();

			if (empty($logs)) {
				echo "Empty set. Skip.\n";
				continue;
			}
			foreach ($logs as $l) {
				$this->add($l);
				echo "\n";
			}
			$output->writeln('Write block');
			$this->write($output);
		}
	}

	private function write(OutputInterface $output) {
		$connect_count = 0;
		while ($connect_count < 10) {
			try {
				$this->em->flush();
			} catch (Exception $e) {
				$output->writeln("Reconnect #$connect_count");
				$this->em->getConnection()->close();
				$this->em->getConnection()->connect();
			}
			$connect_count++;
		}
	}

	private function add(Log $l) {
		$log = json_decode($l->getRaw(), true);
		$update_id = $log['update_id'];
		echo "[id: $update_id]";
		if (isset($log['edited_message']))
			//TODO: Придумать, как поступать с измененными сообщениями.
			return; //$log['message'] = $log['edited_message'];

		$this->log = $log;
		$this->processLog($l);
		$this->em->persist($l);
	}

	private function processMessage() {
		$message_data = $this->log['message'];
		$message = $this->em_message->find($message_data['message_id']);

		if (is_null($message)) {
			$message = new Message();
			$dt = new \DateTime();
			$dt->setTimestamp($message_data['date']);
			$message->setMessageId($message_data['message_id']);
			$message->setDate($dt);
			$this->em->persist($message);
		}
		if (isset($message_data['reply_to_message'])) {
			$message->setReplyToMessage($this->em_message->find($message_data['reply_to_message']['message_id']));
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
		$user = $this->em_user->find($user_data['id']);

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
		$chat = $this->em_chat->find($chat_data['id']);

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
			->getOneOrNullResult()
		;
		if (is_null($request))
			$user->addChat($chat);
		$this->em->persist($user);
		$l->setMessage($message);
		$l->setUpdateId($this->log['update_id']);
	}

}
