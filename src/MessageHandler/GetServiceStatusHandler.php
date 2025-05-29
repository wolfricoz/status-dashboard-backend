<?php

namespace App\MessageHandler;

use App\Entity\ServiceStatus;
use App\Enum\ServiceStatusType;
use App\Helpers\DiscordMessenger;
use App\Message\GetServiceStatus;
use App\Service\BaseApi;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetServiceStatusHandler

{
	public $repository;

	public function __construct(
			private EntityManagerInterface $entityManager,
			private LoggerInterface $logger

	) {
		$this->entityManager = $entityManager;
		$this->repository = $entityManager->getRepository(ServiceStatus::class);
	}


	public
	function __invoke(GetServiceStatus $message): void
	{
		$services = $this->repository->getAllServices();
		foreach ($services as $service) {
			/** @var ServiceStatus $service */

//			$services = [
//					'banwatch' => 'App\Service\BanwatchApi',
//					'ageverifier' => 'App\Service\AgeVerifierApi',
//			];

			$this->logger->info('Checking service: ' . $service->getService());
			echo "Checking service: " . $service->getService() . "\n";
			$service_api = new BaseApi($this->entityManager, $this->logger, $service->getService());

			$result = $service_api->send_request('/ping', 'POST');
			if ($result['status'] === 'alive') {
				$result['status'] = 'ok'; // Normalize status to 'ok'
			}

			if (strtolower($service->getStatus()->value) !== strtolower($result['status'])){
				$discordMessenger = new DiscordMessenger();
				$colors = [
						'ok' => '#00c950',
						'offline' => '#c10007',
						'error' => '#c10007',
						'unknown' => '#a1a1a1',
				];

				$discordMessenger->sendNotification(
					'Service Status Alert',
					'The service ' . $service->getService() . ' has changed status from ' . strtolower($service->getStatus()
							->value) . ' to ' .
					$result['status'] . '.',
					color:$colors[$result['status']] ?? '#000000',
				);
			}

			switch($result['status']) {
				case 'ok':
					$service->setStatus(ServiceStatusType::OK);
					break;
				case 'offline':
					$service->setStatus(ServiceStatusType::OFFLINE);
					break;
				case 'error':
					$service->setStatus(ServiceStatusType::ERROR);
					break;
				default:
					$service->setStatus(ServiceStatusType::UNKNOWN);
					break;
			}

			if (isset($result['high_priority_queue'])) {
				$service->setHighTasks($result['high_priority_queue']);
			}
			if (isset($result['normal_priority_queue'])) {
				$service->setMediumTasks($result['normal_priority_queue']);
			}
			if (isset($result['low_priority_queue'])) {
				$service->setLowTasks($result['low_priority_queue']);
			}
			$this->entityManager->persist($service);
			$this->entityManager->flush();
			$this->logger->info('Service status updated: ' . $service->getService());

		}
		echo 'Service status check completed.' . PHP_EOL;
	}
}
