<?php

namespace App\Repository;

use App\Entity\ServiceStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ServiceStatus>
 */
class ServiceStatusRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, ServiceStatus::class);
	}

	/**
	 * @param string $serviceName
	 * @return ServiceStatus|null
	 */
	public function findByName(string $serviceName): ?ServiceStatus
	{
//		Searches the ServiceStatus table for a service name and returns the ServiceStatus object
		return $this->findOneBy(['service' => $serviceName]);
	}

	public function getAllServices(): array
	{
		return $this->findAll();
	}

	//    /**
	//     * @return ServiceStatus[] Returns an array of ServiceStatus objects
	//     */
	//    public function findByExampleField($value): array
	//    {
	//        return $this->createQueryBuilder('s')
	//            ->andWhere('s.exampleField = :val')
	//            ->setParameter('val', $value)
	//            ->orderBy('s.id', 'ASC')
	//            ->setMaxResults(10)
	//            ->getQuery()
	//            ->getResult()
	//        ;
	//    }

	//    public function findOneBySomeField($value): ?ServiceStatus
	//    {
	//        return $this->createQueryBuilder('s')
	//            ->andWhere('s.exampleField = :val')
	//            ->setParameter('val', $value)
	//            ->getQuery()
	//            ->getOneOrNullResult()
	//        ;
	//    }
}
