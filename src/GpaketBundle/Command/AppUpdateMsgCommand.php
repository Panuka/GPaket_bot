<?php

namespace GpaketBundle\Command;


use Doctrine\ORM\EntityManager;
use GpaketBundle\Handler\LogHandler;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class AppUpdateMsgCommand extends ContainerAwareCommand  {

	/** @var EntityManager */
	private $em;
	/** @var OutputInterface */
	private $output;

	protected function configure() {
		$this->setName('app:update-msg')
			->setDescription('Update messages')
			->addArgument('multiple', InputArgument::REQUIRED, 'Multiple of selected row by one iteration');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->output = $output;
		$this->output->writeln('Initialization...');
		$this->em = $this->getContainer()->get('doctrine')->getManager();
		$args = $input->getArguments();

		$sw = new Stopwatch();

		$count = (int)$this->em->createQuery('SELECT COUNT(l.update_id) FROM GpaketBundle\Entity\Log l')->getSingleScalarResult();
		$first = (int)$this->em->createQuery('SELECT l.update_id FROM GpaketBundle\Entity\Log l')->setMaxResults(1)->getSingleScalarResult();
		$by_page = (int)$args['multiple'];
		$n = ceil($count / $by_page) + 1;
		$request = $this->em->createQueryBuilder()
			->select('l')
			->from('GpaketBundle\Entity\Log', 'l')
			->where('l.update_id >= :start AND l.update_id < :end');

		$output->writeln('Run cycle...');
		$lh = new LogHandler($this->em);
		for ($i = 0; $i <= $n; $i++) {
			$sw->start("Iteration $i");
			$start = $first + $by_page * $i;
			$end = $start + $by_page;
			$logs = $request->setParameter('start', $start)
				->setParameter('end', $end)
				->getQuery()
				->getResult();
			$output->writeln("Process rows: [$start - $end]");

			if (empty($logs)) continue;
			$this->output->write('[ ');
			$els = 0;
			foreach ($logs as $l) {
				$els++;
				$lh->add($l);
			}
			$this->output->writeln(' ]');
			$lh->write();
			$sw->stop("Iteration $i");
			$t = $sw->getEvent("Iteration $i")->getDuration()/1000;
			$tpel = number_format($t/$els, 2);
			$this->output->writeln('[ Duration: '. gmdate("H:i:s", $t) ."m, min/el: {$tpel}s, els: {$els}pcs. ]");
			$this->output->writeln('');
		}
	}

}
