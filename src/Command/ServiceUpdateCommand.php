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
    name: 'service:update',
    description: 'Add a short description for your command',
)]
class ServiceUpdateCommand extends Command
{
    public function __construct(
				private EntityManagerInterface $entityManager
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('service_name', InputArgument::REQUIRED, 'This is the service name that you want to update');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
		$helper = $this->getHelper('question');
		$service = $input->getArgument('service_name');
		$updated_service = $this->entityManager->getRepository(ServiceStatus::class)->findOneBy(['service' => $service]);
		if (!$updated_service) {
			$output->writeln('<error>Service doesn\'t exists!</error>');
			return Command::FAILURE;
		}


		$url = $helper->ask($input, $output, new QuestionAlias('Service URL: '));
		$key = $helper->ask($input, $output, new QuestionAlias('Service Key: '));
		if (!$service || !$url || !$key) {
			$output->writeln('<error>Service name, URL and key are required!</error>');
			return Command::FAILURE;
		}
		$updated_service->setUrl($url);
		$updated_service->setKey($key);
		$this->entityManager->persist($updated_service);
		$this->entityManager->flush();


		$io->success('Service updated successfully!');
        return Command::SUCCESS;
    }
}
