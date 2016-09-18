<?php

namespace GpaketBundle\Command;

use Doctrine\DBAL\DBALException;
use GpaketBundle\Entity\Chat;
use GpaketBundle\Entity\Message;
use GpaketBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AppUpdateDbCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('app:update-db')
            ->setDescription('...')
            ->addArgument('argument', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
	    $logs_id = [];
	    $args = $input->getArguments();
	    $em = $this->getContainer()->get('doctrine')->getEntityManager();

	    $count = (int) $em->createQuery('SELECT COUNT(l.update_id) FROM GpaketBundle\Entity\Log l')
		                  ->getSingleScalarResult();
	    $by_page = $args['argument'] * 1000;
	    $n = ceil($count/$by_page) + 1;

	    $em_user = $em->getRepository('GpaketBundle:User');
	    $em_chat = $em->getRepository('GpaketBundle:Chat');
	    $em_log = $em->getRepository('GpaketBundle:Log');
	    $em_message = $em->getRepository('GpaketBundle:Message');
		$request = $em->createQueryBuilder()
					  ->select('l')
					  ->from('GpaketBundle\Entity\Log', 'l');


	    for ($i = 1; $i<=$n; $i++) {
	    	$start = (10*$by_page) * $i + 1;
	    	$end = $start + (10*$by_page);
		    $logs = $request->where('l.update_id >= :start AND l.update_id < :end')
				            ->setParameter('start', $start)
				            ->setParameter('end', $end)
				            ->getQuery()
				            ->getResult();

		    if (empty($logs)) {
			    $output->writeln('Skip.');
			    continue;
		    } else {
			    $output->writeln('Block.');
			    $output->writeln('Count of elements: ');
			    $output->writeln(count($logs));
		    }
			foreach ($logs as $l) {
			    $log = json_decode($l->getRaw(), true);
			    $update_id = $log['update_id'];
//				var_dump(array_search($update_id, $logs_id), $update_id, $logs_id);
//				die();
				if ($l->getUpdateId() == $update_id)
					continue;
				elseif (array_search($update_id, $logs_id)!==false)
					continue;
				elseif (!is_null($em_log->find($update_id)))
					continue;
				$logs_id[] = $update_id;

				if (isset($log['edited_message']))
				    $log['message'] = $log['edited_message'];
			    $message_id = $log['message']['message_id'];
			    $user_id = $log['message']['from']['id'];
			    $chat_id = $log['message']['chat']['id'];


			    $message = $em_message->find($message_id);
			    $chat = $em_chat->find($chat_id);
			    $user = $em_user->find($user_id);

			    if (is_null($message)) {;
				    $message = new Message();
				    $dt = new \DateTime();
				    $dt->setTimestamp($log['message']['date']);
				    $message->setMessageId($message_id);
				    $message->setDate($dt);
				    if (isset($log['message']['reply_to_message']))
					    $message->setReplyToMessage($em_message->find($log['message']['reply_to_message']['message_id']));
				    if (isset($log['message']['text']))
					    $message->setText($log['message']['text']);
				    $em->persist($message);
			    }

			    if (is_null($chat)) {
				    $chat = new Chat();
				    $chat->setChatId($chat_id);
				    if (isset($log['message']['chat']['username']))
					    $chat->setUsername($log['message']['chat']['username']);
				    if (isset($log['message']['chat']['title']))
				        $chat->setTitle($log['message']['chat']['title']);
				    $chat->setType($log['message']['chat']['type']);
				    $message->setChat($chat);
				    $em->persist($chat);
			    } elseif (is_null($message->getChat()))
				    $message->setChat($chat);

			    if (is_null($user)) {
				    $user = new User();
				    $user->setUserId($user_id);
				    if (isset($log['message']['from']['first_name']))
					    $user->setFirstName($log['message']['from']['first_name']);
				    if (isset($log['message']['from']['last_name']))
					    $user->setLastName($log['message']['from']['last_name']);
				    if (isset($log['message']['from']['username']))
					    $user->setUsername($log['message']['from']['username']);
				    $message->setFrom($user);
				    $em->persist($user);
			    } elseif (is_null($message->getFrom()))
				    $message->setFrom($user);

			    $l->setMessage($message);
				$l->setUpdateId($update_id);
			    $em->persist($l);
		    }
		    $connect_count = 0;
		    while($count<10) {
			    try {
				    $em->flush();
			    } catch(Exception $e) {
			    	$output->writeln("Reconnect #$count");
				    $em->getConnection()->close();
				    $em->getConnection()->connect();
			    }
			    $connect_count++;
		    }

	    }
    }

}
