<?php

namespace App\Controller;

use App\Entity\ServiceStatus;
use App\Enum\ServiceStatusType;
use App\Service\BaseApi;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ApiController extends AbstractController
{
//    #[Route('/api', name: 'app_api', methods: ['POST', 'OPTIONS'])]
//    public function index(): JsonResponse
//    {
//        return $this->json([
//            'message' => 'Welcome to your new controller!',
//            'path' => 'src/Controller/ApiController.php',
//        ]);
//    }

	#[Route('/api/{service}', name: 'app_api', methods: ['POST', 'OPTIONS'])]
	public function index(EntityManagerInterface $entityManager, string $service): JsonResponse
	{
		$repository = $entityManager->getRepository(ServiceStatus::class);
		$serviceStatus = $repository->findByName($service);
		if ($serviceStatus == null) {
			$serviceStatus = new ServiceStatus();
			$serviceStatus->setService($service);
			$entityManager->persist($serviceStatus);
			$entityManager->flush();
		}

		return $this->json([
				'status' => $serviceStatus->getStatus(),
				'high' => $serviceStatus->getHighTasks(),
				'medium' => $serviceStatus->getMediumTasks(),
				'low' => $serviceStatus->getLowTasks(),

		]);
	}
}
