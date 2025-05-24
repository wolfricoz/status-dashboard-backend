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
		name: 'service:delete',
		description: 'Add a short description for your command',
)]
class ServiceDeleteCommand extends Command
{
	public function __construct(
		private EntityManagerInterface $entityManager
	)
	{
		parent::__construct();
	}

	protected function configure(): void
	{

	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new SymfonyStyle($input, $output);

		$helper = $this->getHelper('question');
		$service = $helper->ask($input, $output, new QuestionAlias('Service name: '));
		$check_service = $this->entityManager->getRepository(ServiceStatus::class)->findOneBy(['service' => $service]);
		if (!$check_service) {
			$output->writeln('<error>Service doesn\'t exists!</error>');
			return Command::FAILURE;
		}
		$confirm = $helper->ask($input, $output, new QuestionAlias('Are you sure you want to delete this service? (yes/no): '), 'no');
		if (strtolower($confirm) !== 'yes') {
			$output->writeln('<info>Service deletion cancelled.</info>');
			return Command::SUCCESS;
		}
		$this->entityManager->remove($check_service);
		$this->entityManager->flush();
		$io->success('Service deleted successfully!');

		return Command::SUCCESS;
	}
}
