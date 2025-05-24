<?php

namespace App\Command;

use App\Entity\ServiceStatus;
use App\Enum\ServiceStatusType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question as QuestionAlias;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
		name: 'service:create',
		description: 'Add a short description for your command',
)]
class ServiceCreateCommand extends Command
{

	public function __construct(
			private EntityManagerInterface $entityManager
	) {
		parent::__construct();
	}

	protected function configure(): void
	{
		$this
				->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
				->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new SymfonyStyle($input, $output);

		$helper = $this->getHelper('question');
		$service = $helper->ask($input, $output, new QuestionAlias('Service name: '));
		$check_service = $this->entityManager->getRepository(ServiceStatus::class)->findOneBy(['service' => $service]);
		if ($check_service) {
			$output->writeln('<error>Service already exists!</error>');
			return Command::FAILURE;
		}


		$url = $helper->ask($input, $output, new QuestionAlias('Service URL: '));
		$key = $helper->ask($input, $output, new QuestionAlias('Service Key: '));
		if (!$service || !$url || !$key) {
			$output->writeln('<error>Service name, URL and key are required!</error>');
			return Command::FAILURE;
		}


		$new_service = new ServiceStatus();
		$new_service->setService($service);
		$new_service->setUrl($url);
		$new_service->setKey($key);
		$new_service->setStatus(ServiceStatusType::UNKNOWN);
		$this->entityManager->persist($new_service);
		$this->entityManager->flush();


		$io->success('Service created successfully!');

		return Command::SUCCESS;
	}
}
