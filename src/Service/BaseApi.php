<?php

namespace App\Service;


use App\Entity\ServiceStatus;
use App\Enum\ServiceStatusType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\HttpClient;


use Symfony\Contracts\HttpClient\HttpClientInterface;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

class BaseApi
{
	protected string|null $url = null;
	protected string|null $key = null;
	protected HttpClientInterface $client;


//    private string API_KEU = env()
	public function __construct(
			private EntityManagerInterface $entityManager,
			private LoggerInterface        $logger,
			                               $serviceName,
	) {
		$service = $this->entityManager->getRepository(ServiceStatus::class)->findOneBy(['service' => $serviceName]);
		/** @var $service ServiceStatus */
		if (!$service) {
			$this->logger->error("Service {$serviceName} not found");

		}
		if (!$service->getUrl() || !$service->getKey()) {
			$this->logger->error("Service {$serviceName} has no url or key");
			$service->setStatus(ServiceStatusType::ERROR);
			$this->entityManager->persist($service);
			$this->entityManager->flush();
			return;
		}

		$this->client = HttpClient::create();
		$this->url = $service->getUrl();
		$this->key = $service->getKey();
	}

	public function urlBuilder($path)
	{
		return $this->url . $path;
	}

	public function send_request($path, $method = 'GET', $data = []): array
	{
		if (!$this->url || !$this->key) {
			return [
					'status' => 'error',
					'message' => 'Service client not available due to missing URL or key or service doesn\'t exist.',
			];
		}

		try {
			return $this->client->request(
					$method,
					$this->urlBuilder($path),
					[
							'headers' => [
									'token' => $this->key,
							],
					],
			)?->toArray();
		} catch (TransportException) {
			return [
					'status' => 'offline',

			];

		} catch (\Exception $e) {
			return [
					'status' => 'error',
					'message' => $e->getMessage(),
			];
		}
	}


}
