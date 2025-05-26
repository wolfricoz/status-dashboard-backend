<?php

namespace App\Command;

use App\Entity\ServiceStatus;
use App\Service\BaseApi;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
		name: 'service:test',
		description: 'Add a short description for your command',
)]
class ServiceTestCommand extends Command
{
	public function __construct(
			private EntityManagerInterface $entityManager,
			private LoggerInterface $logger
	) {
		parent::__construct();
	}


	protected function configure(): void
	{
		$this
				->addArgument('arg1', InputArgument::REQUIRED, 'Service Name');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new SymfonyStyle($input, $output);
		$service = $input->getArgument('arg1');
		$api = new BaseApi($this->entityManager, $this->logger, $service);
		$result = $api->send_request('/ping', 'POST');



		$io->success("TEST RESULT:");
		$io->success($result);

		return Command::SUCCESS;
	}
}
