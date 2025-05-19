<?php

namespace App\MessageHandler;

use App\Entity\ServiceStatus;
use App\Enum\ServiceStatusType;
use App\Message\GetServiceStatus;
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

			$services = [
					'banwatch' => 'App\Service\BanwatchApi',
					'ageverifier' => 'App\Service\AgeVerifierApi',
			];
			if (!array_key_exists($service->getService(), $services)) {
				continue;
			}
			$this->logger->info('Checking service: ' . $service->getService());
			echo "Checking service: " . $service->getService() . "\n";
			$service_api = new $services[$service->getService()]();
			$result = $service_api->send_request('/ping', 'POST');
			switch($result['status']) {
				case 'alive':
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
			if (isset($result['high'])) {
				$service->setHighTasks($result['high']);
			}
			if (isset($result['medium'])) {
				$service->setMediumTasks($result['medium']);
			}
			if (isset($result['low'])) {
				$service->setLowTasks($result['low']);
			}
			$this->entityManager->persist($service);
			$this->entityManager->flush();
		}
	}
}
