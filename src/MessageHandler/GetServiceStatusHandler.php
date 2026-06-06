<?php

namespace App\MessageHandler;

use App\Entity\ServiceStatus;
use App\Enum\ServiceStatusType;
use App\Helpers\DiscordMessenger;
use App\Message\GetServiceStatus;
use App\Service\BaseApi;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetServiceStatusHandler
{
	private $repository;

	public function __construct(
			private EntityManagerInterface $entityManager,
			private LoggerInterface        $logger
	) {
		// Removed redundant property reassignment
		$this->repository = $entityManager->getRepository(ServiceStatus::class);
	}

	public function __invoke(GetServiceStatus $message): void
	{
		// Fetch all services from the repository
		$services = $this->repository->getAllServices();

		foreach ($services as $service) {
			/** @var ServiceStatus $service */
			$this->logger->info('Checking service: ' . $service->getService());

			// Create the API handler for the current service
			$service_api = new BaseApi($this->entityManager, $this->logger, $service->getService());

			// Send a ping request to the service
			$result = $service_api->send_request('/ping', 'POST');

			// Normalize 'alive' status to 'ok'
			if ($result['status'] === 'alive') {
				$result['status'] = 'ok';
			}

			// UPDATED: Tracking attempts using the Entity methods directly
			if ($result['status'] !== 'ok') {
				// Assumes you have standard getter/setter or custom increment methods on the entity
				$currentAttempts = $service->getAttempts() ?? 0;
				$service->setAttempts($currentAttempts + 1);
			} else {
				$service->setAttempts(0);
			}

			// If the service status has changed, send a Discord notification
			if (strtolower($service->getStatus()->value) !== strtolower($result['status'])) {

				// FIXED: Changed 'return' to 'continue' and reading attempts from the Entity
				if ($result['status'] !== 'ok' && $service->getAttempts() < 3){
					continue;
				}

				$discordMessenger = new DiscordMessenger();
				$colors = [
						'ok' => '#00c950',
						'offline' => '#c10007',
						'error' => '#c10007',
						'unknown' => '#a1a1a1',
				];

				$discordMessenger->sendNotification(
						'Service Status Alert',
						'The service ' . $service->getService() . ' has changed status from ' . strtolower($service->getStatus()->value) . ' to ' . $result['status'] . '.',
						color: $colors[$result['status']] ?? '#000000',
				);
			}

			// Update the service status based on the result
			switch ($result['status']) {
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

			// Update queue counts if present in the result
			if (isset($result['high_priority_queue'])) {
				$service->setHighTasks($result['high_priority_queue']);
			}
			if (isset($result['normal_priority_queue'])) {
				$service->setMediumTasks($result['normal_priority_queue']);
			}
			if (isset($result['low_priority_queue'])) {
				$service->setLowTasks($result['low_priority_queue']);
			}

			// Persist the updated service entity
			$this->entityManager->persist($service);
			$this->logger->info('Service status updated: ' . $service->getService());
		}

		// FIXED: Moved flush outside the loop to execute all updates in a single batch
		$this->entityManager->flush();

		$this->logger->info('Service status check completed.');
	}
}